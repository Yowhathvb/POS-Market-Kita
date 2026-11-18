@extends('layouts.app')

@section('title', 'Login Pengguna')

@section('content')
<div class="max-w-md mx-auto my-6">
    <div class="glass-card rounded-2xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
        <!-- Glow accent -->
        <div class="absolute -top-16 -right-16 w-36 h-36 bg-brand-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-500/10 border border-brand-500/30 text-brand-400 mb-4 shadow-inner">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Selamat Datang Kembali</h2>
            <p class="text-sm text-slate-400 mt-1">Masukkan username/email dan password untuk masuk</p>
        </div>

        <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Username or Email -->
            <div>
                <label for="login" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Username atau Email
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        id="login" 
                        name="login" 
                        value="{{ old('login') }}" 
                        required 
                        autofocus
                        placeholder="contoh: budi atau budi@example.com" 
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent text-sm transition"
                    >
                </div>
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                        Password
                    </label>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        placeholder="••••••••" 
                        class="w-full pl-10 pr-11 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent text-sm transition"
                    >
                    <button 
                        type="button" 
                        onclick="togglePasswordVisibility('password', this)" 
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200"
                    >
                        <svg class="w-5 h-5 eye-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Remember me -->
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center space-x-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-brand-600 focus:ring-brand-500 focus:ring-offset-slate-900">
                    <span class="text-xs text-slate-300">Ingat Saya</span>
                </label>
            </div>

            <!-- Submit Button with Loading Indicator -->
            <button 
                type="submit" 
                id="loginBtn"
                class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-semibold text-sm shadow-lg shadow-brand-500/25 hover:shadow-brand-500/40 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition active:scale-[0.98] flex items-center justify-center relative overflow-hidden group"
            >
                <!-- Normal State -->
                <span id="loginBtnText" class="flex items-center space-x-2">
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    <span>Masuk ke Aplikasi</span>
                </span>
                <!-- Loading State (hidden initially) -->
                <span id="loginBtnLoading" class="hidden flex items-center space-x-2">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Memproses Masuk...</span>
                </span>
                <!-- Bottom progress bar animation on loading -->
                <div id="loginBtnBar" class="hidden absolute bottom-0 left-0 h-1 bg-white/50 w-full animate-pulse"></div>
            </button>
        </form>

        <!-- Feature Highlight Box -->
        <div class="mt-6 p-3.5 rounded-xl bg-slate-900/60 border border-slate-800 text-xs text-slate-400 space-y-1.5">
            <div class="flex items-center space-x-1.5 text-brand-300 font-semibold">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Automatisasi Key Login:</span>
            </div>
            <p class="leading-relaxed">
                Setiap kali login berhasil, kolom <code class="code-font text-amber-300">key</code> pada tabel users akan otomatis diperbarui dengan hasil hash dari <strong>12 huruf & angka acak + username</strong>.
            </p>
        </div>

        <div class="mt-6 text-center text-xs text-slate-400">
            Belum memiliki akun? 
            <a href="{{ route('register') }}" class="font-semibold text-brand-400 hover:text-brand-300 ml-1 underline decoration-brand-400/40 underline-offset-4">
                Daftar sekarang
            </a>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility(fieldId, btn) {
        const input = document.getElementById(fieldId);
        if (input.type === 'password') {
            input.type = 'text';
            btn.classList.add('text-brand-400');
        } else {
            input.type = 'password';
            btn.classList.remove('text-brand-400');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                if (!form.checkValidity()) return;
                const btn = document.getElementById('loginBtn');
                const text = document.getElementById('loginBtnText');
                const loading = document.getElementById('loginBtnLoading');
                const bar = document.getElementById('loginBtnBar');
                
                btn.classList.add('pointer-events-none', 'opacity-85', 'cursor-wait');
                text.classList.add('hidden');
                loading.classList.remove('hidden');
                if (bar) bar.classList.remove('hidden');
            });
        }
    });
</script>
@endsection
