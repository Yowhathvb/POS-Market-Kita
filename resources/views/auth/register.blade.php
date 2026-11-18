@extends('layouts.app')

@section('title', 'Daftar Pengguna Baru')

@section('content')
<div class="max-w-2xl mx-auto my-4">
    <div class="glass-card rounded-2xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
        <div class="absolute -top-20 -left-20 w-44 h-44 bg-brand-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 mb-4 shadow-inner">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Registrasi Akun Baru</h2>
            <p class="text-sm text-slate-400 mt-1">Daftarkan akun untuk mengisi data tabel <code class="code-font text-brand-300">users</code></p>
        </div>

        <form action="{{ route('register.post') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Grid 1: Nama & Username -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nama" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Nama Lengkap <span class="text-rose-400">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="nama" 
                        name="nama" 
                        value="{{ old('nama') }}" 
                        required 
                        placeholder="Budi Santoso" 
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm transition"
                    >
                </div>
                <div>
                    <label for="username" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Username <span class="text-rose-400">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="{{ old('username') }}" 
                        required 
                        placeholder="budisantoso" 
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm transition"
                    >
                </div>
            </div>

            <!-- Email Field -->
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Alamat Email <span class="text-rose-400">*</span>
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    placeholder="budi@example.com" 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm transition"
                >
            </div>

            <!-- Auto-generated Info Badge (id_users & role) -->
            <div class="p-3.5 rounded-xl bg-brand-950/40 border border-brand-500/30 text-xs text-brand-200 flex items-start space-x-3">
                <svg class="w-5 h-5 text-brand-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="space-y-1">
                    <p class="font-semibold text-white">Ketentuan Akun Baru:</p>
                    <p class="text-slate-300 leading-relaxed">
                        • <strong>ID Users</strong> akan dibuat otomatis oleh sistem dengan format <code class="code-font text-cyan-300 font-bold bg-slate-900/80 px-1.5 py-0.5 rounded">POS-XXXXXXXX</code> (huruf & angka acak).<br>
                        • <strong>Role</strong> otomatis ditetapkan sebagai <span class="px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-semibold border border-emerald-500/30">user</span> (Penjual).
                    </p>
                </div>
            </div>

            <!-- Grid 3: Password & Confirmation -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Password <span class="text-rose-400">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        placeholder="Min. 6 karakter" 
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm transition"
                    >
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Konfirmasi Password <span class="text-rose-400">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        required 
                        placeholder="Ulangi password" 
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm transition"
                    >
                </div>
            </div>

            <!-- Section: Data Kolom 'value' (JSON) -->
            <div class="pt-4 border-t border-slate-800">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-cyan-400 flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z" />
                        </svg>
                        <span>Data Kolom 'value' (JSON Type)</span>
                    </span>
                    <button type="button" onclick="toggleAdvancedJson()" class="text-xs text-brand-400 hover:text-brand-300 underline">
                        <span id="jsonToggleText">Tampilkan Editor JSON Mentah</span>
                    </button>
                </div>
                <p class="text-xs text-slate-400 mb-3">
                    Data di bawah akan otomatis disimpan ke dalam kolom bertipe JSON <code class="code-font text-amber-300">value</code> pada tabel <code class="code-font text-white">users</code>.
                </p>

                <!-- Quick form fields for JSON -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-3">
                    <div>
                        <label for="phone" class="block text-xs text-slate-300 mb-1">No. WhatsApp / Telepon</label>
                        <input 
                            type="text" 
                            id="phone" 
                            name="phone" 
                            value="{{ old('phone') }}" 
                            placeholder="08123456789" 
                            class="w-full px-3 py-2 rounded-lg bg-slate-900/80 border border-slate-700/80 text-white placeholder-slate-500 text-sm"
                        >
                    </div>
                    <div>
                        <label for="address" class="block text-xs text-slate-300 mb-1">Alamat Domisili</label>
                        <input 
                            type="text" 
                            id="address" 
                            name="address" 
                            value="{{ old('address') }}" 
                            placeholder="Jl. Thamrin No. 10, Jakarta" 
                            class="w-full px-3 py-2 rounded-lg bg-slate-900/80 border border-slate-700/80 text-white placeholder-slate-500 text-sm"
                        >
                    </div>
                </div>

                <!-- Raw JSON Field (Collapsible) -->
                <div id="advancedJsonContainer" class="hidden space-y-2 mt-3">
                    <div class="flex items-center justify-between">
                        <label for="value_json" class="block text-xs text-slate-300">
                            Payload JSON Tambahan (Kustom)
                        </label>
                        <button type="button" onclick="fillJsonTemplate()" class="text-[11px] px-2 py-0.5 bg-slate-800 text-brand-300 hover:bg-slate-700 rounded border border-slate-700">
                            Isi Template Sample
                        </button>
                    </div>
                    <textarea 
                        id="value_json" 
                        name="value_json" 
                        rows="3" 
                        placeholder='{"preferences": {"theme": "dark", "language": "id"}, "membership": "gold"}' 
                        class="w-full p-3 rounded-lg bg-slate-950/80 border border-slate-700/80 text-emerald-400 placeholder-slate-600 code-font text-xs focus:ring-2 focus:ring-brand-500 outline-none"
                    >{{ old('value_json') }}</textarea>
                </div>
            </div>

            <!-- Submit Button with Loading Indicator -->
            <button 
                type="submit" 
                id="registerBtn"
                class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-semibold text-sm shadow-lg shadow-brand-500/25 hover:shadow-brand-500/40 focus:outline-none focus:ring-2 focus:ring-brand-500 transition active:scale-[0.98] flex items-center justify-center relative overflow-hidden group"
            >
                <!-- Normal State -->
                <span id="registerBtnText" class="flex items-center space-x-2">
                    <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    <span>Daftarkan Akun</span>
                </span>
                <!-- Loading State (hidden initially) -->
                <span id="registerBtnLoading" class="hidden flex items-center space-x-2">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Memproses Pendaftaran...</span>
                </span>
                <!-- Bottom progress bar animation on loading -->
                <div id="registerBtnBar" class="hidden absolute bottom-0 left-0 h-1 bg-white/50 w-full animate-pulse"></div>
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-slate-400">
            Sudah memiliki akun? 
            <a href="{{ route('login') }}" class="font-semibold text-brand-400 hover:text-brand-300 ml-1 underline decoration-brand-400/40 underline-offset-4">
                Masuk di sini
            </a>
        </div>
    </div>
</div>

<script>
    function toggleAdvancedJson() {
        const container = document.getElementById('advancedJsonContainer');
        const text = document.getElementById('jsonToggleText');
        if (container.classList.contains('hidden')) {
            container.classList.remove('hidden');
            text.innerText = 'Sembunyikan Editor JSON Mentah';
        } else {
            container.classList.add('hidden');
            text.innerText = 'Tampilkan Editor JSON Mentah';
        }
    }

    function fillJsonTemplate() {
        const sample = {
            "preferences": {
                "theme": "dark",
                "language": "id",
                "notifications": true
            },
            "membership_tier": "VIP",
            "pos_branch": "Jakarta Pusat"
        };
        document.getElementById('value_json').value = JSON.stringify(sample, null, 2);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                if (!form.checkValidity()) return;
                const btn = document.getElementById('registerBtn');
                const text = document.getElementById('registerBtnText');
                const loading = document.getElementById('registerBtnLoading');
                const bar = document.getElementById('registerBtnBar');
                
                btn.classList.add('pointer-events-none', 'opacity-85', 'cursor-wait');
                text.classList.add('hidden');
                loading.classList.remove('hidden');
                if (bar) bar.classList.remove('hidden');
            });
        }
    });
</script>
@endsection
