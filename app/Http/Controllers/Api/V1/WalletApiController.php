<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WalletApiController extends Controller
{
    /**
     * GET /v1/wallet
     * Mengambil informasi saldo wallet, pending balance, info rekening bank, dan ringkasan mutasi.
     * Wajib menyertakan key dari tabel users untuk otentikasi.
     */
    public function index(Request $request): JsonResponse
    {
        [$user, $authError] = $this->authenticateUserKey($request);
        if ($authError) {
            return $authError;
        }

        $wallet = $user->getOrCreateWallet();
        $bankAccount = $wallet->value['bank_account'] ?? null;
        $rawMutations = array_reverse($wallet->value['mutations'] ?? []);
        $mutations = array_map(function ($m) {
            $ts = $m['timestamp'] ?? $m['created_at'] ?? now()->toIso8601String();
            $amt = (float) ($m['amount'] ?? 0);
            $after = isset($m['balance_after']) ? (float) $m['balance_after'] : null;
            $before = isset($m['balance_before']) ? (float) $m['balance_before'] : ($after !== null ? $after - $amt : null);
            return [
                'type' => $m['type'] ?? 'credit',
                'amount' => $amt,
                'balance_before' => $before,
                'balance_after' => $after,
                'description' => $m['description'] ?? '',
                'timestamp' => $ts,
                'created_at' => $ts,
                'reference_id' => $m['reference_id'] ?? null,
                'id' => $m['id'] ?? null,
            ];
        }, $rawMutations);

        $recentWithdrawals = Withdrawal::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($w) {
                return [
                    'id' => $w->id,
                    'uuid' => $w->uuid,
                    'amount' => (float) $w->amount,
                    'status' => $w->status,
                    'bank_account' => $w->value['bank_name'] ?? null ? [
                        'bank_name' => $w->value['bank_name'] ?? '',
                        'account_number' => $w->value['account_number'] ?? '',
                        'account_holder' => $w->value['account_holder'] ?? '',
                    ] : null,
                    'notes' => $w->value['note'] ?? $w->value['notes'] ?? null,
                    'created_at' => $w->created_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Data wallet penjual berhasil diambil.',
            'data' => [
                'wallet' => [
                    'uuid' => $wallet->uuid,
                    'currency' => $wallet->currency,
                    'balance' => (float) $wallet->balance,
                    'pending_balance' => (float) $wallet->pending_balance,
                    'bank_account' => $bankAccount,
                    'mutations' => $mutations,
                ],
                'recent_withdrawals' => $recentWithdrawals,
                // Direct access compatibility
                'balance' => (float) $wallet->balance,
                'pending_balance' => (float) $wallet->pending_balance,
                'currency' => $wallet->currency,
                'formatted_balance' => 'Rp ' . number_format($wallet->balance, 0, ',', '.'),
                'formatted_pending_balance' => 'Rp ' . number_format($wallet->pending_balance, 0, ',', '.'),
                'bank_account' => $bankAccount,
                'mutations' => $mutations,
                'user' => [
                    'id' => $user->id,
                    'id_users' => $user->id_users,
                    'nama' => $user->nama,
                    'username' => $user->username,
                ],
                'created_at' => $wallet->created_at?->toIso8601String(),
                'updated_at' => $wallet->updated_at?->toIso8601String(),
            ],
        ], 200);
    }

    /**
     * POST /v1/wallet/bank-account
     * Mengatur atau memperbarui data rekening bank tujuan pencairan saldo (Withdraw).
     * Wajib menyertakan key dari tabel users.
     */
    public function updateBankAccount(Request $request): JsonResponse
    {
        [$user, $authError] = $this->authenticateUserKey($request);
        if ($authError) {
            return $authError;
        }

        $validator = Validator::make($request->all(), [
            'bank_name' => ['required', 'string', 'max:50'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder' => ['required', 'string', 'max:100'],
        ], [
            'bank_name.required' => 'Nama bank wajib diisi (contoh: BCA, Mandiri, BRI, BNI).',
            'account_number.required' => 'Nomor rekening wajib diisi.',
            'account_holder.required' => 'Nama pemilik rekening wajib diisi sesuai buku tabungan.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Validasi rekening bank gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

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

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Rekening bank penarikan saldo berhasil disimpan.',
            'data' => [
                'wallet_id' => $wallet->id,
                'bank_account' => [
                    'bank_name' => $val['bank_account']['bank_name'],
                    'account_number' => $val['bank_account']['account_number'],
                    'account_holder' => $val['bank_account']['account_holder'],
                ],
            ],
        ], 200);
    }

    /**
     * POST /v1/wallet/withdraw
     * Mengajukan penarikan dana (Withdraw) dari saldo wallet ke rekening bank.
     * Wajib menyertakan key dari tabel users.
     */
    public function withdraw(Request $request): JsonResponse
    {
        [$user, $authError] = $this->authenticateUserKey($request);
        if ($authError) {
            return $authError;
        }

        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'numeric', 'min:10000'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_holder' => ['nullable', 'string', 'max:100'],
        ], [
            'amount.required' => 'Nominal penarikan dana wajib diisi.',
            'amount.numeric' => 'Nominal penarikan harus berupa angka.',
            'amount.min' => 'Minimal penarikan dana adalah Rp 10.000.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Validasi penarikan dana gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $wallet = $user->getOrCreateWallet();
        $amount = (float) $request->input('amount');

        if ($wallet->balance < $amount) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => "Saldo tidak mencukupi untuk melakukan penarikan Rp " . number_format($amount, 0, ',', '.') . ". Saldo Anda saat ini: Rp " . number_format($wallet->balance, 0, ',', '.'),
                'error' => 'INSUFFICIENT_BALANCE',
                'current_balance' => (float) $wallet->balance,
            ], 400);
        }

        // Tentukan rekening tujuan (dari request atau dari profil wallet)
        $savedBank = $wallet->value['bank_account'] ?? [];
        $bankName = $request->input('bank_name', $savedBank['bank_name'] ?? null);
        $accountNumber = $request->input('account_number', $savedBank['account_number'] ?? null);
        $accountHolder = $request->input('account_holder', $savedBank['account_holder'] ?? null);

        if (empty($bankName) || empty($accountNumber) || empty($accountHolder)) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => 'Rekening bank tujuan belum disetel. Sertakan field bank_name, account_number, dan account_holder, atau simpan rekening terlebih dahulu.',
                'error' => 'BANK_ACCOUNT_NOT_SET',
            ], 400);
        }

        $adminFee = 2500.00; // Biaya admin transfer antar bank
        $netAmount = max(0, $amount - $adminFee);

        $balanceBefore = (float) $wallet->balance;

        // Potong saldo aktif, pindahkan ke pending balance
        $wallet->balance -= $amount;
        $wallet->pending_balance += $amount;

        // Catat mutasi debit
        $val = $wallet->value ?? [];
        $mutations = $val['mutations'] ?? [];
        $withdrawalUuid = (string) Str::uuid();
        $now = now()->toIso8601String();

        $mutations[] = [
            'id' => $withdrawalUuid,
            'type' => 'debit',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => (float) $wallet->balance,
            'description' => "Penarikan dana (Withdrawal) ke {$bankName} {$accountNumber} a/n {$accountHolder}",
            'reference_id' => 'WD-' . strtoupper(substr($withdrawalUuid, 0, 8)),
            'timestamp' => $now,
            'created_at' => $now,
        ];
        $val['mutations'] = $mutations;
        $wallet->value = $val;
        $wallet->save();

        // Buat record di tabel withdrawals
        $withdrawal = Withdrawal::create([
            'uuid' => $withdrawalUuid,
            'wallet_id' => $wallet->id,
            'user_id' => $user->id,
            'amount' => $amount,
            'admin_fee' => $adminFee,
            'net_amount' => $netAmount,
            'status' => 'pending',
            'value' => [
                'bank_name' => $bankName,
                'account_number' => $accountNumber,
                'account_holder' => $accountHolder,
                'note' => $request->input('notes', $request->input('note', 'Permintaan penarikan dana via API')),
                'notes' => $request->input('notes', $request->input('note', 'Permintaan penarikan dana via API')),
            ],
            'requested_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'code' => 201,
            'message' => 'Permintaan penarikan dana berhasil diajukan dan sedang diproses.',
            'data' => [
                'withdrawal_id' => $withdrawal->id,
                'uuid' => $withdrawal->uuid,
                'reference_code' => 'WD-' . strtoupper(substr($withdrawalUuid, 0, 8)),
                'amount' => $amount,
                'admin_fee' => $adminFee,
                'net_amount' => $netAmount,
                'status' => $withdrawal->status,
                'notes' => $request->input('notes', $request->input('note')),
                'bank_destination' => [
                    'bank_name' => $bankName,
                    'account_number' => $accountNumber,
                    'account_holder' => $accountHolder,
                ],
                'remaining_balance' => (float) $wallet->balance,
                'pending_balance' => (float) $wallet->pending_balance,
                'requested_at' => $withdrawal->requested_at?->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Otentikasi key user dari request (header X-USER-KEY, body 'key', query 'key', atau session).
     *
     * @return array{0: ?User, 1: ?JsonResponse}
     */
    private function authenticateUserKey(Request $request): array
    {
        $userKey = $request->input('key')
            ?? $request->input('user_key')
            ?? $request->header('X-USER-KEY')
            ?? $request->header('X-KEY')
            ?? $request->query('key');

        if (empty($userKey) && Auth::check() && Auth::user()?->key) {
            $userKey = Auth::user()->key;
        }

        if (empty($userKey)) {
            return [
                null,
                response()->json([
                    'status' => 'error',
                    'code' => 401,
                    'message' => 'Key dari tabel user wajib disertakan untuk mengakses API Wallet. Sertakan field "key" pada body atau header "X-USER-KEY".',
                    'error' => 'MISSING_USER_KEY',
                ], 401)
            ];
        }

        // Cari user yang memiliki key cocok
        $targetUser = null;
        if ($request->filled('id_users')) {
            $targetUser = User::where('id_users', trim($request->input('id_users')))->first();
        } elseif ($request->filled('username')) {
            $targetUser = User::where('username', strtolower(trim($request->input('username'))))->first();
        } elseif ($request->filled('user_id')) {
            $targetUser = User::find($request->input('user_id'));
        } elseif (Auth::check()) {
            $targetUser = Auth::user();
        }

        if ($targetUser) {
            if (empty($targetUser->key) || !$this->verifyKeyMatch($userKey, $targetUser->key)) {
                return [
                    null,
                    response()->json([
                        'status' => 'error',
                        'code' => 401,
                        'message' => 'Key pengguna tidak valid atau tidak cocok dengan data di tabel users.',
                        'error' => 'INVALID_USER_KEY',
                    ], 401)
                ];
            }
            return [$targetUser, null];
        }

        // Cari di seluruh kandidat user
        $candidates = User::whereNotNull('key')->get();
        foreach ($candidates as $candidate) {
            if ($this->verifyKeyMatch($userKey, $candidate->key)) {
                return [$candidate, null];
            }
        }

        return [
            null,
            response()->json([
                'status' => 'error',
                'code' => 401,
                'message' => 'Key pengguna tidak ditemukan atau tidak valid di tabel users.',
                'error' => 'INVALID_USER_KEY',
            ], 401)
        ];
    }

    private function verifyKeyMatch(string $inputKey, string $storedKey): bool
    {
        if (hash_equals($storedKey, $inputKey)) {
            return true;
        }

        if (str_starts_with($storedKey, '$2y$') || str_starts_with($storedKey, '$2a$') || str_starts_with($storedKey, '$2b$')) {
            try {
                return Hash::check($inputKey, $storedKey);
            } catch (\Throwable) {
                return false;
            }
        }

        return false;
    }
}
