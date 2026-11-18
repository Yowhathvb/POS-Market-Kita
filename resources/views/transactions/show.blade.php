@extends('layouts.app')

@section('title', 'Detail Transaksi ' . $transaction->invoice_number . ' - POS Market Kita')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Breadcrumbs & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-brand-400 transition">Beranda</a>
                <span>/</span>
                <a href="{{ route('transactions.index') }}" class="hover:text-brand-400 transition">Transaksi</a>
                <span>/</span>
                <span class="text-slate-200 font-semibold">{{ $transaction->invoice_number }}</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight flex items-center space-x-3">
                <span class="p-2 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
                <span>Detail Transaksi Invoice</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1 code-font">
                Nomor Invoice: <span class="text-cyan-300 font-bold">{{ $transaction->invoice_number }}</span> &bull; ID Transaksi: <span class="text-slate-300">{{ $transaction->uuid }}</span>
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('transactions.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Daftar</span>
            </a>
            <a href="{{ route('checkout.invoice', $transaction->invoice_number) }}" target="_blank" class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold shadow-md shadow-cyan-600/20 transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span>Buka Invoice QRIS</span>
            </a>
        </div>
    </div>

    <!-- Main Grid Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Product Items & Delivery Status (2 cols) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Product Card -->
            <div class="glass-card rounded-2xl border border-slate-800 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-white flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                        <span>Item Produk Digital</span>
                    </h3>
                    <span class="text-xs text-slate-400">Tipe: Produk Digital</span>
                </div>

                <div class="space-y-4">
                    @foreach ($transaction->items as $item)
                        @php
                            $downloadUrl = $transaction->status === 'paid' 
                                ? (data_get($item->value, 'file_url') ?: data_get($item->product?->value, 'file_url')) 
                                : null;
                            $itemImage = data_get($item->value, 'gambar') ?: data_get($item->product?->value, 'gambar');
                        @endphp
                        <div class="p-4 rounded-xl bg-slate-900/70 border border-slate-800/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="flex items-center space-x-3.5">
                                @if (!empty($itemImage))
                                    <img src="{{ $itemImage }}" alt="{{ $item->product?->name }}" class="w-14 h-14 rounded-xl object-cover border border-slate-700">
                                @else
                                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-900 to-slate-900 border border-slate-700 flex items-center justify-center text-indigo-400">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <h4 class="text-sm font-bold text-white">{{ $item->product?->name ?? ($item->value['product_name'] ?? 'Produk Digital') }}</h4>
                                    <div class="text-xs text-slate-400 mt-0.5">
                                        {{ $item->qty }} unit &times; Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                    </div>
                                    @if ($downloadUrl)
                                        <a href="{{ $downloadUrl }}" target="_blank" class="inline-flex items-center text-xs font-bold text-cyan-400 hover:text-cyan-300 mt-1.5 space-x-1">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            <span>Link File Digital Pembeli</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right sm:self-center">
                                <span class="text-xs text-slate-400 block">Subtotal</span>
                                <span class="text-base font-bold text-white code-font">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Total Amount Summary -->
                <div class="pt-3 border-t border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Total Pembayaran</span>
                    <span class="text-xl font-black text-cyan-400 code-font">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Email Delivery Status Log -->
            <div class="glass-card rounded-2xl border border-slate-800 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-white flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span>Pengiriman Produk Digital via Email</span>
                    </h3>
                    <span class="text-xs text-slate-400">Tabel: email_notifications</span>
                </div>

                @if ($transaction->emailNotifications->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($transaction->emailNotifications as $notif)
                            <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-800 flex items-center justify-between text-xs">
                                <div>
                                    <div class="font-semibold text-white">{{ $notif->subject }}</div>
                                    <div class="text-slate-400 text-[11px] mt-0.5">
                                        Penerima: <span class="code-font text-cyan-300">{{ $notif->recipient_email }}</span> &bull; 
                                        Waktu: {{ $notif->sent_at?->format('d M Y, H:i') ?? '-' }} WIB
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $notif->status === 'sent' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' }}">
                                    {{ $notif->status }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 rounded-xl bg-slate-900/40 border border-slate-800/80 text-center text-xs text-slate-400">
                        @if ($transaction->status === 'paid')
                            Notifikasi email sedang diproses.
                        @else
                            Email produk digital otomatis dikirimkan ke pembeli setelah pembayaran QRIS lunas.
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Right: Buyer Info & Payment Status (1 col) -->
        <div class="space-y-6">
            <!-- Payment Status Card -->
            <div class="glass-card rounded-2xl border border-slate-800 p-6 space-y-4">
                <div class="border-b border-slate-800 pb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Status Transaksi</span>
                    <div class="mt-2 flex items-center space-x-2">
                        @if ($transaction->status === 'paid')
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center space-x-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Lunas (Paid)</span>
                            </span>
                        @elseif ($transaction->status === 'unpaid')
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center space-x-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                <span>Menunggu Pembayaran</span>
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-400 border border-slate-700">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Gateway:</span>
                        <span class="font-semibold text-white uppercase">Midtrans</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Metode Pembayaran:</span>
                        <span class="font-semibold text-sky-400 uppercase">QRIS</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Waktu Transaksi:</span>
                        <span class="text-white">{{ $transaction->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    @if ($transaction->paid_at)
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Waktu Lunas:</span>
                            <span class="text-emerald-400 font-semibold">{{ $transaction->paid_at->format('d M Y, H:i') }}</span>
                        </div>
                    @endif
                </div>

                @php
                    $qrCodeUrl = data_get($transaction->latestPayment?->value, 'qr_code_url');
                @endphp
                @if (!empty($qrCodeUrl))
                    <div class="pt-3 border-t border-slate-800 text-center">
                        <span class="text-[11px] text-slate-400 block mb-2">QRIS Code Transaksi:</span>
                        <img src="{{ $qrCodeUrl }}" alt="QRIS Midtrans" class="w-40 h-40 mx-auto rounded-xl p-2 bg-white border border-slate-300">
                    </div>
                @endif
            </div>

            <!-- Customer Data Card -->
            <div class="glass-card rounded-2xl border border-slate-800 p-6 space-y-4">
                <div class="border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-white flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                        <span>Informasi Pembeli</span>
                    </h3>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 text-[11px] block">Nama Lengkap:</span>
                        <span class="font-semibold text-white text-sm">{{ $transaction->value['customer_name'] ?? 'Pembeli' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[11px] block">Alamat Email:</span>
                        <span class="font-semibold text-cyan-300 code-font">{{ $transaction->buyer_email }}</span>
                    </div>
                    @if (!empty($transaction->value['customer_phone']))
                        <div>
                            <span class="text-slate-400 text-[11px] block">Nomor Telepon:</span>
                            <span class="text-white">{{ $transaction->value['customer_phone'] }}</span>
                        </div>
                    @endif
                    @if (!empty($transaction->value['notes']))
                        <div>
                            <span class="text-slate-400 text-[11px] block">Catatan Pembeli:</span>
                            <span class="text-slate-300 italic">{{ $transaction->value['notes'] }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
