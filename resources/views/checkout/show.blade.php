@extends('layouts.app')

@section('title', 'Checkout ' . $product->name . ' - POS Market Kita')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="text-center space-y-2">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-300 border border-emerald-500/30">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-2 animate-pulse"></span>
            Pembayaran Instan via QRIS Midtrans
        </span>
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Checkout Produk Digital</h1>
        <p class="text-xs text-slate-400">Masukkan nama dan email Anda. File produk digital akan otomatis dikirimkan ke email Anda setelah pembayaran QRIS diverifikasi.</p>
    </div>

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        <!-- Left: Product Summary Card (2 Cols) -->
        <div class="md:col-span-2 p-6 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-xl space-y-4 flex flex-col">
            <div class="w-full h-44 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-center overflow-hidden">
                @if (!empty($product->value['gambar']))
                    <img src="{{ $product->value['gambar'] }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                @else
                    <svg class="w-12 h-12 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                @endif
            </div>

            <div class="flex-1 space-y-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/40 uppercase">
                    {{ $product->product_type }}
                </span>
                <h3 class="text-sm font-bold text-white">{{ $product->name }}</h3>
                <p class="text-xs text-slate-400 line-clamp-3">
                    {{ $product->value['deskripsi'] ?? 'Produk digital siap unduh setelah pembayaran terkonfirmasi.' }}
                </p>
            </div>

            <div class="pt-4 border-t border-slate-800 space-y-1">
                <div class="flex justify-between text-xs text-slate-400">
                    <span>Penjual:</span>
                    <strong class="text-white">{{ $product->user->nama ?? 'POS Seller' }}</strong>
                </div>
                <div class="flex justify-between text-xs text-slate-400">
                    <span>Stok:</span>
                    <span class="text-emerald-400 font-semibold">{{ $product->stock !== null ? $product->stock . ' unit' : 'Unlimited' }}</span>
                </div>
                <div class="flex justify-between text-sm pt-2 border-t border-slate-800/80 font-bold">
                    <span class="text-white">Total Harga:</span>
                    <span class="text-emerald-400 text-base">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Right: Checkout Customer Form (3 Cols) -->
        <div class="md:col-span-3 p-6 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-xl space-y-5">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center space-x-2 border-b border-slate-800 pb-3">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Informasi Pembeli & Pengiriman</span>
            </h3>

            <form action="{{ route('checkout.process', $product->slug) }}" method="POST" class="space-y-4 text-xs" onsubmit="const btn = this.querySelector('button[type=submit]'); btn.disabled = true; btn.classList.add('opacity-80', 'cursor-not-allowed'); btn.innerHTML = '<svg class=\'animate-spin w-5 h-5 mr-2 text-white inline-block\' fill=\'none\' viewBox=\'0 0 24 24\'><circle class=\'opacity-25\' cx=\'12\' cy=\'12\' r=\'10\' stroke=\'currentColor\' stroke-width=\'4\'></circle><path class=\'opacity-75\' fill=\'currentColor\' d=\'M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z\'></path></svg><span>Memproses Pembayaran QRIS...</span>';">
                @csrf

                <div>
                    <label for="customer_name" class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">
                        Nama Lengkap <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" id="customer_name" name="customer_name" required value="{{ old('customer_name', Auth::user()?->nama ?? '') }}" placeholder="Contoh: Budi Santoso" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xs">
                    @error('customer_name')
                        <span class="text-rose-400 text-[10px] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="customer_email" class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">
                        Alamat Email Penerima <span class="text-rose-400">*</span>
                    </label>
                    <input type="email" id="customer_email" name="customer_email" required value="{{ old('customer_email', Auth::user()?->email ?? '') }}" placeholder="budi@example.com" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xs">
                    <p class="text-[10px] text-slate-400 mt-1">Pastikan email aktif. File produk digital & tanda terima invoice akan otomatis dikirimkan ke alamat ini.</p>
                    @error('customer_email')
                        <span class="text-rose-400 text-[10px] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="customer_phone" class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">
                        Nomor WhatsApp / HP (Opsional)
                    </label>
                    <input type="tel" id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="081234567890" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xs font-mono">
                </div>

                <div>
                    <label for="notes" class="block font-semibold uppercase tracking-wider text-slate-300 mb-1">
                        Catatan Tambahan (Opsional)
                    </label>
                    <textarea id="notes" name="notes" rows="2" placeholder="Tuliskan catatan bila ada..." class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xs">{{ old('notes') }}</textarea>
                </div>

                <!-- Payment Method Badge -->
                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold text-[10px]">QRIS</span>
                        <span class="text-slate-200 text-xs font-semibold">QRIS All Payment (Midtrans)</span>
                    </div>
                    <span class="text-[10px] text-slate-400">Instan & Otomatis</span>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-brand-600 hover:from-emerald-500 hover:to-brand-500 text-white font-bold text-sm shadow-lg shadow-emerald-500/25 transition active:scale-[0.98] flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        <span>Lanjut ke Pembayaran QRIS</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
