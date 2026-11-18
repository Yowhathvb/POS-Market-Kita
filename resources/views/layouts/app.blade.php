<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'POS Market Kita') - Sistem POS & Integrasi API</title>
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
            background: radial-gradient(circle at top right, #1e1b4b 0%, #0f172a 50%, #020617 100%);
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .code-font {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Independent Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.4);
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.35);
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.6);
        }
    </style>
    @stack('styles')
</head>
<body class="text-slate-100 {{ Auth::check() ? 'h-screen overflow-hidden flex' : 'min-h-screen flex flex-col' }}">

    @auth
        <!-- Left Sidebar (Fixed Viewport Height, Independent Scroll) -->
        @include('layouts.sidebar')

        <!-- Main Body Wrapper (Independent Scroll) -->
        <div class="flex-1 h-screen max-h-screen flex flex-col min-w-0 overflow-y-auto overflow-x-hidden custom-scrollbar bg-slate-900/60">
            <!-- Top Sticky Bar -->
            <header class="border-b border-slate-800/80 bg-slate-900/80 backdrop-blur sticky top-0 z-40 h-16 flex items-center justify-between px-6 flex-shrink-0">
                <div class="flex items-center space-x-3">
                    <span class="text-sm font-semibold text-slate-300">
                        @yield('header_title', 'Dashboard Penjual')
                    </span>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('docs') }}" target="_blank" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 text-emerald-400 hover:bg-slate-700 hover:text-white border border-emerald-500/30 transition flex items-center space-x-1.5" title="Buka Dokumentasi API Publik di Halaman Terpisah">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>Menu Docs</span>
                        <svg class="w-2.5 h-2.5 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                    <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 border border-slate-700 transition">
                        Dashboard
                    </a>
                </div>
            </header>

            <!-- Main Scrollable Content Area -->
            <main class="flex-1 p-6 max-w-7xl w-full mx-auto">
                <!-- Flash Alert Messages -->
                @if (session('status'))
                    @php $status = session('status'); @endphp
                    <div class="mb-6 p-4 rounded-xl border flex items-start space-x-3 
                        {{ ($status['type'] ?? 'info') === 'success' ? 'bg-emerald-950/40 border-emerald-500/30 text-emerald-200' : 'bg-brand-950/40 border-brand-500/30 text-brand-200' }}">
                        <svg class="w-6 h-6 flex-shrink-0 mt-0.5 {{ ($status['type'] ?? 'info') === 'success' ? 'text-emerald-400' : 'text-brand-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1">
                            @if (!empty($status['title']))
                                <h4 class="font-bold text-sm text-white mb-0.5">{{ $status['title'] }}</h4>
                            @endif
                            <p class="text-sm leading-relaxed">{{ $status['message'] ?? (is_string($status) ? $status : '') }}</p>
                            
                            @if (!empty($status['key_info']))
                                <div class="mt-3 p-3 bg-black/40 rounded-lg border border-slate-700/60 text-xs code-font text-slate-300 space-y-1">
                                    <div><span class="text-slate-500">12 Karakter Acak:</span> <span class="text-amber-400 font-semibold">{{ $status['key_info']['random_12'] }}</span></div>
                                    <div><span class="text-slate-500">Username:</span> <span class="text-cyan-400 font-semibold">{{ $status['key_info']['username'] }}</span></div>
                                    <div><span class="text-slate-500">Kombinasi Mentah:</span> <span class="text-emerald-400 font-semibold">{{ $status['key_info']['raw_key'] }}</span></div>
                                    <div class="truncate"><span class="text-slate-500">Hasil Hash (Tersimpan di DB users.key):</span> <span class="text-brand-300 font-mono">{{ $status['key_info']['hashed_key'] }}</span></div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl border bg-rose-950/40 border-rose-500/30 text-rose-200 flex items-start space-x-3">
                        <svg class="w-6 h-6 flex-shrink-0 mt-0.5 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="flex-1">
                            <h4 class="font-bold text-sm text-white mb-1">Terdapat beberapa kesalahan:</h4>
                            <ul class="list-disc list-inside text-sm space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Authenticated Footer -->
            <footer class="border-t border-slate-800/80 bg-slate-950/60 mt-auto py-4 px-6 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2 flex-shrink-0">
                <p>&copy; {{ date('Y') }} POS Market Kita &bull; Use Case Implementation System</p>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('docs') }}" class="text-brand-400 hover:underline">Dokumentasi API</a>
                    <span>&bull;</span>
                    <span class="code-font text-slate-400">Endpoint: /v1/auth</span>
                </div>
            </footer>
        </div>
    @else
        <!-- Guest Layout (Login, Register, Public Docs) -->
        <div class="w-full flex flex-col min-h-screen">
            <!-- Guest Top Navigation -->
            <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur sticky top-0 z-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('login') }}" class="flex items-center space-x-3 group">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-cyan-500 flex items-center justify-center shadow-lg shadow-brand-500/20 group-hover:scale-105 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                            <div>
                                <span class="font-extrabold text-lg text-white tracking-tight">POS<span class="text-brand-400">Market</span></span>
                                <span class="hidden sm:inline-block ml-2 px-2 py-0.5 text-xs font-semibold uppercase tracking-wider bg-brand-500/20 text-brand-300 rounded border border-brand-500/30">Kita App</span>
                            </div>
                        </a>
                    </div>

                    <nav class="flex items-center space-x-2 sm:space-x-3">
                        <a href="{{ route('docs') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold {{ request()->routeIs('docs') ? 'bg-emerald-600 text-white' : 'text-emerald-400 hover:bg-slate-800' }} border border-emerald-500/30 transition flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>Menu Docs</span>
                        </a>
                        <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium {{ request()->routeIs('login') ? 'bg-brand-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }} transition">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium {{ request()->routeIs('register') ? 'bg-brand-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }} transition">
                            Daftar
                        </a>
                    </nav>
                </div>
            </header>

            <!-- Guest Main Content -->
            <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Flash Alert Messages -->
                @if (session('status'))
                    @php $status = session('status'); @endphp
                    <div class="max-w-2xl mx-auto mb-6 p-4 rounded-xl border flex items-start space-x-3 
                        {{ ($status['type'] ?? 'info') === 'success' ? 'bg-emerald-950/40 border-emerald-500/30 text-emerald-200' : 'bg-brand-950/40 border-brand-500/30 text-brand-200' }}">
                        <svg class="w-6 h-6 flex-shrink-0 mt-0.5 {{ ($status['type'] ?? 'info') === 'success' ? 'text-emerald-400' : 'text-brand-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1">
                            @if (!empty($status['title']))
                                <h4 class="font-bold text-sm text-white mb-0.5">{{ $status['title'] }}</h4>
                            @endif
                            <p class="text-sm leading-relaxed">{{ $status['message'] ?? (is_string($status) ? $status : '') }}</p>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="max-w-2xl mx-auto mb-6 p-4 rounded-xl border bg-rose-950/40 border-rose-500/30 text-rose-200 flex items-start space-x-3">
                        <svg class="w-6 h-6 flex-shrink-0 mt-0.5 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="flex-1">
                            <h4 class="font-bold text-sm text-white mb-1">Terdapat beberapa kesalahan:</h4>
                            <ul class="list-disc list-inside text-sm space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Guest Footer -->
            <footer class="border-t border-slate-800/80 bg-slate-950/60 mt-auto py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500">
                    <p>&copy; {{ date('Y') }} POS Market Kita &bull; Endpoint API: <span class="code-font text-brand-400">/v1/auth</span> &bull; API Key Guard</p>
                </div>
            </footer>
        </div>
    @endauth

    @stack('scripts')
</body>
</html>
