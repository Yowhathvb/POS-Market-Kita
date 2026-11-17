@extends('layouts.app')

@section('title', 'Dashboard Penjual - POS Market Kita')
@section('header_title', 'Dashboard Utama Penjual')

@section('content')
<div class="space-y-8">
    <!-- Top Greeting Banner -->
    <div class="glass-card rounded-2xl p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border border-brand-500/20 shadow-2xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div>
            <div class="flex items-center space-x-3 mb-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider 
                    {{ $user->role === 'admin' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/40' : 'bg-brand-500/20 text-brand-300 border border-brand-500/40' }}">
                    Role: {{ $user->role }}
                </span>
                <span class="text-xs text-slate-400">ID Users: <strong class="code-font text-cyan-300">{{ $user->id_users }}</strong></span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                Halo, {{ $user->nama }}! 👋
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Selamat datang di sistem <strong>POS Market Kita</strong>. Kunci keamanan akun Anda telah aktif dan tersinkronisasi.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('docs') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-emerald-600 text-white hover:bg-emerald-500 shadow-lg shadow-emerald-600/20 transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Buka Menu Docs</span>
            </a>
        </div>
    </div>

    <!-- Main Grid: Profil, Kolom Key, Kolom Value -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Card 1: Informasi User & Tabel Users -->
        <div class="glass-card rounded-2xl p-6 shadow-xl space-y-4">
            <div class="flex items-center space-x-2.5 text-brand-400 font-bold text-sm uppercase tracking-wider">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Data Akun (Tabel Users)</span>
            </div>

            <div class="divide-y divide-slate-800/80 text-xs">
                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-400">ID (Auto Increment)</span>
                    <span class="code-font font-bold text-white bg-slate-800/60 px-2 py-0.5 rounded">{{ $user->id }}</span>
                </div>
                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-400">ID Users (Otomatis)</span>
                    <span class="code-font text-cyan-300 font-semibold">{{ $user->id_users }}</span>
                </div>
                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-400">Nama Lengkap</span>
                    <span class="font-medium text-white">{{ $user->nama }}</span>
                </div>
                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-400">Username</span>
                    <span class="code-font text-indigo-300 font-semibold">&#64;{{ $user->username }}</span>
                </div>
                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-400">Email</span>
                    <span class="text-slate-200">{{ $user->email }}</span>
                </div>
                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-400">Role</span>
                    <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase font-semibold text-[11px]">{{ $user->role }}</span>
                </div>
                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-400">Terdaftar Pada</span>
                    <span class="text-slate-400">{{ $user->created_at?->format('d M Y H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Security Key Generator (Dibuat Saat Login) -->
        <div class="glass-card rounded-2xl p-6 shadow-xl space-y-4">
            <div class="flex items-center space-x-2.5 text-amber-400 font-bold text-sm uppercase tracking-wider">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                <span>Kolom 'key' (Dibuat saat Login)</span>
            </div>

            <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-xl text-xs text-amber-200/90 leading-relaxed">
                <strong>Formula:</strong> 12 Karakter Huruf + Angka Acak + Username, lalu di-hash dan disimpan ke kolom <code class="code-font text-amber-300">key</code> saat login.
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1">Nilai Hash Tersimpan di Database (users.key):</label>
                <div class="p-3 rounded-xl bg-slate-950/90 border border-slate-800 text-xs code-font text-amber-300 break-all select-all">
                    {{ $user->key ?? 'Belum ada (akan dibuat otomatis saat login berikutnya)' }}
                </div>
            </div>

            <div class="text-xs text-slate-400 space-y-1">
                <div class="flex items-center space-x-1.5 text-slate-300">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Status Key: <strong class="text-emerald-400">Telah Di-hash & Aktif</strong></span>
                </div>
            </div>
        </div>

        <!-- Card 3: Kolom 'value' (JSON) -->
        <div class="glass-card rounded-2xl p-6 shadow-xl space-y-4">
            <div class="flex items-center space-x-2.5 text-cyan-400 font-bold text-sm uppercase tracking-wider">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z" />
                </svg>
                <span>Kolom 'value' (JSON Type)</span>
            </div>

            <p class="text-xs text-slate-400">
                Data JSON fleksibel yang tersimpan di kolom <code class="code-font text-cyan-300">value</code> akun ini:
            </p>

            <div class="rounded-xl bg-slate-950/90 border border-slate-800 p-3 max-h-48 overflow-auto">
                <pre class="code-font text-xs text-cyan-300">{{ json_encode($user->value ?? ['info' => 'Kosong (null)'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>

            <div class="text-xs text-slate-400">
                <a href="{{ route('docs') }}#upload-value" class="text-brand-400 hover:text-brand-300 underline">
                    Lihat cara upload data baru ke kolom value &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Use Case Modules Quick Grid (Berdasarkan Use Case Diagram POS Market Kita) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-white flex items-center space-x-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span>Modul Sistem POS Market Kita (Berdasarkan Use Case)</span>
            </h2>
            <span class="text-xs text-slate-500">Sesuai Diagram Use Case</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Modul 1: Produk -->
            <div id="produk" class="glass-card rounded-2xl p-5 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold">
                        📦
                    </span>
                    <span class="text-[10px] code-font text-slate-500">UC-5, 6, 7</span>
                </div>
                <h3 class="text-sm font-bold text-white">Mengelola Data Produk</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Manajemen katalog produk penjual, input produk baru, upload gambar, serta edit dan delete produk.
                </p>
                <div class="pt-2 flex items-center space-x-2 text-[11px]">
                    <span class="px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">Input Produk (UC-6)</span>
                    <span class="px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">Edit/Delete (UC-7)</span>
                </div>
            </div>

            <!-- Modul 2: Website Penjual -->
            <div id="setting-web" class="glass-card rounded-2xl p-5 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                        🌐
                    </span>
                    <span class="text-[10px] code-font text-slate-500">UC-8, 9, 13</span>
                </div>
                <h3 class="text-sm font-bold text-white">Website & Toko Penjual</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Konfigurasi halaman website penjualan toko online, pemilihan template etalase, dan link toko.
                </p>
                <div class="pt-2 flex items-center space-x-2 text-[11px]">
                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">Pilih Template (UC-9)</span>
                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">Halaman Toko (UC-13)</span>
                </div>
            </div>

            <!-- Modul 3: Checkout & Payment -->
            <div id="transaksi" class="glass-card rounded-2xl p-5 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="w-8 h-8 rounded-lg bg-cyan-500/20 text-cyan-400 flex items-center justify-center font-bold">
                        💳
                    </span>
                    <span class="text-[10px] code-font text-slate-500">UC-10, 14, 15</span>
                </div>
                <h3 class="text-sm font-bold text-white">Checkout & Pembayaran</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Sistem pemesanan pembeli, integrasi payment gateway, notifikasi email otomatis, serta akses Magic Link.
                </p>
                <div class="pt-2 flex items-center space-x-2 text-[11px]">
                    <span class="px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">Payment (UC-10)</span>
                    <span class="px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">Magic Link (UC-15)</span>
                </div>
            </div>

            <!-- Modul 4: Wallet & Withdraw -->
            <div id="wallet" class="glass-card rounded-2xl p-5 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold">
                        💰
                    </span>
                    <span class="text-[10px] code-font text-slate-500">UC-17, 18</span>
                </div>
                <h3 class="text-sm font-bold text-white">Dompet & Penarikan Dana</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Monitoring saldo penjualan, mutasi pendapatan pesanan, dan pengajuan penarikan dana (Withdraw).
                </p>
                <div class="pt-2 flex items-center space-x-2 text-[11px]">
                    <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-300 border border-amber-500/20">Saldo (UC-17)</span>
                    <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-300 border border-amber-500/20">Withdraw (UC-18)</span>
                </div>
            </div>

            <!-- Modul 5: Integrasi API -->
            <div id="integrasi-api" class="glass-card rounded-2xl p-5 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="w-8 h-8 rounded-lg bg-brand-500/20 text-brand-400 flex items-center justify-center font-bold">
                        ⚡
                    </span>
                    <span class="text-[10px] code-font text-slate-500">UC-11</span>
                </div>
                <h3 class="text-sm font-bold text-white">Integrasi & API Key</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Koneksi REST API untuk aplikasi kasir pihak ketiga dengan autentikasi API Key terenkripsi.
                </p>
                <div class="pt-2">
                    <a href="{{ route('docs') }}" class="text-[11px] text-emerald-400 hover:underline flex items-center space-x-1">
                        <span>Buka Dokumentasi API Lengkap &rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Modul 6: Admin Manage User -->
            @if ($user->role === 'admin')
                <div id="manage-users" class="glass-card rounded-2xl p-5 border border-rose-500/30 bg-rose-950/20 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="w-8 h-8 rounded-lg bg-rose-500/20 text-rose-400 flex items-center justify-center font-bold">
                            👥
                        </span>
                        <span class="text-[10px] code-font text-rose-400">UC-16 (Admin Only)</span>
                    </div>
                    <h3 class="text-sm font-bold text-white">Manage User (Kelola Pengguna)</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Fitur eksklusif administrator untuk memantau, memverifikasi, dan mengelola seluruh akun penjual.
                    </p>
                    <div class="pt-2">
                        <span class="text-[11px] px-2 py-0.5 rounded bg-rose-500/20 text-rose-300 font-semibold border border-rose-500/30">Hak Akses Super Admin</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Live Database Users Table -->
    <div class="glass-card rounded-2xl p-6 shadow-2xl border border-slate-800 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-white flex items-center space-x-2">
                <svg class="w-5 h-5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span>Daftar Pengguna Tersimpan di Database (Tabel users)</span>
            </h3>
            <span class="text-xs text-slate-500">Total: {{ count($allUsers) }} user</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-3">ID</th>
                        <th class="py-3 px-3">ID Users</th>
                        <th class="py-3 px-3">Nama</th>
                        <th class="py-3 px-3">Username</th>
                        <th class="py-3 px-3">Email</th>
                        <th class="py-3 px-3">Role</th>
                        <th class="py-3 px-3">Key (Hash saat Login)</th>
                        <th class="py-3 px-3">Value (JSON)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 code-font">
                    @forelse($allUsers as $u)
                        <tr class="hover:bg-slate-800/30">
                            <td class="py-3 px-3 text-slate-400">{{ $u->id }}</td>
                            <td class="py-3 px-3 text-cyan-300 font-semibold">{{ $u->id_users }}</td>
                            <td class="py-3 px-3 text-white font-sans">{{ $u->nama }}</td>
                            <td class="py-3 px-3 text-indigo-300">&#64;{{ $u->username }}</td>
                            <td class="py-3 px-3 text-slate-300 font-sans">{{ $u->email }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold {{ $u->role === 'admin' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 'bg-slate-800 text-slate-300' }}">
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-[11px] text-amber-300 max-w-xs truncate" title="{{ $u->key }}">
                                {{ $u->key ? Str::limit($u->key, 25) : 'null (belum login)' }}
                            </td>
                            <td class="py-3 px-3 text-[11px] text-cyan-300 max-w-xs truncate" title="{{ json_encode($u->value) }}">
                                {{ $u->value ? json_encode($u->value) : 'null' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-slate-500">Belum ada pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
