@extends('layouts.app')

@section('title', 'Edit Produk - POS Market Kita')

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
                <span class="text-slate-200 font-semibold">Edit Produk</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight flex items-center space-x-3">
                <span class="p-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </span>
                <span>Edit Produk (UC-7)</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                UUID: <code class="code-font text-cyan-300">{{ $product->uuid }}</code> | Slug: <code class="code-font text-slate-300">{{ $product->slug }}</code>
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
    <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="productEditForm" class="space-y-6">
        @csrf
        @method('PUT')

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
                            value="{{ old('name', $product->name) }}" 
                            class="w-full px-4 py-3 rounded-xl bg-slate-950/80 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm transition"
                        >
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
                            <span class="text-slate-300">Khusus E-Book, Source Code, Template, Lisensi, Kursus, atau Asset Digital.</span>
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
                                value="{{ old('price', (int)$product->price) }}" 
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/80 border border-slate-700/80 text-white font-semibold placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm transition"
                                oninput="formatRupiahPreview(this.value)"
                            >
                        </div>
                        <div class="mt-1 text-[11px] text-emerald-400 font-medium" id="pricePreview">
                            Terbaca: Rp {{ number_format($product->price, 0, ',', '.') }}
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
                    </div>

                    <!-- Digital File URL -->
                    <div id="digitalFileContainer" class="{{ $product->product_type === 'digital' ? '' : 'hidden' }}">
                        <label for="file_url" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            File Download URL / Link Asset Digital
                        </label>
                        <input 
                            type="url" 
                            id="file_url" 
                            name="file_url" 
                            value="{{ old('file_url', $product->value['file_url'] ?? '') }}" 
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-950/80 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm transition"
                        >
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="deskripsi" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Deskripsi Lengkap Produk
                        </label>
                        <textarea 
                            id="deskripsi" 
                            name="deskripsi" 
                            rows="4" 
                            class="w-full p-3.5 rounded-xl bg-slate-950/80 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm transition"
                        >{{ old('deskripsi', $product->value['deskripsi'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Right 1 Col: Media & Status -->
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
                    <div id="imagePreviewContainer" class="w-full h-44 rounded-xl border border-slate-700 bg-slate-950/50 flex flex-col items-center justify-center p-3 text-center overflow-hidden relative">
                        @if (!empty($product->value['gambar']))
                            <img id="imagePreview" src="{{ $product->value['gambar'] }}" alt="Preview" class="w-full h-full object-cover rounded-lg">
                        @else
                            <img id="imagePreview" src="" alt="Preview" class="hidden w-full h-full object-cover rounded-lg">
                            <div id="imagePlaceholder" class="space-y-2">
                                <svg class="w-10 h-10 mx-auto text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="block text-xs text-slate-400">Pilih file atau URL gambar baru</span>
                            </div>
                        @endif
                    </div>

                    <!-- File Upload Input -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Ganti File Gambar:</label>
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
                        <label for="gambar_url" class="block text-[11px] font-semibold text-slate-300 mb-1">Atau Ubah URL Gambar:</label>
                        <input 
                            type="url" 
                            id="gambar_url" 
                            name="gambar_url" 
                            value="{{ old('gambar_url', $product->value['gambar'] ?? '') }}" 
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
                            value="{{ old('stock', $product->stock) }}" 
                            placeholder="Contoh: 100 (kosongkan jika unlimited)" 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950/80 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-xs transition"
                        >
                        <p class="text-[10px] text-slate-400 mt-1">
                            Jumlah persediaan unit. Kosongkan jika produk digital ini tidak memiliki batas stok (Unlimited).
                        </p>
                    </div>

                    <!-- Active Toggle -->
                    <div class="pt-3 border-t border-slate-800/80">
                        <label class="flex items-center justify-between cursor-pointer">
                            <div>
                                <span class="text-xs font-bold text-white block">Status Produk Aktif</span>
                                <span class="text-[11px] text-slate-400">Tampilkan di POS / Website Penjual</span>
                            </div>
                            <input 
                                type="checkbox" 
                                name="is_active" 
                                value="1" 
                                class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 bg-slate-950 border-slate-700"
                                {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                            >
                        </label>
                    </div>

                    <!-- Submit Button with Loading Indicator -->
                    <div class="pt-4 border-t border-slate-800">
                        <button 
                            type="submit" 
                            id="submitEditBtn"
                            class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-amber-600 to-brand-600 hover:from-amber-500 hover:to-brand-500 text-white font-bold text-sm shadow-lg shadow-brand-500/25 hover:shadow-brand-500/40 focus:outline-none focus:ring-2 focus:ring-brand-500 transition active:scale-[0.98] flex items-center justify-center relative overflow-hidden group"
                        >
                            <!-- Normal State -->
                            <span id="editBtnText" class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Perbarui Data Produk</span>
                            </span>
                            <!-- Loading State -->
                            <span id="editBtnLoading" class="hidden flex items-center space-x-2">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Menyimpan Perubahan...</span>
                            </span>
                            <!-- Progress Bar -->
                            <div id="editBtnBar" class="hidden absolute bottom-0 left-0 h-1 bg-white/50 w-full animate-pulse"></div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function formatRupiahPreview(amount) {
        const preview = document.getElementById('pricePreview');
        const num = parseInt(amount, 10);
        if (isNaN(num) || num < 0) {
            preview.innerText = 'Terbaca: Rp 0';
            return;
        }
        preview.innerText = 'Terbaca: Rp ' + num.toLocaleString('id-ID');
    }

    function toggleProductType(type) {
        const digitalContainer = document.getElementById('digitalFileContainer');
        if (type === 'digital') {
            digitalContainer.classList.remove('hidden');
        } else {
            digitalContainer.classList.add('hidden');
        }
    }

    function previewUploadedImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('imagePreview');
                img.src = e.target.result;
                img.classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        }
    }

    function previewUrlImage(url) {
        const img = document.getElementById('imagePreview');
        if (url && url.trim().length > 5) {
            img.src = url;
            img.classList.remove('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('productEditForm');
        if (form) {
            form.addEventListener('submit', function() {
                if (!form.checkValidity()) return;
                const btn = document.getElementById('submitEditBtn');
                const text = document.getElementById('editBtnText');
                const loading = document.getElementById('editBtnLoading');
                const bar = document.getElementById('editBtnBar');

                btn.classList.add('pointer-events-none', 'opacity-85', 'cursor-wait');
                text.classList.add('hidden');
                loading.classList.remove('hidden');
                bar.classList.remove('hidden');
            });
        }
    });
</script>
@endsection
