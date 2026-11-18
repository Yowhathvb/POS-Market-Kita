@extends('layouts.app')

@section('title', 'Menu Wallet dan Saldo - POS Market Kita')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header with Breadcrumbs & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-brand-400 transition">Beranda</a>
                <span>/</span>
                <span class="text-slate-200 font-semibold">Wallet dan Saldo Penjual</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight flex items-center space-x-3">
                <span class="p-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </span>
                <span>Wallet dan Saldo Penjual</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                Sesuai skema ERD <code class="code-font text-emerald-400">tbl_wallets</code>: Saldo otomatis bertambah setiap ada pembeli yang membayar produk digital Anda via QRIS Midtrans.
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="document.getElementById('withdrawModal').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-brand-600 hover:from-emerald-500 hover:to-brand-500 text-white font-bold text-xs shadow-lg shadow-emerald-500/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Tarik Saldo (Withdraw)</span>
            </button>
        </div>
    </div>

    <!-- Alert Success / Error -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center space-x-2.5">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-center space-x-2.5">
            <svg class="w-5 h-5 flex-shrink-0 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- 4 Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Saldo Aktif -->
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-xl relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center justify-between text-slate-400 text-xs mb-2">
                <span class="font-semibold uppercase tracking-wider">Saldo Aktif</span>
                <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-black text-white tracking-tight">
                Rp {{ number_format($wallet->balance, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-emerald-400 flex items-center space-x-1 font-medium">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Siap ditarik ke rekening</span>
            </div>
        </div>

        <!-- Saldo Tertahan (Pending) -->
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-xl relative overflow-hidden">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-2">
                <span class="font-semibold uppercase tracking-wider">Saldo Tertahan</span>
                <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-300 tracking-tight">
                Rp {{ number_format($wallet->pending_balance, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-amber-400 flex items-center space-x-1 font-medium">
                <span>Dalam proses pencairan</span>
            </div>
        </div>

        <!-- Mata Uang -->
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-xl relative overflow-hidden">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-2">
                <span class="font-semibold uppercase tracking-wider">Mata Uang</span>
                <span class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-black text-white tracking-tight">
                {{ $wallet->currency }}
            </div>
            <div class="mt-2 text-[11px] text-slate-400 font-medium">
                Indonesian Rupiah
            </div>
        </div>

        <!-- Total Penjualan -->
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-xl relative overflow-hidden">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-2">
                <span class="font-semibold uppercase tracking-wider">Total Transaksi</span>
                <span class="p-1.5 rounded-lg bg-cyan-500/10 text-cyan-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-black text-white tracking-tight">
                {{ $transactions->total() }}
            </div>
            <div class="mt-2 text-[11px] text-cyan-400 font-medium">
                Invoice berhasil dibuat
            </div>
        </div>
    </div>

    <!-- 2 Column Section: Bank Account Card & Quick Withdraw Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Bank Account Card (Left 1 Col) -->
        <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                    </svg>
                    <span>Rekening Pencairan</span>
                </h3>
                <button onclick="document.getElementById('bankModal').classList.remove('hidden')" class="text-xs text-brand-400 hover:text-brand-300 font-semibold transition">
                    {{ !empty($wallet->value['bank_account']) ? 'Ubah' : '+ Atur Rekening' }}
                </button>
            </div>

            @if (!empty($wallet->value['bank_account']))
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                    <div class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Bank Tujuan:</div>
                    <div class="text-lg font-black text-white flex items-center space-x-2">
                        <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-xs font-mono font-bold">{{ $wallet->value['bank_account']['bank_name'] }}</span>
                        <span class="font-mono text-cyan-300">{{ $wallet->value['bank_account']['account_number'] }}</span>
                    </div>
                    <div class="text-xs text-slate-400 pt-1">
                        a/n <strong class="text-slate-200">{{ $wallet->value['bank_account']['account_holder'] }}</strong>
                    </div>
                </div>
            @else
                <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-300 text-xs space-y-2 text-center">
                    <svg class="w-8 h-8 mx-auto text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="font-semibold">Belum Ada Rekening Bank</p>
                    <p class="text-[11px] text-amber-300/80">Atur rekening bank Anda untuk menerima penarikan dana hasil penjualan produk digital.</p>
                    <button onclick="document.getElementById('bankModal').classList.remove('hidden')" class="mt-2 px-3 py-1.5 rounded-lg bg-amber-500/20 hover:bg-amber-500/30 text-amber-200 text-xs font-bold transition">
                        Atur Rekening Sekarang
                    </button>
                </div>
            @endif

            <div class="text-[11px] text-slate-400 space-y-1 pt-2 border-t border-slate-800/80">
                <div class="flex items-center justify-between">
                    <span>UUID Wallet:</span>
                    <span class="code-font text-slate-300 text-[10px]">{{ substr($wallet->uuid, 0, 18) }}...</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Biaya Admin Tarik:</span>
                    <span class="text-emerald-400 font-semibold">Rp 2.500 / penarikan</span>
                </div>
            </div>
        </div>

        <!-- Transactions & Inflow Table (Right 2 Cols) -->
        <div class="lg:col-span-2 p-6 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center space-x-2">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <span>Riwayat Penjualan Produk (Inflow Saldo)</span>
                </h3>
                <span class="text-xs text-slate-400">Total: {{ $transactions->total() }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider text-[10px] border-b border-slate-800">
                        <tr>
                            <th class="py-2.5 px-3">Invoice</th>
                            <th class="py-2.5 px-3">Produk</th>
                            <th class="py-2.5 px-3">Pembeli</th>
                            <th class="py-2.5 px-3">Nominal</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80">
                        @forelse ($transactions as $trx)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="py-2.5 px-3 code-font font-bold text-cyan-400">
                                    <a href="{{ route('checkout.invoice', $trx->invoice_number) }}" target="_blank" class="hover:underline">
                                        {{ $trx->invoice_number }}
                                    </a>
                                </td>
                                <td class="py-2.5 px-3 text-slate-200">
                                    {{ $trx->items->first()?->product?->name ?? 'Produk Digital' }}
                                </td>
                                <td class="py-2.5 px-3 text-slate-400">
                                    <div class="text-white text-[11px] font-semibold">{{ $trx->value['customer_name'] ?? '-' }}</div>
                                    <div class="text-[10px]">{{ $trx->buyer_email }}</div>
                                </td>
                                <td class="py-2.5 px-3 whitespace-nowrap font-bold text-white">
                                    Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 whitespace-nowrap">
                                    @if ($trx->status === 'paid')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                                            ✓ LUNAS
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40">
                                            {{ strtoupper($trx->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 whitespace-nowrap text-slate-500 text-[11px]">
                                    {{ $trx->created_at->format('d M, H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500 text-xs">
                                    Belum ada transaksi penjualan yang tercatat. Bagikan link produk digital Anda untuk mulai menerima transaksi!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($transactions->hasPages())
                <div class="pt-3 border-t border-slate-800">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Mutations History Section -->
    <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-xl space-y-4">
        <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center space-x-2 border-b border-slate-800 pb-3">
            <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <span>Log Mutasi Saldo Wallet (JSON value.mutations)</span>
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider text-[10px] border-b border-slate-800">
                    <tr>
                        <th class="py-2.5 px-3">Tipe</th>
                        <th class="py-2.5 px-3">Deskripsi</th>
                        <th class="py-2.5 px-3">Nominal</th>
                        <th class="py-2.5 px-3">Saldo Setelah</th>
                        <th class="py-2.5 px-3">Referensi</th>
                        <th class="py-2.5 px-3">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse ($mutations as $mut)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                @if (($mut['type'] ?? '') === 'credit')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400">
                                        + CREDIT
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400">
                                        - DEBIT
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-slate-300">
                                {{ $mut['description'] ?? '-' }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap font-bold {{ ($mut['type'] ?? '') === 'credit' ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ ($mut['type'] ?? '') === 'credit' ? '+' : '-' }} Rp {{ number_format($mut['amount'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap font-mono text-slate-200">
                                Rp {{ number_format($mut['balance_after'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap code-font text-slate-400">
                                {{ $mut['reference_id'] ?? '-' }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap text-slate-500 text-[11px]">
                                {{ !empty($mut['created_at']) ? \Carbon\Carbon::parse($mut['created_at'])->format('d M Y, H:i') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-500 text-xs">
                                Belum ada riwayat mutasi saldo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal 1: Atur Rekening Bank -->
<div id="bankModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                </svg>
                <span>Atur Rekening Pencairan</span>
            </h3>
            <button onclick="document.getElementById('bankModal').classList.add('hidden')" class="text-slate-400 hover:text-white">✕</button>
        </div>

        <form action="{{ route('wallet.bank') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label for="bank_name" class="block font-semibold text-slate-300 mb-1">Nama Bank:</label>
                <input type="text" id="bank_name" name="bank_name" required value="{{ old('bank_name', $wallet->value['bank_account']['bank_name'] ?? '') }}" placeholder="Contoh: BCA, MANDIRI, BRI, BNI" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label for="account_number" class="block font-semibold text-slate-300 mb-1">Nomor Rekening:</label>
                <input type="text" id="account_number" name="account_number" required value="{{ old('account_number', $wallet->value['bank_account']['account_number'] ?? '') }}" placeholder="Contoh: 1234567890" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
            </div>
            <div>
                <label for="account_holder" class="block font-semibold text-slate-300 mb-1">Nama Pemilik Rekening (Sesuai Buku Tabungan):</label>
                <input type="text" id="account_holder" name="account_holder" required value="{{ old('account_holder', $wallet->value['bank_account']['account_holder'] ?? '') }}" placeholder="Contoh: Budi Santoso" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="pt-2 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('bankModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold transition">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition">Simpan Rekening</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Tarik Saldo (Withdraw) -->
<div id="withdrawModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Tarik Saldo (Withdrawal)</span>
            </h3>
            <button onclick="document.getElementById('withdrawModal').classList.add('hidden')" class="text-slate-400 hover:text-white">✕</button>
        </div>

        @if (empty($wallet->value['bank_account']))
            <div class="p-3 bg-amber-500/10 border border-amber-500/30 rounded-xl text-xs text-amber-300">
                Silakan isi data rekening bank terlebih dahulu sebelum mengajukan penarikan dana.
            </div>
        @else
            <form action="{{ route('wallet.withdraw') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="p-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-400 space-y-1">
                    <div class="flex justify-between">
                        <span>Saldo Tersedia:</span>
                        <strong class="text-emerald-400 font-mono">Rp {{ number_format($wallet->balance, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span>Rekening Tujuan:</span>
                        <span class="text-slate-200">{{ $wallet->value['bank_account']['bank_name'] }} - {{ $wallet->value['bank_account']['account_number'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Pemilik:</span>
                        <span class="text-slate-200">{{ $wallet->value['bank_account']['account_holder'] }}</span>
                    </div>
                </div>

                <div>
                    <label for="amount" class="block font-semibold text-slate-300 mb-1">Nominal Penarikan (Rp):</label>
                    <input type="number" id="amount" name="amount" min="10000" max="{{ (int)$wallet->balance }}" step="1000" required placeholder="Contoh: 50000" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <span class="text-[10px] text-slate-500 mt-1 block">Minimal Rp 10.000 &bull; Biaya transfer bank Rp 2.500</span>
                </div>

                <div class="pt-2 flex items-center justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('withdrawModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold transition">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-brand-600 hover:from-emerald-500 hover:to-brand-500 text-white font-bold transition">Ajukan Penarikan</button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
