<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Tampilkan daftar transaksi pada produk milik penjual yang sedang login.
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();

        $query = Transaction::where('seller_user_id', $userId)
            ->with(['items.product', 'latestPayment']);

        // Filter status
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', strtolower(trim($request->input('status'))));
        }

        // Pencarian: nomor invoice, email pembeli, atau nama pembeli
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('buyer_email', 'like', "%{$search}%")
                    ->orWhere('value->customer_name', 'like', "%{$search}%");
            });
        }

        // Filter tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Ringkasan metrik statistik produk penjual
        $baseQuery = Transaction::where('seller_user_id', $userId);
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'paid' => (clone $baseQuery)->where('status', 'paid')->count(),
            'unpaid' => (clone $baseQuery)->where('status', 'unpaid')->count(),
            'revenue' => (float) (clone $baseQuery)->where('status', 'paid')->sum('total_amount'),
        ];

        $transactions = $query->latest('id')->paginate(12)->withQueryString();

        return view('transactions.index', compact('transactions', 'stats'));
    }

    /**
     * Tampilkan detail transaksi produk.
     */
    public function show(Transaction $transaction): View
    {
        abort_unless($transaction->seller_user_id === Auth::id(), 403, 'Anda tidak memiliki hak akses melihat transaksi produk milik penjual lain.');

        $transaction->load(['items.product', 'latestPayment', 'emailNotifications']);

        return view('transactions.show', compact('transaction'));
    }
}
