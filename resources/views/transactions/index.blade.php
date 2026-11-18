@extends('layouts.app')

@section('title', 'Data Transaksi & Checkout Produk - POS Market Kita')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header with Breadcrumbs & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-brand-400 transition">Beranda</a>
                <span>/</span>
                <span class="text-slate-200 font-semibold">Checkout & Transaksi</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight flex items-center space-x-3">
                <span class="p-2 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
                <span>Data Transaksi & Checkout Produk</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                Seluruh transaksi pada produk yang Anda buat otomatis tercatat di sini lengkap dengan status tagihan QRIS Midtrans, pembeli, dan pengiriman produk.
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('products.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs border border-slate-700 transition flex items-center space-x-2">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span>Katalog Produk Saya</span>
            </a>
            <a href="{{ route('wallet.index') }}" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-brand-600 hover:from-emerald-500 hover:to-brand-500 text-white font-bold text-xs shadow-lg shadow-emerald-500/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span>Lihat Saldo Wallet</span>
            </a>
        </div>
    </div>

    <!-- 4 Stats Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Transaksi -->
        <div class="glass-card rounded-2xl p-5 border border-slate-800 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Transaksi</span>
                    <h3 class="text-2xl font-black text-white mt-1">{{ number_format($stats['total']) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-3">Semua pesanan produk Anda</p>
        </div>

        <!-- Card 2: Transaksi Lunas -->
        <div class="glass-card rounded-2xl p-5 border border-emerald-500/20 bg-emerald-950/10 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400">Lunas (Paid)</span>
                    <h3 class="text-2xl font-black text-emerald-400 mt-1">{{ number_format($stats['paid']) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-3">Pembayaran QRIS berhasil</p>
        </div>

        <!-- Card 3: Menunggu Pembayaran -->
        <div class="glass-card rounded-2xl p-5 border border-amber-500/20 bg-amber-950/10 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-400">Menunggu (Unpaid)</span>
                    <h3 class="text-2xl font-black text-amber-400 mt-1">{{ number_format($stats['unpaid']) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-3">Menunggu pembeli scan QRIS</p>
        </div>

        <!-- Card 4: Total Pendapatan Penjualan -->
        <div class="glass-card rounded-2xl p-5 border border-indigo-500/20 bg-indigo-950/10 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-300">Total Pendapatan</span>
                    <h3 class="text-xl font-black text-white mt-1">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-center text-indigo-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-3">Masuk ke Wallet & Saldo Anda</p>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="glass-card rounded-2xl p-4 border border-slate-800">
        <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <!-- Search Query -->
            <div class="sm:col-span-5 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari invoice (INV-...), email, atau nama pembeli..." class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 transition">
                <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Status Filter -->
            <div class="sm:col-span-3">
                <select name="status" class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white focus:outline-none focus:border-cyan-500 transition">
                    <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>Semua Status</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Lunas (Paid)</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Menunggu (Unpaid)</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan (Cancelled)</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="sm:col-span-4 flex items-center space-x-2">
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs transition flex items-center justify-center space-x-1.5 shadow-md shadow-cyan-600/20">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span>Filter Data</span>
                </button>
                @if (request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                    <a href="{{ route('transactions.index') }}" class="px-3 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold border border-slate-700 transition" title="Reset Filter">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Transaksi Table -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden shadow-xl">
        <div class="p-4 sm:p-5 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
                <h3 class="text-sm font-bold text-white">Daftar Transaksi Produk</h3>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300">
                    {{ $transactions->total() }} Pesanan
                </span>
            </div>
            <span class="text-xs text-slate-400">Diurutkan dari yang terbaru</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800/80 bg-slate-950/40 text-slate-400 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Invoice & Tanggal</th>
                        <th class="py-3 px-4">Produk Digital</th>
                        <th class="py-3 px-4">Data Pembeli</th>
                        <th class="py-3 px-4">Total Tagihan</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Metode Bayar</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse ($transactions as $trx)
                        @php
                            $firstItem = $trx->items->first();
                            $isPaid = $trx->status === 'paid';
                        @endphp
                        <tr class="hover:bg-slate-900/40 transition">
                            <!-- Invoice & Tanggal -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-cyan-300 code-font flex items-center space-x-1.5">
                                    <span>{{ $trx->invoice_number }}</span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $trx->created_at->format('d M Y, H:i') }} WIB
                                </div>
                            </td>

                            <!-- Produk Digital -->
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="font-semibold text-white truncate" title="{{ $firstItem?->product?->name ?? ($firstItem?->value['product_name'] ?? 'Produk Digital') }}">
                                    {{ $firstItem?->product?->name ?? ($firstItem?->value['product_name'] ?? 'Produk Digital') }}
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5 flex items-center space-x-2">
                                    <span>Qty: {{ $firstItem?->qty ?? 1 }}</span>
                                    <span>&bull;</span>
                                    <span>@ Rp {{ number_format($firstItem?->unit_price ?? $trx->total_amount, 0, ',', '.') }}</span>
                                </div>
                            </td>

                            <!-- Data Pembeli -->
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-200">
                                    {{ $trx->value['customer_name'] ?? 'Pembeli' }}
                                </div>
                                <div class="text-[11px] text-slate-400 code-font mt-0.5">
                                    {{ $trx->buyer_email }}
                                </div>
                                @if (!empty($trx->value['customer_phone']))
                                    <div class="text-[10px] text-slate-500 mt-0.5">
                                        {{ $trx->value['customer_phone'] }}
                                    </div>
                                @endif
                            </td>

                            <!-- Total Tagihan -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white code-font">
                                    Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4">
                                @if ($isPaid)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                                        Lunas (Paid)
                                    </span>
                                @elseif ($trx->status === 'unpaid')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-1.5"></span>
                                        Menunggu Bayar
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">
                                        {{ ucfirst($trx->status) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Metode Bayar -->
                            <td class="py-3.5 px-4">
                                <div class="inline-flex items-center space-x-1.5 px-2 py-0.5 rounded bg-slate-800 text-slate-300 text-[11px] font-medium border border-slate-700">
                                    <span class="font-bold text-sky-400 uppercase">QRIS</span>
                                    <span class="text-slate-500">&bull;</span>
                                    <span class="text-slate-400">Midtrans</span>
                                </div>
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('transactions.show', $trx->invoice_number) }}" class="p-1.5 rounded-lg bg-cyan-500/10 text-cyan-400 hover:bg-cyan-500/20 border border-cyan-500/20 transition" title="Lihat Detail Transaksi">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('checkout.invoice', $trx->invoice_number) }}" target="_blank" class="p-1.5 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700 transition" title="Buka Halaman QRIS Pembeli">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-4 text-center">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-800/60 border border-slate-700/60 flex items-center justify-center text-slate-400">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <h4 class="text-sm font-bold text-white">Belum Ada Transaksi Produk</h4>
                                    <p class="text-xs text-slate-400">
                                        Setiap kali pembeli melakukan checkout dan memesan produk digital yang Anda buat, transaksinya akan otomatis muncul di halaman ini.
                                    </p>
                                    <div class="pt-2">
                                        <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs shadow-md shadow-cyan-600/20 transition space-x-1.5">
                                            <span>Lihat Katalog Produk Saya</span>
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($transactions->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
