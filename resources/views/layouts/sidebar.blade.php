@php
    $currentUser = Auth::user();
    $isAdmin = $currentUser && $currentUser->role === 'admin';
@endphp

<aside class="w-72 h-screen max-h-screen bg-slate-950/95 border-r border-slate-800/80 flex flex-col flex-shrink-0 text-slate-300 sticky top-0 overflow-hidden select-none z-50">
    <!-- App Brand Header -->
    <div class="h-16 px-6 flex items-center justify-between border-b border-slate-800/80 bg-slate-900/40 flex-shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-cyan-500 flex items-center justify-center shadow-lg shadow-brand-500/20 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
            <div>
                <span class="font-extrabold text-base text-white tracking-tight">POS<span class="text-brand-400">Market</span></span>
                <span class="block text-[10px] text-slate-400 uppercase tracking-widest font-semibold">Kita App</span>
            </div>
        </a>
    </div>

    <!-- User Profile Quick Info -->
    <div class="p-4 mx-3 my-3 rounded-xl bg-slate-900/70 border border-slate-800/80 flex items-center space-x-3 flex-shrink-0">
        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-white shadow">
            {{ strtoupper(substr($currentUser->nama ?? 'U', 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
            <h4 class="text-xs font-bold text-white truncate">{{ $currentUser->nama ?? 'Guest' }}</h4>
            <div class="flex items-center space-x-1.5 mt-0.5">
                <span class="text-[10px] code-font text-cyan-300 truncate">{{ $currentUser->id_users ?? 'POS-XXXX' }}</span>
                <span class="px-1.5 py-0.2 rounded text-[9px] font-semibold uppercase tracking-wider {{ $isAdmin ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' }}">
                    {{ $currentUser->role ?? 'user' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Navigation Menu: Scrollable Independently -->
    <nav class="flex-1 px-3 space-y-6 overflow-y-auto py-2 text-xs custom-scrollbar">
        
        <!-- Group 1: Beranda -->
        <div>
            <div class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Menu Utama</div>
            <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 rounded-xl font-medium {{ request()->routeIs('dashboard') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }} transition group">
                <svg class="w-4 h-4 mr-3 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-brand-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Dashboard</span>
                <span class="ml-auto text-[10px] text-slate-400 code-font">UC-4</span>
            </a>
        </div>

        <!-- Group 2: Produk & Katalog (UC 5, 6, 7) -->
        <div>
            <div class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Katalog Produk</div>
            <div class="space-y-1">
                <a href="{{ route('dashboard') }}#produk" class="flex items-center px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/70 transition group">
                    <svg class="w-4 h-4 mr-3 text-indigo-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span>Mengelola Produk</span>
                    <span class="ml-auto text-[10px] text-slate-400 code-font">UC-5</span>
                </a>
                <div class="pl-8 space-y-1">
                    <a href="{{ route('dashboard') }}#input-produk" class="flex items-center py-1.5 px-2.5 rounded-lg text-slate-400 hover:text-indigo-300 text-[11px] transition">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mr-2"></span>
                        <span>Input Produk Baru</span>
                        <span class="ml-auto text-[9px] text-slate-400 code-font">UC-6</span>
                    </a>
                    <a href="{{ route('dashboard') }}#kelola-produk" class="flex items-center py-1.5 px-2.5 rounded-lg text-slate-400 hover:text-indigo-300 text-[11px] transition">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mr-2"></span>
                        <span>Edit & Hapus Produk</span>
                        <span class="ml-auto text-[9px] text-slate-400 code-font">UC-7</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Group 3: Website Penjual (UC 8, 9, 13) -->
        <div>
            <div class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Website Penjual</div>
            <div class="space-y-1">
                <a href="{{ route('dashboard') }}#setting-web" class="flex items-center px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/70 transition group">
                    <svg class="w-4 h-4 mr-3 text-emerald-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Setting Web Penjual</span>
                    <span class="ml-auto text-[10px] text-slate-400 code-font">UC-8</span>
                </a>
                <div class="pl-8 space-y-1">
                    <a href="{{ route('dashboard') }}#template-web" class="flex items-center py-1.5 px-2.5 rounded-lg text-slate-400 hover:text-emerald-300 text-[11px] transition">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span>
                        <span>Pilih Template Web</span>
                        <span class="ml-auto text-[9px] text-slate-400 code-font">UC-9</span>
                    </a>
                    <a href="{{ route('dashboard') }}#preview-web" class="flex items-center py-1.5 px-2.5 rounded-lg text-slate-400 hover:text-emerald-300 text-[11px] transition">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span>
                        <span>Akses Web Penjual</span>
                        <span class="ml-auto text-[9px] text-slate-400 code-font">UC-13</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Group 4: Transaksi & Payment (UC 10, 14, 15) -->
        <div>
            <div class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Transaksi & Order</div>
            <div class="space-y-1">
                <a href="{{ route('dashboard') }}#transaksi" class="flex items-center px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/70 transition group">
                    <svg class="w-4 h-4 mr-3 text-cyan-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Checkout & Transaksi</span>
                    <span class="ml-auto text-[10px] text-slate-400 code-font">UC-14</span>
                </a>
                <a href="{{ route('dashboard') }}#payment" class="flex items-center px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/70 transition group">
                    <svg class="w-4 h-4 mr-3 text-cyan-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Payment (Pembayaran)</span>
                    <span class="ml-auto text-[10px] text-slate-400 code-font">UC-10</span>
                </a>
                <a href="{{ route('dashboard') }}#magic-link" class="flex items-center px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/70 transition group">
                    <svg class="w-4 h-4 mr-3 text-cyan-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    <span>Magic Link Produk</span>
                    <span class="ml-auto text-[10px] text-slate-400 code-font">UC-15</span>
                </a>
            </div>
        </div>

        <!-- Group 5: Wallet & Keuangan (UC 17, 18) -->
        <div>
            <div class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Dompet & Saldo</div>
            <div class="space-y-1">
                <a href="{{ route('dashboard') }}#wallet" class="flex items-center px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/70 transition group">
                    <svg class="w-4 h-4 mr-3 text-amber-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span>Mengelola Saldo (Wallet)</span>
                    <span class="ml-auto text-[10px] text-slate-400 code-font">UC-17</span>
                </a>
                <div class="pl-8 space-y-1">
                    <a href="{{ route('dashboard') }}#tarik-dana" class="flex items-center py-1.5 px-2.5 rounded-lg text-slate-400 hover:text-amber-300 text-[11px] transition">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-2"></span>
                        <span>Withdraw (Tarik Dana)</span>
                        <span class="ml-auto text-[9px] text-slate-400 code-font">UC-18</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Group 6: Integrasi & Dokumentasi API (UC 11 & Menu Docs) -->
        <div>
            <div class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Developer & Integrasi</div>
            <div class="space-y-1">
                <a href="{{ route('dashboard') }}#integrasi-api" class="flex items-center px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/70 transition group">
                    <svg class="w-4 h-4 mr-3 text-brand-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Konfigurasi & Integrasi API</span>
                    <span class="ml-auto text-[10px] text-slate-400 code-font">UC-11</span>
                </a>
                <!-- Sesuai permintaan user: Menu Docs -->
                <a href="{{ route('docs') }}" class="flex items-center px-3 py-2 rounded-xl font-medium {{ request()->routeIs('docs') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }} transition group">
                    <svg class="w-4 h-4 mr-3 {{ request()->routeIs('docs') ? 'text-white' : 'text-emerald-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>Dokumentasi API</span>
                    <span class="ml-auto text-[9px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-bold border border-emerald-500/30">DOCS</span>
                </a>
            </div>
        </div>

        <!-- Group 7: Administrasi (UC 16: Admin Only) -->
        @if ($isAdmin)
            <div>
                <div class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-rose-400 flex items-center space-x-1">
                    <span>Administrasi</span>
                    <span class="px-1 py-0.2 rounded bg-rose-500/20 text-rose-300 text-[9px]">Admin</span>
                </div>
                <a href="{{ route('dashboard') }}#manage-users" class="flex items-center px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-rose-950/40 border border-transparent hover:border-rose-500/30 transition group">
                    <svg class="w-4 h-4 mr-3 text-rose-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Manage User</span>
                    <span class="ml-auto text-[10px] text-slate-400 code-font">UC-16</span>
                </a>
            </div>
        @endif

    </nav>

    <!-- Sidebar Bottom Controls -->
    <div class="p-3 border-t border-slate-800/80 bg-slate-900/50 space-y-2 flex-shrink-0">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center px-3 py-2 rounded-xl text-xs font-semibold bg-rose-600/10 text-rose-400 hover:bg-rose-600/20 border border-rose-500/20 transition">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Keluar (Logout)</span>
            </button>
        </form>
    </div>
</aside>
