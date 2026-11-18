<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WalletController extends Controller
{
    /**
     * Tampilkan halaman utama Wallet pengguna.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();

        // Riwayat transaksi penjualan produk milik user
        $transactions = $user->sellerTransactions()
            ->with(['items.product', 'latestPayment'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Riwayat penarikan dana (withdrawals)
        $withdrawals = $user->withdrawals()->latest()->take(10)->get();

        // Mutasi dari JSON wallet
        $mutations = array_reverse($wallet->value['mutations'] ?? []);

        return view('wallet.index', compact(
            'user',
            'wallet',
            'transactions',
            'withdrawals',
            'mutations'
        ));
    }

    /**
     * Simpan / Perbarui rekening bank pencairan.
     */
    public function updateBank(Request $request): RedirectResponse
    {
        $request->validate([
            'bank_name' => ['required', 'string', 'max:50'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder' => ['required', 'string', 'max:100'],
        ], [
            'bank_name.required' => 'Nama bank wajib diisi.',
            'account_number.required' => 'Nomor rekening wajib diisi.',
            'account_holder.required' => 'Nama pemilik rekening wajib diisi.',
        ]);

        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();

        $val = $wallet->value ?? [];
        $val['bank_account'] = [
            'bank_name' => strtoupper(trim($request->input('bank_name'))),
            'account_number' => trim($request->input('account_number')),
            'account_holder' => trim($request->input('account_holder')),
            'updated_at' => now()->toIso8601String(),
        ];
        $wallet->value = $val;
        $wallet->save();

        return redirect()->route('wallet.index')->with('success', 'Rekening bank pencairan berhasil disimpan!');
    }

    /**
     * Ajukan penarikan dana dari saldo wallet.
     */
    public function withdraw(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:10000'],
        ], [
            'amount.required' => 'Nominal penarikan wajib diisi.',
            'amount.min' => 'Minimal penarikan dana adalah Rp 10.000.',
        ]);

        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();
        $amount = (float) $request->input('amount');

        if ($wallet->balance < $amount) {
            return redirect()->route('wallet.index')->with('error', 'Saldo aktif tidak mencukupi untuk melakukan penarikan sebesar Rp ' . number_format($amount, 0, ',', '.'));
        }

        $bank = $wallet->value['bank_account'] ?? null;
        if (empty($bank) || empty($bank['account_number'])) {
            return redirect()->route('wallet.index')->with('error', 'Silakan lengkapi informasi rekening bank tujuan terlebih dahulu sebelum melakukan penarikan dana.');
        }

        $adminFee = 2500.00;
        $netAmount = max(0, $amount - $adminFee);

        // Potong saldo aktif, pindahkan ke pending balance
        $wallet->balance -= $amount;
        $wallet->pending_balance += $amount;

        $uuid = (string) Str::uuid();
        $val = $wallet->value ?? [];
        $mutations = $val['mutations'] ?? [];
        $mutations[] = [
            'id' => $uuid,
            'type' => 'debit',
            'amount' => $amount,
            'description' => "Penarikan saldo ke {$bank['bank_name']} {$bank['account_number']} a/n {$bank['account_holder']}",
            'reference_id' => 'WD-' . strtoupper(substr($uuid, 0, 8)),
            'balance_after' => (float) $wallet->balance,
            'created_at' => now()->toIso8601String(),
        ];
        $val['mutations'] = $mutations;
        $wallet->value = $val;
        $wallet->save();

        Withdrawal::create([
            'uuid' => $uuid,
            'wallet_id' => $wallet->id,
            'user_id' => $user->id,
            'amount' => $amount,
            'admin_fee' => $adminFee,
            'net_amount' => $netAmount,
            'status' => 'pending',
            'value' => [
                'bank_name' => $bank['bank_name'],
                'account_number' => $bank['account_number'],
                'account_holder' => $bank['account_holder'],
                'notes' => 'Penarikan dana via Web Dashboard',
            ],
            'requested_at' => now(),
        ]);

        return redirect()->route('wallet.index')->with('success', 'Pengajuan penarikan dana sebesar Rp ' . number_format($amount, 0, ',', '.') . ' berhasil diajukan dan sedang diproses.');
    }
}
