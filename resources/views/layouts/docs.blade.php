<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950 scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dokumentasi API POS Market Kita') - Public Developer Portal</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #1e1b4b 0%, #0f172a 40%, #020617 100%);
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .code-font {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.6);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.4);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.7);
        }
    </style>
    @stack('styles')
</head>
<body class="text-slate-100 min-h-screen flex flex-col selection:bg-brand-500 selection:text-white">

    <!-- Top Navigation Bar (Public & Dedicated) -->
    <header class="border-b border-slate-800/80 bg-slate-950/85 backdrop-blur sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            
            <!-- Brand Logo & Documentation Title -->
            <div class="flex items-center space-x-3">
                <a href="{{ route('docs') }}" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="font-extrabold text-lg text-white tracking-tight">POS<span class="text-emerald-400">Market</span></span>
                            <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 rounded-full border border-emerald-500/40">
                                Public Docs
                            </span>
                        </div>
                        <span class="block text-[10px] text-slate-400 uppercase tracking-widest font-semibold">API Documentation Portal</span>
                    </div>
                </a>
            </div>

            <!-- Quick Jump Category Navigation (Desktop) -->
            <nav class="hidden md:flex items-center space-x-1 text-xs">
                <a href="#overview" class="px-2.5 py-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800/70 transition">Overview</a>
                <a href="#register-endpoint" class="px-2.5 py-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800/70 transition">Auth</a>
                <a href="#products-api" class="px-2.5 py-1.5 rounded-lg text-emerald-300 hover:text-white hover:bg-slate-800/70 transition">Produk</a>
                <a href="#wallet-api" class="px-2.5 py-1.5 rounded-lg text-amber-300 hover:text-white hover:bg-slate-800/70 transition">Wallet</a>
                <a href="#transactions-api" class="px-2.5 py-1.5 rounded-lg text-sky-300 hover:text-white hover:bg-slate-800/70 transition">QRIS Midtrans</a>
                <a href="#magic-link" class="px-2.5 py-1.5 rounded-lg text-cyan-300 hover:text-white hover:bg-slate-800/70 transition">Magic Link</a>
            </nav>

            <!-- User Auth Status / Dashboard Link -->
            <div class="flex items-center space-x-2 sm:space-x-3">
                @auth
                    <div class="hidden sm:flex items-center space-x-2 px-3 py-1.5 rounded-xl bg-slate-900/80 border border-slate-800 text-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-slate-300 font-medium">{{ Auth::user()->nama }}</span>
                    </div>
                    <a href="{{ route('dashboard') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-gradient-to-r from-brand-600 to-cyan-600 hover:from-brand-500 hover:to-cyan-500 text-white shadow-md shadow-brand-500/20 transition flex items-center space-x-1.5">
                        <span>Dashboard Penjual</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-gradient-to-r from-emerald-600 to-cyan-600 hover:from-emerald-500 hover:to-cyan-500 text-white shadow-md shadow-emerald-500/20 transition">
                        Daftar Akun
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Documentation Layout (Sidebar + Content) -->
    <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1">
        <div class="flex flex-col lg:flex-row gap-8 items-start">
            
            <!-- Sticky Documentation Table of Contents Sidebar (Desktop) -->
            <aside class="hidden lg:block w-64 flex-shrink-0 sticky top-24 space-y-4">
                <div class="glass-card rounded-2xl p-4 text-xs space-y-3 border border-slate-800">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <span class="font-bold text-slate-300 uppercase tracking-wider text-[11px] flex items-center space-x-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                            </svg>
                            <span>Daftar Isi</span>
                        </span>
                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 code-font">v1</span>
                    </div>

                    <nav class="space-y-1 max-h-[calc(100vh-160px)] overflow-y-auto pr-1">
                        <a href="#overview" class="block py-1.5 px-2.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800/80 transition">
                            1. Overview & API Key
                        </a>
                        <a href="#endpoints" class="block py-1.5 px-2.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800/80 transition">
                            2. Ringkasan Endpoint
                        </a>
                        <a href="#register-endpoint" class="block py-1.5 px-2.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800/80 transition">
                            3. Register (POS- & POSM-)
                        </a>
                        <a href="#login-endpoint" class="block py-1.5 px-2.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800/80 transition">
                            4. Login (Kolom Key)
                        </a>
                        <a href="#products-api" class="block py-1.5 px-2.5 rounded-lg text-emerald-400 hover:text-emerald-300 hover:bg-slate-800/80 transition font-medium">
                            5. Produk Digital (/v1/products)
                        </a>
                        <a href="#wallet-api" class="block py-1.5 px-2.5 rounded-lg text-amber-300 hover:text-amber-200 hover:bg-slate-800/80 transition font-medium">
                            6. Wallet & Saldo (/v1/wallet)
                        </a>
                        <a href="#transactions-api" class="block py-1.5 px-2.5 rounded-lg text-sky-400 hover:text-sky-300 hover:bg-slate-800/80 transition font-medium">
                            7. Checkout & QRIS Midtrans
                        </a>
                        <a href="#magic-link" class="block py-1.5 px-2.5 rounded-lg text-cyan-400 hover:text-cyan-300 hover:bg-slate-800/80 transition font-medium">
                            8. Magic Link & Firebase Google
                        </a>
                        <a href="#upload-value" class="block py-1.5 px-2.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800/80 transition">
                            9. Upload Kolom Value (JSON)
                        </a>
                        <a href="#status-codes" class="block py-1.5 px-2.5 rounded-lg text-purple-400 hover:text-purple-300 hover:bg-slate-800/80 transition font-medium">
                            10. Katalog Respon HTTP
                        </a>
                    </nav>

                    <div class="pt-3 border-t border-slate-800">
                        <div class="p-2.5 rounded-xl bg-slate-950/80 border border-slate-800/80 text-[10px] text-slate-400 space-y-1">
                            <span class="font-bold text-slate-300 block">Akses Terbuka Publik</span>
                            <p>Dokumentasi ini dapat diakses secara bebas tanpa harus login terlebih dahulu.</p>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1 min-w-0 w-full space-y-8">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Public Documentation Footer -->
    <footer class="border-t border-slate-800/80 bg-slate-950/90 py-8 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>&copy; {{ date('Y') }} POS Market Kita &bull; Portal Dokumentasi API Terbuka untuk Umum</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('login') }}" class="text-slate-400 hover:text-white transition">Masuk Akun</a>
                <span>&bull;</span>
                <a href="{{ route('register') }}" class="text-slate-400 hover:text-white transition">Daftar Akun Baru</a>
                <span>&bull;</span>
                <a href="#overview" class="text-emerald-400 hover:underline">Kembali ke Atas &uarr;</a>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
