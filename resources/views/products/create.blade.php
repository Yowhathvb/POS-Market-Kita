@extends('layouts.app')

@section('title', 'Input Produk Baru - POS Market Kita')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header with Breadcrumbs & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-brand-400 transition">Beranda</a>
                <span>/</span>
                <a href="{{ route('products.index') }}" class="hover:text-brand-400 transition">Katalog Produk</a>
                <span>/</span>
                <span class="text-slate-200 font-semibold">Input Produk Baru</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight flex items-center space-x-3">
                <span class="p-2 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </span>
                <span>Input Produk Baru</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                Sesuai skema ERD POS Market Kita: Kolom <code class="code-font text-cyan-300">uuid</code>, <code class="code-font text-cyan-300">slug</code>, dan JSON <code class="code-font text-amber-300">value</code> dibuat otomatis.
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('products.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold border border-slate-700 transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </div>

    <!-- Error Summary Alerts -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs space-y-1.5">
            <div class="flex items-center space-x-2 font-bold text-rose-400">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Terdapat kesalahan pada isian form:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 pl-6 text-rose-300/90">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Input Form Card -->
    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" id="productForm" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Main Product Data -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Section 1: Basic Information -->
                <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-xl space-y-5">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center space-x-2 border-b border-slate-800 pb-3">
                        <svg class="w-4 h-4 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span>Informasi Utama Produk</span>
                    </h3>

                    <!-- Product Name -->
                    <div>
                        <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Nama Produk <span class="text-rose-400">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            required 
                            value="{{ old('name') }}" 
                            placeholder="Contoh: E-Book Panduan Master POS Market, Baju Kaos Polos..." 
                            class="w-full px-4 py-3 rounded-xl bg-slate-950/80 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm transition"
                            oninput="updateSlugPreview(this.value)"
                        >
                        <div class="mt-1.5 flex items-center space-x-2 text-[11px] text-slate-400">
                            <span>Slug otomatis:</span>
                            <span id="slugPreview" class="code-font text-cyan-400 font-semibold truncate">-</span>
                        </div>
                    </div>

                    <!-- Hidden Product Type: Strictly Digital -->
                    <input type="hidden" name="product_type" value="digital">

                    <!-- Digital Product Banner Info -->
                    <div class="p-4 rounded-xl bg-indigo-500/10 border border-indigo-500/30 flex items-start space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </div>
                        <div class="flex-1 text-xs">
                            <span class="font-bold text-white block">Kategori: Produk Digital</span>
                            <span class="text-slate-300">Khusus E-Book, Source Code, Template, Lisensi, Kursus, atau Asset Digital. Stok dapat diisi jika terbatas, atau kosongkan untuk <strong class="text-indigo-300">Unlimited</strong>.</span>
                        </div>
                    </div>

                    <!-- Price Input -->
                    <div>
                        <label for="price" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Harga Produk (Rp) <span class="text-rose-400">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400 pointer-events-none">
                                Rp
                            </span>
                            <input 
                                type="number" 
                                id="price" 
                                name="price" 
                                required 
                                step="100" 
                                min="0" 
                                value="{{ old('price', 0) }}" 
                                placeholder="50000" 
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/80 border border-slate-700/80 text-white font-semibold placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm transition"
                                oninput="formatRupiahPreview(this.value)"
                            >
                        </div>
                        <div class="mt-1 text-[11px] text-emerald-400 font-medium" id="pricePreview">
                            Terbaca: Rp 0
                        </div>
                    </div>
                </div>

                <!-- Section 2: Data Kolom 'value' (JSON) -->
                <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-xl space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center space-x-2">
                            <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z" />
                            </svg>
                            <span>Data Kolom 'value' (JSON ERD)</span>
                        </h3>
                        <span class="px-2 py-0.5 rounded-md text-[10px] code-font font-bold bg-amber-500/10 text-amber-300 border border-amber-500/30">
                            value: JSON (file_url, gambar, deskripsi)
                        </span>
                    </div>

                    <!-- Digital File URL (Shown for Digital products) -->
                    <div id="digitalFileContainer">
                        <label for="file_url" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5 flex items-center space-x-1.5">
                            <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                            <span>File Download URL / Link Asset Digital</span>
                        </label>
                        <input 
                            type="url" 
                            id="file_url" 
                            name="file_url" 
                            value="{{ old('file_url') }}" 
                            placeholder="https://drive.google.com/file/... atau link file download" 
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950/80 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm transition"
                        >
                        <p class="text-[11px] text-slate-400 mt-1">
                            Link download yang akan otomatis dikirimkan ke pembeli setelah pembayaran berhasil.
                        </p>
                    </div>

                    <!-- Description (deskripsi) -->
                    <div>
                        <label for="deskripsi" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Deskripsi Lengkap Produk
                        </label>
                        <textarea 
                            id="deskripsi" 
                            name="deskripsi" 
                            rows="4" 
                            placeholder="Tuliskan detail spesifikasi produk, keunggulan, petunjuk pemakaian..." 
                            class="w-full p-3.5 rounded-xl bg-slate-950/80 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm transition"
                        >{{ old('deskripsi') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Right 1 Col: Media, Store Assignment & Publishing -->
            <div class="space-y-6">
                <!-- Section 3: Gambar Produk -->
                <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-xl space-y-4">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center space-x-2 border-b border-slate-800 pb-3">
                        <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Foto / Gambar Produk</span>
                    </h3>

                    <!-- Image Preview Area -->
                    <div id="imagePreviewContainer" class="w-full h-44 rounded-xl border-2 border-dashed border-slate-700 bg-slate-950/50 flex flex-col items-center justify-center p-3 text-center overflow-hidden relative">
                        <img id="imagePreview" src="" alt="Preview" class="hidden w-full h-full object-cover rounded-lg">
                        <div id="imagePlaceholder" class="space-y-2">
                            <svg class="w-10 h-10 mx-auto text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="block text-xs text-slate-400">Pilih file gambar atau masukkan URL gambar</span>
                        </div>
                    </div>

                    <!-- File Upload Input -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Upload File (JPG, PNG, WEBP):</label>
                        <input 
                            type="file" 
                            name="gambar_file" 
                            id="gambar_file" 
                            accept="image/*" 
                            class="block w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-500/20 file:text-brand-300 hover:file:bg-brand-500/30 file:cursor-pointer cursor-pointer"
                            onchange="previewUploadedImage(event)"
                        >
                    </div>

                    <!-- Or Image URL -->
                    <div class="pt-2 border-t border-slate-800/80">
                        <label for="gambar_url" class="block text-[11px] font-semibold text-slate-300 mb-1">Atau Gunakan URL Gambar:</label>
                        <input 
                            type="url" 
                            id="gambar_url" 
                            name="gambar_url" 
                            value="{{ old('gambar_url') }}" 
                            placeholder="https://contoh.com/gambar.jpg" 
                            class="w-full px-3 py-2 rounded-lg bg-slate-950/80 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500 text-xs transition"
                            oninput="previewUrlImage(this.value)"
                        >
                    </div>
                </div>

                <!-- Section 4: Stok & Status Produk -->
                <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-xl space-y-4">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center space-x-2 border-b border-slate-800 pb-3">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span>Stok & Status Produk</span>
                    </h3>

                    <!-- Stock Input -->
                    <div>
                        <label for="stock" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Stok Produk (Jumlah Tersedia)
                        </label>
                        <input 
                            type="number" 
                            id="stock" 
                            name="stock" 
                            min="0" 
                            value="{{ old('stock') }}" 
                            placeholder="Contoh: 100 (kosongkan jika unlimited)" 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950/80 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-xs transition"
                        >
                        <p class="text-[10px] text-slate-400 mt-1">
                            Jumlah persediaan unit. Kosongkan jika produk digital ini tidak memiliki batas stok (Unlimited).
                        </p>
                    </div>

                    <!-- Active Toggle (is_active) -->
                    <div class="pt-3 border-t border-slate-800/80">
                        <label class="flex items-center justify-between cursor-pointer">
                            <div>
                                <span class="text-xs font-bold text-white block">Status Produk Aktif</span>
                                <span class="text-[11px] text-slate-400">Tampilkan dan aktifkan untuk transaksi</span>
                            </div>
                            <input 
                                type="checkbox" 
                                name="is_active" 
                                value="1" 
                                class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 bg-slate-950 border-slate-700"
                                {{ old('is_active', true) ? 'checked' : '' }}
                            >
                        </label>
                    </div>

                    <!-- Submit Button with Loading Indicator -->
                    <div class="pt-4 border-t border-slate-800">
                        <button 
                            type="submit" 
                            id="submitProductBtn"
                            class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-bold text-sm shadow-lg shadow-brand-500/25 hover:shadow-brand-500/40 focus:outline-none focus:ring-2 focus:ring-brand-500 transition active:scale-[0.98] flex items-center justify-center relative overflow-hidden group"
                        >
                            <!-- Normal State -->
                            <span id="btnText" class="flex items-center space-x-2">
                                <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Simpan Produk Baru</span>
                            </span>
                            <!-- Loading State -->
                            <span id="btnLoading" class="hidden flex items-center space-x-2">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Menyimpan ke Database...</span>
                            </span>
                            <!-- Progress Bar -->
                            <div id="btnBar" class="hidden absolute bottom-0 left-0 h-1 bg-white/50 w-full animate-pulse"></div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Live Slug Preview Generator
    function updateSlugPreview(text) {
        const preview = document.getElementById('slugPreview');
        if (!text.trim()) {
            preview.innerText = '-';
            return;
        }
        const slug = text.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '');
        preview.innerText = slug + '-xxxxxx';
    }

    // Currency Formatter Preview
    function formatRupiahPreview(amount) {
        const preview = document.getElementById('pricePreview');
        const num = parseInt(amount, 10);
        if (isNaN(num) || num < 0) {
            preview.innerText = 'Terbaca: Rp 0';
            return;
        }
        preview.innerText = 'Terbaca: Rp ' + num.toLocaleString('id-ID');
    }

    // Toggle Product Type (digital vs physical)
    function toggleProductType(type) {
        const digitalContainer = document.getElementById('digitalFileContainer');
        const stockBadge = document.getElementById('stockBadge');
        const stockHelp = document.getElementById('stockHelp');

        if (type === 'digital') {
            digitalContainer.classList.remove('hidden');
            stockBadge.innerText = '(opsional untuk digital)';
            stockHelp.innerText = 'Digital: Kosong = stok tanpa batas.';
        } else {
            digitalContainer.classList.add('hidden');
            stockBadge.innerText = '(wajib untuk fisik)';
            stockHelp.innerText = 'Fisik: Masukkan jumlah stok barang yang tersedia di gudang/toko.';
        }
    }

    // Preview File Uploaded Image
    function previewUploadedImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('imagePreview');
                const placeholder = document.getElementById('imagePlaceholder');
                img.src = e.target.result;
                img.classList.remove('hidden');
                placeholder.classList.add('hidden');
            }
            reader.readAsDataURL(file);
        }
    }

    // Preview URL Image
    function previewUrlImage(url) {
        const img = document.getElementById('imagePreview');
        const placeholder = document.getElementById('imagePlaceholder');
        if (url && url.trim().length > 5) {
            img.src = url;
            img.onload = function() {
                img.classList.remove('hidden');
                placeholder.classList.add('hidden');
            };
            img.onerror = function() {
                img.classList.add('hidden');
                placeholder.classList.remove('hidden');
            };
        }
    }

    // Form Submit Loading Bar & Button Animation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('productForm');
        if (form) {
            form.addEventListener('submit', function() {
                if (!form.checkValidity()) return;
                const btn = document.getElementById('submitProductBtn');
                const text = document.getElementById('btnText');
                const loading = document.getElementById('btnLoading');
                const bar = document.getElementById('btnBar');

                btn.classList.add('pointer-events-none', 'opacity-85', 'cursor-wait');
                text.classList.add('hidden');
                loading.classList.remove('hidden');
                bar.classList.remove('hidden');
            });
        }
    });
</script>
@endsection
