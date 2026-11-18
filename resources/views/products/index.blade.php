@extends('layouts.app')

@section('title', 'Halaman Data Produk - POS Market Kita')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header with Breadcrumbs & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-brand-400 transition">Beranda</a>
                <span>/</span>
                <span class="text-slate-200 font-semibold">Halaman Data Produk</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight flex items-center space-x-3">
                <span class="p-2 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </span>
                <span>Halaman Data Produk Digital</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                Data produk terisolasi per akun milik: <strong class="text-white">{{ Auth::user()->nama }}</strong> (<code class="code-font text-cyan-300">{{ Auth::user()->id_users }}</code>).
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('products.create') }}" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-brand-500/20 hover:shadow-brand-500/40 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Input Produk Baru (UC-6)</span>
            </a>
        </div>
    </div>

    <!-- User Data Isolation & API Quick Info Banner -->
    <div class="p-4 rounded-2xl bg-gradient-to-r from-indigo-950/60 via-slate-900/90 to-cyan-950/40 border border-indigo-500/30 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-lg">
        <div class="flex items-start space-x-3">
            <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center flex-shrink-0 mt-0.5 border border-indigo-500/30">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <div>
                <h4 class="text-xs font-bold text-white flex items-center space-x-2">
                    <span>Isolasi Data Produk Aktif</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-semibold">Terkunci per ID User</span>
                </h4>
                <p class="text-[11px] text-slate-300 mt-0.5 leading-relaxed">
                    Sistem memastikan akun Anda hanya mengakses produk yang Anda buat. Pengguna lain tidak dapat melihat atau mengambil produk milik akun Anda.
                </p>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px]">
                    <span class="text-slate-400">Endpoint API Data Produk:</span>
                    <code class="px-2 py-0.5 rounded bg-slate-950 text-cyan-300 code-font border border-slate-800">GET /v1/products?id_users={{ Auth::user()->id_users }}</code>
                    <span class="text-slate-400">atau</span>
                    <code class="px-2 py-0.5 rounded bg-slate-950 text-indigo-300 code-font border border-slate-800">GET /v1/products/user/{{ Auth::user()->id_users }}</code>
                </div>
            </div>
        </div>
        <div class="flex-shrink-0 self-end md:self-center">
            <a href="{{ route('docs') }}#products-api" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs border border-slate-700 font-semibold transition flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Lihat Docs API</span>
            </a>
        </div>
    </div>

    <!-- Success Message Alert -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    <!-- Metric Stat Cards (Digital Products Focus) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Digital Products -->
        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 shadow">
            <div class="flex items-center justify-between text-indigo-400 text-xs">
                <span>Total Produk Digital</span>
                <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
            </div>
            <div class="text-2xl font-black text-white mt-2">{{ $totalProducts }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">E-Book, Script, Template, Lisensi</div>
        </div>

        <!-- Active Products -->
        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 shadow">
            <div class="flex items-center justify-between text-emerald-400 text-xs">
                <span>Status Aktif</span>
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            </div>
            <div class="text-2xl font-black text-white mt-2">{{ $activeCount }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Siap diakses & diunduh pembeli</div>
        </div>

        <!-- Draft Products -->
        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 shadow">
            <div class="flex items-center justify-between text-amber-400 text-xs">
                <span>Draf / Non-aktif</span>
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
            </div>
            <div class="text-2xl font-black text-white mt-2">{{ max(0, $totalProducts - $activeCount) }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Disembunyikan dari katalog</div>
        </div>

        <!-- Digital Delivery Badge -->
        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 shadow">
            <div class="flex items-center justify-between text-cyan-400 text-xs">
                <span>Pengiriman Otomatis</span>
                <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
            </div>
            <div class="text-2xl font-black text-white mt-2">100%</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Via JSON value (file_url)</div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <!-- Search input -->
        <form action="{{ route('products.index') }}" method="GET" class="flex-1 max-w-md flex items-center space-x-2">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 pointer-events-none">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari nama produk digital atau slug..." 
                    class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-950/80 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-xs transition"
                >
            </div>
            <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold border border-slate-700 transition">
                Cari
            </button>
            @if (request('search'))
                <a href="{{ route('products.index') }}" class="px-3 py-2 rounded-xl bg-slate-800/60 hover:bg-slate-700 text-slate-400 hover:text-white text-xs transition" title="Reset Filter">
                    Reset
                </a>
            @endif
        </form>

        <div class="text-xs text-slate-400 flex items-center space-x-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/30 font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 mr-2"></span>
                Katalog Khusus Produk Digital
            </span>
        </div>
    </div>

    <!-- Products Table Card -->
    <div class="rounded-2xl bg-slate-900/80 border border-slate-800 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/80 text-slate-400 uppercase tracking-wider text-[10px] border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Produk Digital</th>
                        <th class="py-3 px-4">Harga</th>
                        <th class="py-3 px-4">Asset / File URL (JSON)</th>
                        <th class="py-3 px-4">Stok</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse ($products as $prod)
                        <tr class="hover:bg-slate-800/40 transition group">
                            <!-- Product Name & Thumbnail -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-3">
                                    @php
                                        $gambarUrl = $prod->value['gambar'] ?? null;
                                        if ($gambarUrl && !str_starts_with($gambarUrl, 'http://') && !str_starts_with($gambarUrl, 'https://') && !str_starts_with($gambarUrl, '//')) {
                                            $gambarUrl = str_starts_with($gambarUrl, '/') ? asset(ltrim($gambarUrl, '/')) : asset('storage/' . $gambarUrl);
                                        }
                                    @endphp
                                    <div class="w-11 h-11 rounded-xl bg-slate-950 border border-slate-700/80 flex items-center justify-center overflow-hidden flex-shrink-0 relative">
                                        @if ($gambarUrl)
                                            <img src="{{ $gambarUrl }}" alt="{{ $prod->name }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                                            <div class="hidden w-full h-full flex items-center justify-center bg-slate-900 text-slate-500">
                                                <svg class="w-5 h-5 text-indigo-400/60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @else
                                            <svg class="w-5 h-5 text-indigo-400/60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-white text-xs truncate">{{ $prod->name }}</div>
                                        <div class="flex items-center space-x-2 mt-0.5">
                                            <span class="code-font text-[10px] text-cyan-400 truncate">{{ $prod->slug }}</span>
                                            <span class="text-[10px] text-slate-500 code-font">#{{ substr($prod->uuid, 0, 8) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Price -->
                            <td class="py-3.5 px-4 whitespace-nowrap font-bold text-white">
                                Rp {{ number_format($prod->price, 0, ',', '.') }}
                            </td>

                            <!-- Digital Asset URL -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if (!empty($prod->value['file_url']))
                                    <a href="{{ $prod->value['file_url'] }}" target="_blank" class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-indigo-500/10 text-indigo-300 hover:text-white hover:bg-indigo-500/20 text-[11px] font-medium border border-indigo-500/30 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        <span class="truncate max-w-[140px]">Link Asset</span>
                                    </a>
                                @else
                                    <span class="text-slate-500 text-[11px] italic">Belum disetel</span>
                                @endif
                            </td>

                            <!-- Stok -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if (!is_null($prod->stock))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-800 text-slate-200 border border-slate-700">
                                        {{ number_format($prod->stock, 0, ',', '.') }} unit
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-500/10 text-indigo-300 border border-indigo-500/30">
                                        Unlimited
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if ($prod->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-700/50 text-slate-400 border border-slate-600">
                                        Draf
                                    </span>
                                @endif
                            </td>

                            <!-- Actions (Edit & Delete UC-7) -->
                            <td class="py-3.5 px-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('products.edit', $prod->id) }}" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition" title="Edit Produk (UC-7)">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('products.destroy', $prod->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini dari katalog?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg bg-slate-800 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 transition" title="Hapus Produk (UC-7)">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-800/60 border border-slate-700/60 flex items-center justify-center text-slate-400">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </div>
                                    <h4 class="text-sm font-bold text-white">Belum ada produk digital di katalog</h4>
                                    <p class="text-xs text-slate-400">
                                        Tambahkan produk digital pertama Anda (E-Book, Template, Source Code, Lisensi) untuk mulai berjualan.
                                    </p>
                                    <div class="pt-2">
                                        <a href="{{ route('products.create') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-semibold text-xs shadow-lg shadow-brand-500/20 transition space-x-1.5">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            <span>Input Produk Digital Pertama</span>
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($products->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/60 flex items-center justify-between text-xs">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
