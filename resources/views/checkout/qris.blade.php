@extends('layouts.app')

@section('title', 'Pembayaran QRIS ' . $transaction->invoice_number . ' - POS Market Kita')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    @if ($transaction->status === 'paid')
        <!-- Status SUKSES / LUNAS -->
        <div class="p-8 rounded-3xl bg-slate-900/90 border border-emerald-500/30 shadow-2xl text-center space-y-6 relative overflow-hidden">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <div>
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                    Pembayaran Berhasil Diverifikasi
                </span>
                <h1 class="text-2xl font-black text-white mt-3">Transaksi Lunas!</h1>
                <p class="text-xs text-slate-400 mt-1">
                    Terima kasih, pembayaran untuk Invoice <strong class="text-cyan-300 code-font">{{ $transaction->invoice_number }}</strong> telah berhasil diproses.
                </p>
            </div>

            <!-- Transaction Summary -->
            <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 text-xs text-left space-y-2">
                <div class="flex justify-between">
                    <span class="text-slate-400">Produk Digital:</span>
                    <strong class="text-white">{{ $item?->product?->name ?? ($item?->value['product_name'] ?? 'Produk Digital') }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Total Dibayar:</span>
                    <strong class="text-emerald-400 font-bold">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Email Penerima:</span>
                    <span class="text-slate-200">{{ $transaction->buyer_email }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Waktu Bayar:</span>
                    <span class="text-slate-300">{{ $transaction->paid_at ? $transaction->paid_at->format('d M Y, H:i') : '-' }} WIB</span>
                </div>
            </div>

            <!-- Download Button -->
            @php
                $downloadUrl = $item?->value['file_url'] ?? $item?->product?->value['file_url'] ?? null;
            @endphp

            @if (!empty($downloadUrl))
                <div class="pt-2 space-y-3">
                    <a href="{{ $downloadUrl }}" target="_blank" class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 to-brand-600 hover:from-emerald-500 hover:to-brand-500 text-white font-bold text-sm shadow-xl shadow-emerald-500/30 transition flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Download File Produk Digital Sekarang</span>
                    </a>
                    <p class="text-[11px] text-slate-400">
                        File asset digital dan link unduhan juga telah otomatis dikirimkan ke email Anda (<strong>{{ $transaction->buyer_email }}</strong>).
                    </p>
                </div>
            @else
                <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs">
                    Link produk sedang disiapkan oleh penjual. Anda juga dapat menghubungi penjual melalui email mereka.
                </div>
            @endif

            <div class="pt-4 border-t border-slate-800 flex justify-center space-x-3">
                <a href="{{ route('products.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold transition">
                    Kembali ke Katalog
                </a>
            </div>
        </div>

    @else
        <!-- Status UNPAID: Tampilkan QRIS -->
        <div class="p-6 sm:p-8 rounded-3xl bg-slate-900/90 border border-slate-800 shadow-2xl text-center space-y-5">
            <!-- Header -->
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-amber-500/10 text-amber-300 border border-amber-500/30">
                    <span class="w-2 h-2 rounded-full bg-amber-400 mr-2 animate-ping"></span>
                    Menunggu Pembayaran QRIS
                </span>
                <h1 class="text-xl sm:text-2xl font-black text-white mt-2">Scan QRIS untuk Membayar</h1>
                <p class="text-xs text-slate-400 mt-1">
                    Invoice: <span class="code-font text-cyan-400 font-bold select-all">{{ $transaction->invoice_number }}</span>
                </p>
            </div>

            <!-- Total Amount Card -->
            <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                <div class="text-left">
                    <span class="text-[10px] uppercase font-bold text-slate-500 block">Total Tagihan</span>
                    <span class="text-xs text-slate-300">{{ $item?->product?->name ?? 'Produk Digital' }}</span>
                </div>
                <div class="text-xl font-black text-emerald-400">
                    Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                </div>
            </div>

            <!-- QRIS Image Box -->
            @php
                $qrCodeUrl = $payment?->value['qr_code_url'] ?? null;
                $qrString = $payment?->value['qr_string'] ?? null;
                if (empty($qrCodeUrl) && !empty($qrString)) {
                    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=350x350&data=' . urlencode($qrString);
                }
            @endphp

            <div class="p-6 rounded-2xl bg-white max-w-[280px] mx-auto shadow-xl relative">
                @if (!empty($qrCodeUrl))
                    <img src="{{ $qrCodeUrl }}" alt="QRIS Midtrans" class="w-full h-auto mx-auto rounded-lg">
                @else
                    <div class="w-48 h-48 mx-auto flex items-center justify-center text-slate-500 text-xs">
                        QRIS tidak tersedia
                    </div>
                @endif
                <div class="mt-2 text-center">
                    <span class="text-[10px] font-black text-slate-800 tracking-widest uppercase">QRIS &bull; MIDTRANS</span>
                </div>
            </div>

            <!-- Supported Apps Badges -->
            <div class="text-[11px] text-slate-400 space-y-1.5">
                <span>Dapat di-scan menggunakan seluruh aplikasi yang mendukung QRIS:</span>
                <div class="flex flex-wrap items-center justify-center gap-1.5 pt-1 text-[10px] font-semibold text-slate-300">
                    <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700">GoPay</span>
                    <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700">OVO</span>
                    <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700">Dana</span>
                    <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700">ShopeePay</span>
                    <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700">BCA Mobile</span>
                    <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700">Livin Mandiri</span>
                    <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700">BRImo</span>
                </div>
            </div>

            <!-- Action Buttons: Check Status & Sandbox Simulation -->
            <div class="space-y-3 pt-3 border-t border-slate-800">
                <button onclick="window.location.reload()" class="w-full py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Cek Status Pembayaran (Refresh)</span>
                </button>

                <!-- Sandbox Simulation Button -->
                <button id="simulateBtn" onclick="simulatePayment('{{ $transaction->invoice_number }}')" class="w-full py-2.5 rounded-xl bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-xs font-semibold transition flex items-center justify-center space-x-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Simulasi Bayar QRIS Sukses (Sandbox Mode)</span>
                </button>
            </div>
        </div>

        <script>
            // Polling otomatis setiap 5 detik untuk cek status
            setInterval(function() {
                fetch('/v1/transactions/{{ $transaction->invoice_number }}/status', {
                    headers: { 'X-API-KEY': '{{ env('API_KEY', 'pos_secret_token_1234567890') }}' }
                })
                .then(res => res.json())
                .then(res => {
                    if (res && res.data && res.data.is_paid) {
                        window.location.reload();
                    }
                })
                .catch(() => {});
            }, 5000);

            function simulatePayment(invoice) {
                const btn = document.getElementById('simulateBtn');
                btn.innerText = 'Memproses simulasi pembayaran...';
                btn.disabled = true;

                fetch('/v1/transactions/' + invoice + '/simulate-payment', {
                    method: 'POST',
                    headers: {
                        'X-API-KEY': '{{ env('API_KEY', 'pos_secret_token_1234567890') }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        window.location.reload();
                    } else {
                        alert(res.message || 'Gagal simulasi');
                        btn.disabled = false;
                        btn.innerText = 'Simulasi Bayar QRIS Sukses (Sandbox Mode)';
                    }
                })
                .catch(err => {
                    alert('Error: ' + err.message);
                    btn.disabled = false;
                    btn.innerText = 'Simulasi Bayar QRIS Sukses (Sandbox Mode)';
                });
            }
        </script>
    @endif
</div>
@endsection
