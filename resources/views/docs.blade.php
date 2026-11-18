@extends('layouts.app')

@section('title', 'Dokumentasi API POS Market Kita')
@section('header_title', 'Dokumentasi API (/docs)')

@section('content')
<div class="space-y-8">
    <!-- Header Banner -->
    <div class="glass-card rounded-2xl p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border border-emerald-500/20 shadow-2xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div>
            <div class="flex items-center space-x-2.5 mb-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                    Menu Docs
                </span>
                <span class="text-xs text-slate-400">Versi API: <strong class="code-font text-white">v1 (/v1/auth)</strong></span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                Dokumentasi Lengkap API POS Market Kita
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Panduan integrasi autentikasi, API Key, mekanisme pembentukan kolom key, upload data JSON ke kolom value, dan katalog respon HTTP status code.
            </p>
        </div>

        <div class="flex items-center space-x-2 bg-slate-950/80 px-4 py-2.5 rounded-xl border border-slate-800">
            <span class="text-xs text-slate-400">API Key (.env):</span>
            <code class="code-font text-xs text-emerald-400 font-bold select-all">{{ $apiKey }}</code>
        </div>
    </div>

    <!-- Quick Navigation Anchor Links -->
    <div class="flex flex-wrap gap-2 text-xs">
        <a href="#overview" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 border border-slate-700 transition">Overview & Header</a>
        <a href="#endpoints" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 border border-slate-700 transition">Daftar Endpoint</a>
        <a href="#register-endpoint" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 border border-slate-700 transition">POST Register</a>
        <a href="#login-endpoint" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 border border-slate-700 transition">POST Login (Kolom Key)</a>
        <a href="#products-api" class="px-3 py-1.5 rounded-lg bg-slate-800 text-emerald-300 hover:bg-slate-700 border border-emerald-500/30 transition">Produk Digital API (/v1/products)</a>
        <a href="#wallet-api" class="px-3 py-1.5 rounded-lg bg-slate-800 text-amber-300 hover:bg-slate-700 border border-amber-500/30 transition">Wallet & Saldo Penjual (/v1/wallet)</a>
        <a href="#transactions-api" class="px-3 py-1.5 rounded-lg bg-slate-800 text-sky-300 hover:bg-slate-700 border border-sky-500/30 transition">Checkout & QRIS Midtrans (/v1/transactions)</a>
        <a href="#upload-value" class="px-3 py-1.5 rounded-lg bg-slate-800 text-cyan-300 hover:bg-slate-700 border border-cyan-500/30 transition">Upload ke Kolom 'value' (JSON)</a>
        <a href="#status-codes" class="px-3 py-1.5 rounded-lg bg-slate-800 text-purple-300 hover:bg-slate-700 border border-purple-500/30 transition">Semua Respon HTTP (200, 201, 203, ...)</a>
    </div>

    <!-- Section 1: Overview & Authentication Header -->
    <div id="overview" class="glass-card rounded-2xl p-6 sm:p-8 shadow-xl space-y-4">
        <h2 class="text-lg font-bold text-white flex items-center space-x-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
            <span>1. Overview & Autentikasi API Key</span>
        </h2>
        <p class="text-sm text-slate-300 leading-relaxed">
            Semua endpoint di bawah grup <code class="code-font text-brand-300">/v1/auth</code> diproteksi oleh middleware API Key. Nilai API Key disimpan pada file konfigurasi lingkungan <code class="code-font text-amber-300">.env</code> server:
        </p>

        <div class="p-3.5 bg-slate-950 rounded-xl border border-slate-800 text-xs code-font text-slate-300 space-y-1">
            <div class="text-slate-500"># Konfigurasi di file .env</div>
            <div>API_KEY=<span class="text-emerald-400">{{ $apiKey }}</span></div>
        </div>

        <p class="text-xs text-slate-400">
            Setiap request API wajib menyertakan API Key tersebut melalui salah satu metode berikut:
        </p>
        <ul class="list-disc list-inside text-xs text-slate-300 space-y-1 pl-2">
            <li><strong>Header HTTP (Paling Disarankan):</strong> <code class="code-font text-amber-300">X-API-KEY: {{ $apiKey }}</code></li>
            <li><strong>Authorization Header:</strong> <code class="code-font text-amber-300">Authorization: Bearer {{ $apiKey }}</code></li>
            <li><strong>Query Parameter:</strong> <code class="code-font text-amber-300">?api_key={{ $apiKey }}</code></li>
        </ul>
    </div>

    <!-- Section 2: Summary of Endpoints -->
    <div id="endpoints" class="glass-card rounded-2xl p-6 sm:p-8 shadow-xl space-y-4">
        <h2 class="text-lg font-bold text-white flex items-center space-x-2">
            <span class="w-2.5 h-2.5 rounded-full bg-brand-400"></span>
            <span>2. Ringkasan Endpoint API</span>
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-3">Method</th>
                        <th class="py-3 px-3">Endpoint Path</th>
                        <th class="py-3 px-3">Header Wajib</th>
                        <th class="py-3 px-3">Respon HTTP</th>
                        <th class="py-3 px-3">Fungsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 code-font">
                    <tr>
                        <td class="py-3 px-3"><span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold">POST</span></td>
                        <td class="py-3 px-3 text-white">/v1/auth/register</td>
                        <td class="py-3 px-3 text-amber-300">X-API-KEY</td>
                        <td class="py-3 px-3 text-cyan-300 font-bold">201 / 422</td>
                        <td class="py-3 px-3 font-sans text-slate-300">Registrasi user baru (ID otomatis POS-XXXX, role: user, key: null)</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3"><span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold">POST</span></td>
                        <td class="py-3 px-3 text-white">/v1/auth/login</td>
                        <td class="py-3 px-3 text-amber-300">X-API-KEY</td>
                        <td class="py-3 px-3 text-emerald-300 font-bold">200 / 401 / 422</td>
                        <td class="py-3 px-3 font-sans text-slate-300">Login, generate 12 acak + username, dihash ke kolom key</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3"><span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 font-bold">GET</span></td>
                        <td class="py-3 px-3 text-white">/v1/auth/me</td>
                        <td class="py-3 px-3 text-amber-300">X-API-KEY, X-USER-ID</td>
                        <td class="py-3 px-3 text-emerald-300 font-bold">200 / 400 / 404</td>
                        <td class="py-3 px-3 font-sans text-slate-300">Ambil profil user berdasarkan ID Users</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3"><span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold">POST</span></td>
                        <td class="py-3 px-3 text-white">/v1/auth/update-value</td>
                        <td class="py-3 px-3 text-amber-300">X-API-KEY</td>
                        <td class="py-3 px-3 text-emerald-300 font-bold">200 / 422</td>
                        <td class="py-3 px-3 font-sans text-slate-300">Upload/update JSON ke kolom 'value'</td>
                    </tr>
                    <tr class="bg-emerald-500/5">
                        <td class="py-3 px-3"><span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 font-bold">GET</span></td>
                        <td class="py-3 px-3 text-emerald-300 font-semibold">/v1/products?id_users={id}</td>
                        <td class="py-3 px-3 text-amber-300">X-API-KEY, id_users</td>
                        <td class="py-3 px-3 text-emerald-300 font-bold">200 / 400 / 404</td>
                        <td class="py-3 px-3 font-sans text-slate-300">Ambil data produk milik user (Isolasi data per akun, tidak perlu key tetapi wajib id_users)</td>
                    </tr>
                    <tr class="bg-emerald-500/5">
                        <td class="py-3 px-3"><span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold">POST</span></td>
                        <td class="py-3 px-3 text-emerald-300 font-semibold">/v1/products</td>
                        <td class="py-3 px-3 text-amber-300">X-API-KEY</td>
                        <td class="py-3 px-3 text-cyan-300 font-bold">201 / 401 / 422</td>
                        <td class="py-3 px-3 font-sans text-slate-300">Input produk digital baru (product_type=digital, stock=null, value JSON)</td>
                    </tr>
                    <tr class="bg-emerald-500/5">
                        <td class="py-3 px-3"><span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 font-bold">GET</span></td>
                        <td class="py-3 px-3 text-emerald-300 font-semibold">/v1/products/{id}</td>
                        <td class="py-3 px-3 text-amber-300">X-API-KEY</td>
                        <td class="py-3 px-3 text-emerald-300 font-bold">200 / 401 / 404</td>
                        <td class="py-3 px-3 font-sans text-slate-300">Detail produk digital by id / uuid / slug</td>
                    </tr>
                    <tr class="bg-emerald-500/5">
                        <td class="py-3 px-3"><span class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-400 font-bold">PUT</span></td>
                        <td class="py-3 px-3 text-emerald-300 font-semibold">/v1/products/{id}</td>
                        <td class="py-3 px-3 text-amber-300">X-API-KEY, key / X-USER-KEY</td>
                        <td class="py-3 px-3 text-emerald-300 font-bold">200 / 401 / 403 / 404 / 422</td>
                        <td class="py-3 px-3 font-sans text-slate-300">Update data produk & stok (Wajib key & verifikasi pemilik produk)</td>
                    </tr>
                    <tr class="bg-emerald-500/5">
                        <td class="py-3 px-3"><span class="px-2 py-0.5 rounded bg-rose-500/20 text-rose-400 font-bold">DELETE</span></td>
                        <td class="py-3 px-3 text-emerald-300 font-semibold">/v1/products/{id}</td>
                        <td class="py-3 px-3 text-amber-300">X-API-KEY, key / X-USER-KEY</td>
                        <td class="py-3 px-3 text-emerald-300 font-bold">200 / 401 / 403 / 404</td>
                        <td class="py-3 px-3 font-sans text-slate-300">Soft delete produk digital (Wajib key & verifikasi pemilik produk)</td>
                    </tr>
                    <tr class="bg-amber-500/5">
                        <td class="py-3 px-3"><span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 font-bold">GET</span></td>
                        <td class="py-3 px-3 text-amber-300 font-semibold">/v1/wallet</td>
                        <td class="py-3 px-3 text-amber-300">X-API-KEY, key / X-USER-KEY</td>
                        <td class="py-3 px-3 text-emerald-300 font-bold">200 / 401</td>
                        <td class="py-3 px-3 font-sans text-slate-300">Ambil data saldo wallet penjual, rekening bank & mutasi</td>
                    </tr>
                    <tr class="bg-amber-500/5">
                        <td class="py-3 px-3"><span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold">POST</span></td>
                        <td class="py-3 px-3 text-amber-300 font-semibold">/v1/wallet/bank-account</td>
                        <td class="py-3 px-3 text-amber-300">X-API-KEY, key / X-USER-KEY</td>
                        <td class="py-3 px-3 text-emerald-300 font-bold">200 / 401 / 422</td>
                        <td class="py-3 px-3 font-sans text-slate-300">Simpan atau perbarui data rekening bank penarikan saldo</td>
                    </tr>
                    <tr class="bg-amber-500/5">
                        <td class="py-3 px-3"><span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold">POST</span></td>
                        <td class="py-3 px-3 text-amber-300 font-semibold">/v1/wallet/withdraw</td>
                        <td class="py-3 px-3 text-amber-300">X-API-KEY, key / X-USER-KEY</td>
                        <td class="py-3 px-3 text-cyan-300 font-bold">201 / 400 / 401 / 422</td>
                        <td class="py-3 px-3 font-sans text-slate-300">Permintaan penarikan saldo (minimal Rp 50.000)</td>
                    </tr>
                    <tr class="bg-sky-500/5">
                        <td class="py-3 px-3"><span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 font-bold">GET</span></td>
                        <td class="py-3 px-3 text-sky-300 font-semibold">/v1/transactions</td>
                        <td class="py-3 px-3 text-amber-300">X-API-KEY, key / X-USER-KEY (atau id_users)</td>
                        <td class="py-3 px-3 text-emerald-300 font-bold">200 / 401 / 404</td>
                        <td class="py-3 px-3 font-sans text-slate-300">Lihat seluruh transaksi pada produk penjual, omzet & statistik pesanan</td>
                    </tr>
                    <tr class="bg-sky-500/5">
                        <td class="py-3 px-3"><span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 font-bold">GET</span></td>
                        <td class="py-3 px-3 text-sky-300 font-semibold">/v1/transactions/{invoice}</td>
                        <td class="py-3 px-3 text-amber-300">X-API-KEY, key / X-USER-KEY</td>
                        <td class="py-3 px-3 text-emerald-300 font-bold">200 / 401 / 403 / 404</td>
                        <td class="py-3 px-3 font-sans text-slate-300">Detail transaksi produk penjual (Wajib key & verifikasi kepemilikan)</td>
                    </tr>
                    <tr class="bg-sky-500/5">
                        <td class="py-3 px-3"><span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold">POST</span></td>
                        <td class="py-3 px-3 text-sky-300 font-semibold">/v1/transactions/checkout</td>
                        <td class="py-3 px-3 text-slate-400">Public / X-API-KEY</td>
                        <td class="py-3 px-3 text-cyan-300 font-bold">201 / 400 / 404 / 422</td>
                        <td class="py-3 px-3 font-sans text-slate-300">Checkout pembeli: buat invoice & QRIS Midtrans asli</td>
                    </tr>
                    <tr class="bg-sky-500/5">
                        <td class="py-3 px-3"><span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 font-bold">GET</span></td>
                        <td class="py-3 px-3 text-sky-300 font-semibold">/v1/transactions/{invoice}/status</td>
                        <td class="py-3 px-3 text-slate-400">Public / X-API-KEY</td>
                        <td class="py-3 px-3 text-emerald-300 font-bold">200 / 404</td>
                        <td class="py-3 px-3 font-sans text-slate-300">Cek status pembayaran invoice & unduh file produk bila lunas</td>
                    </tr>
                    <tr class="bg-sky-500/5">
                        <td class="py-3 px-3"><span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold">POST</span></td>
                        <td class="py-3 px-3 text-sky-300 font-semibold">/v1/transactions/{invoice}/simulate-payment</td>
                        <td class="py-3 px-3 text-slate-400">Public / X-API-KEY</td>
                        <td class="py-3 px-3 text-emerald-300 font-bold">200 / 400 / 404</td>
                        <td class="py-3 px-3 font-sans text-slate-300">Simulasi bayar QRIS langsung lunas (Sandbox Midtrans)</td>
                    </tr>
                    <tr class="bg-sky-500/5">
                        <td class="py-3 px-3"><span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold">POST</span></td>
                        <td class="py-3 px-3 text-sky-300 font-semibold">/v1/payments/midtrans-notification</td>
                        <td class="py-3 px-3 text-slate-400">Midtrans Webhook</td>
                        <td class="py-3 px-3 text-emerald-300 font-bold">200 / 400 / 404</td>
                        <td class="py-3 px-3 font-sans text-slate-300">Webhook Midtrans: verifikasi signature, auto -1 stok, +saldo & kirim email</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3"><span class="px-2 py-0.5 rounded bg-purple-500/20 text-purple-400 font-bold">GET</span></td>
                        <td class="py-3 px-3 text-white">/v1/auth/status-demo/{code}</td>
                        <td class="py-3 px-3 text-amber-300">X-API-KEY</td>
                        <td class="py-3 px-3 text-purple-300 font-bold">200, 201, 203...</td>
                        <td class="py-3 px-3 font-sans text-slate-300">Simulasi uji coba seluruh HTTP Status Code</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 3: Detail Endpoint Register -->
    <div id="register-endpoint" class="glass-card rounded-2xl p-6 sm:p-8 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h2 class="text-base font-bold text-white flex items-center space-x-2">
                <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-mono font-bold text-xs">POST</span>
                <span class="code-font text-emerald-300">/v1/auth/register</span>
            </h2>
            <span class="text-xs text-slate-400">Respon: 201 Created</span>
        </div>

        <p class="text-xs text-slate-300 leading-relaxed">
            Mendaftarkan pengguna baru ke tabel <code class="code-font text-white">users</code>.
            Secara default:
            <br>• <strong>id_users</strong> dibuat otomatis dengan format <code class="code-font text-cyan-300">POS-XXXXXXXX</code> (huruf & angka acak lebih dari 5 karakter).
            <br>• <strong>role</strong> otomatis bernilai <code class="code-font text-emerald-300">user</code>.
            <br>• <strong>key</strong> bernilai <code class="code-font text-slate-400">null</code> (karena hanya dibuat ketika user login).
        </p>

        <!-- Form fields table -->
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 pt-2">Form Parameter yang Diisi:</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400">
                        <th class="py-2 px-3">Field</th>
                        <th class="py-2 px-3">Tipe</th>
                        <th class="py-2 px-3">Wajib?</th>
                        <th class="py-2 px-3">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 code-font">
                    <tr><td class="py-2 px-3 text-white">nama</td><td class="py-2 px-3 text-indigo-300">string</td><td class="py-2 px-3 text-rose-400">Ya</td><td class="py-2 px-3 font-sans text-slate-300">Nama lengkap pengguna</td></tr>
                    <tr><td class="py-2 px-3 text-white">username</td><td class="py-2 px-3 text-indigo-300">string</td><td class="py-2 px-3 text-rose-400">Ya</td><td class="py-2 px-3 font-sans text-slate-300">Username unik (alpha_dash, min 3 char)</td></tr>
                    <tr><td class="py-2 px-3 text-white">email</td><td class="py-2 px-3 text-indigo-300">string</td><td class="py-2 px-3 text-rose-400">Ya</td><td class="py-2 px-3 font-sans text-slate-300">Email unik dan valid</td></tr>
                    <tr><td class="py-2 px-3 text-white">password</td><td class="py-2 px-3 text-indigo-300">string</td><td class="py-2 px-3 text-rose-400">Ya</td><td class="py-2 px-3 font-sans text-slate-300">Password minimal 6 karakter</td></tr>
                    <tr><td class="py-2 px-3 text-white">password_confirmation</td><td class="py-2 px-3 text-indigo-300">string</td><td class="py-2 px-3 text-rose-400">Ya</td><td class="py-2 px-3 font-sans text-slate-300">Konfirmasi password sama dengan password</td></tr>
                    <tr><td class="py-2 px-3 text-cyan-300">value</td><td class="py-2 px-3 text-indigo-300">object/json</td><td class="py-2 px-3 text-slate-400">Opsional</td><td class="py-2 px-3 font-sans text-slate-300">Data JSON tambahan (telepon, alamat, preferensi, dsb.)</td></tr>
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
            <div>
                <span class="text-xs font-semibold text-slate-300 mb-1 block">Contoh Request Body (JSON):</span>
                <pre class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs code-font text-emerald-300 overflow-x-auto">{
  "nama": "Andi Pratama",
  "username": "andipratama",
  "email": "andi@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "value": {
    "phone": "081234567890",
    "address": "Jakarta Barat"
  }
}</pre>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-300 mb-1 block">Contoh Respon (201 Created):</span>
                <pre class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs code-font text-cyan-300 overflow-x-auto">{
  "status": "success",
  "code": 201,
  "message": "User successfully registered.",
  "data": {
    "user": {
      "id": 5,
      "id_users": "POS-M8K9A2P1",
      "nama": "Andi Pratama",
      "username": "andipratama",
      "email": "andi@example.com",
      "role": "user",
      "key": null,
      "value": {
        "phone": "081234567890",
        "address": "Jakarta Barat"
      }
    }
  }
}</pre>
            </div>
        </div>
    </div>

    <!-- Section 4: Detail Endpoint Login & Pembuatan Kolom Key -->
    <div id="login-endpoint" class="glass-card rounded-2xl p-6 sm:p-8 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h2 class="text-base font-bold text-white flex items-center space-x-2">
                <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-mono font-bold text-xs">POST</span>
                <span class="code-font text-emerald-300">/v1/auth/login</span>
            </h2>
            <span class="text-xs text-slate-400">Respon: 200 OK</span>
        </div>

        <div class="p-3.5 bg-amber-500/10 border border-amber-500/20 rounded-xl text-xs text-amber-200 leading-relaxed">
            <strong>Mekanisme Pembentukan Kolom 'key':</strong> Saat login berhasil, sistem secara otomatis membangkitkan tepat 12 huruf + angka acak, disambung dengan username user, lalu di-hash dengan Bcrypt dan disimpan ke kolom <code class="code-font text-amber-300">key</code> di tabel <code class="code-font text-white">users</code>.
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-xs font-semibold text-slate-300 mb-1 block">Request Body:</span>
                <pre class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs code-font text-slate-200 overflow-x-auto">{
  "login": "budi",
  "password": "password123"
}</pre>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-300 mb-1 block">Contoh Respon (200 OK):</span>
                <pre class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs code-font text-amber-300 overflow-x-auto">{
  "status": "success",
  "code": 200,
  "message": "Login successful.",
  "data": {
    "user": {
      "id": 2,
      "id_users": "POS-X8K1N9Q4",
      "username": "budi",
      "role": "user",
      "key": "$2y$12$N9HwJc8K0pE9b1Y..."
    },
    "login_key_details": {
      "random_12": "x9K2p0L1q8Wa",
      "username": "budi",
      "raw_key_combined": "x9K2p0L1q8Wabudi",
      "hashed_key_stored": "$2y$12$N9HwJc8K0pE9b1Y..."
    }
  }
}</pre>
            </div>
        </div>
    </div>

    <!-- Section: REST API Produk Digital (/v1/products) -->
    <div id="products-api" class="glass-card rounded-2xl p-6 sm:p-8 shadow-xl space-y-6 border border-emerald-500/20">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-800 pb-4">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                    <span>3. REST API Produk Digital (/v1/products)</span>
                </h2>
                <p class="text-xs text-slate-400 mt-1">
                    Sesuai ERD (<code class="code-font text-white">tbl_products</code> & <code class="code-font text-white">tbl_stores</code>) &mdash; <strong class="text-emerald-300">Khusus Produk Digital</strong> (E-Book, Source Code, Template, Lisensi, Aset Digital). Produk fisik sepenuhnya ditiadakan.
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-500/10 text-emerald-300 border border-emerald-500/30">
                    Product Type: digital
                </span>
                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-cyan-500/10 text-cyan-300 border border-cyan-500/30">
                    Stock: null (Unlimited)
                </span>
            </div>
        </div>

        <div class="p-3.5 bg-emerald-950/40 border border-emerald-500/20 rounded-xl text-xs text-emerald-200 leading-relaxed space-y-1">
            <p><strong>Mekanisme Penyimpanan JSON Value:</strong> Link unduhan file digital (<code class="code-font text-emerald-300">file_url</code>), cover gambar (<code class="code-font text-emerald-300">gambar</code>), dan deskripsi tersimpan otomatis di dalam kolom <code class="code-font text-cyan-300">value</code> (JSON) pada database.</p>
        </div>

        <!-- 3.1 POST /v1/products -->
        <div class="space-y-4 pt-2">
            <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                <h3 class="text-sm font-bold text-white flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-mono font-bold text-xs">POST</span>
                    <span class="code-font text-emerald-300">/v1/products</span>
                    <span class="text-xs font-normal text-slate-400">&mdash; Input Produk Digital Baru</span>
                </h3>
                <span class="text-xs text-slate-400">Respon: 201 Created</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400">
                            <th class="py-2 px-3">Field</th>
                            <th class="py-2 px-3">Tipe</th>
                            <th class="py-2 px-3">Wajib?</th>
                            <th class="py-2 px-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 code-font">
                        <tr><td class="py-2 px-3 text-white">name</td><td class="py-2 px-3 text-indigo-300">string</td><td class="py-2 px-3 text-rose-400">Ya</td><td class="py-2 px-3 font-sans text-slate-300">Nama produk digital (maks 200 karakter)</td></tr>
                        <tr><td class="py-2 px-3 text-white">price</td><td class="py-2 px-3 text-indigo-300">numeric</td><td class="py-2 px-3 text-rose-400">Ya</td><td class="py-2 px-3 font-sans text-slate-300">Harga produk digital (min: 0)</td></tr>
                        <tr class="bg-amber-500/10"><td class="py-2 px-3 text-amber-300 font-bold">key</td><td class="py-2 px-3 text-indigo-300">string</td><td class="py-2 px-3 text-rose-400 font-bold">Ya</td><td class="py-2 px-3 font-sans text-amber-200">Key otentikasi user dari tabel <code class="code-font text-white">users</code> (<code class="code-font text-amber-300">users.key</code>) yang didapat saat login. Bisa juga dikirim via header <code class="code-font text-amber-300">X-USER-KEY</code>.</td></tr>
                        <tr><td class="py-2 px-3 text-white">id_users</td><td class="py-2 px-3 text-indigo-300">string</td><td class="py-2 px-3 text-slate-400">Opsional</td><td class="py-2 px-3 font-sans text-slate-300">ID pengguna penjual (misal: POS-X8K1N9Q4). Opsional jika key sudah unik</td></tr>
                        <tr><td class="py-2 px-3 text-white">store_website_id</td><td class="py-2 px-3 text-indigo-300">integer</td><td class="py-2 px-3 text-slate-400">Opsional</td><td class="py-2 px-3 font-sans text-slate-300">ID Toko/Website milik user (opsional relasi ke tbl_stores)</td></tr>
                        <tr><td class="py-2 px-3 text-emerald-300">file_url</td><td class="py-2 px-3 text-indigo-300">string / url</td><td class="py-2 px-3 text-slate-400">Opsional</td><td class="py-2 px-3 font-sans text-slate-300">URL download file digital (disimpan ke JSON value)</td></tr>
                        <tr><td class="py-2 px-3 text-white">gambar</td><td class="py-2 px-3 text-indigo-300">string / url</td><td class="py-2 px-3 text-slate-400">Opsional</td><td class="py-2 px-3 font-sans text-slate-300">URL cover / thumbnail produk (disimpan ke JSON value)</td></tr>
                        <tr><td class="py-2 px-3 text-white">deskripsi</td><td class="py-2 px-3 text-indigo-300">string</td><td class="py-2 px-3 text-slate-400">Opsional</td><td class="py-2 px-3 font-sans text-slate-300">Deskripsi lengkap produk digital</td></tr>
                        <tr><td class="py-2 px-3 text-white">is_active</td><td class="py-2 px-3 text-indigo-300">boolean</td><td class="py-2 px-3 text-slate-400">Opsional</td><td class="py-2 px-3 font-sans text-slate-300">Status produk aktif (default: true)</td></tr>
                        <tr><td class="py-2 px-3 text-cyan-300">value</td><td class="py-2 px-3 text-indigo-300">object / json</td><td class="py-2 px-3 text-slate-400">Opsional</td><td class="py-2 px-3 font-sans text-slate-300">Metadata JSON custom tambahan</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <span class="text-xs font-semibold text-slate-300 mb-1 block">Contoh Request Body (JSON):</span>
                    <pre class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs code-font text-emerald-300 overflow-x-auto">{
  "name": "E-Book Panduan Master REST API Laravel 12",
  "price": 125000,
  "key": "$2y$12$N9HwJc8K0pE9b1Y...",
  "file_url": "https://storage.googleapis.com/assets/ebook-master-laravel.pdf",
  "gambar": "https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c",
  "deskripsi": "Panduan tuntas membangun REST API modern dan scalable.",
  "is_active": true
}</pre>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-300 mb-1 block">Contoh Respon (201 Created):</span>
                    <pre class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs code-font text-cyan-300 overflow-x-auto">{
  "status": "success",
  "code": 201,
  "message": "Produk digital baru berhasil disimpan ke database.",
  "data": {
    "id": 1,
    "uuid": "9e2c4b5d-16a7-47b8-b11c-2f3b9c7e0001",
    "user_id": 2,
    "user_key": "$2y$12$N9HwJc8K0pE9b1Y...",
    "user": {
      "id": 2,
      "id_users": "POS-X8K1N9Q4",
      "username": "budi",
      "nama": "Budi Santoso",
      "key": "$2y$12$N9HwJc8K0pE9b1Y..."
    },
    "name": "E-Book Panduan Master REST API Laravel 12",
    "slug": "e-book-panduan-master-rest-api-laravel-12-a1b2c3",
    "product_type": "digital",
    "price": "125000.00",
    "stock": null,
    "is_active": true,
    "value": {
      "file_url": "https://storage.googleapis.com/assets/ebook-master-laravel.pdf",
      "gambar": "https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c",
      "deskripsi": "Panduan tuntas membangun REST API modern dan scalable.",
      "user_key": "$2y$12$N9HwJc8K0pE9b1Y..."
    }
  }
}</pre>
                </div>
            </div>
        </div>

        <!-- 3.2 GET /v1/products & GET /v1/products/{id} -->
        <div class="space-y-4 pt-4 border-t border-slate-800">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
                <h3 class="text-sm font-bold text-white flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 font-mono font-bold text-xs">GET</span>
                    <span class="code-font text-emerald-300">/v1/products</span>
                    <span class="text-xs font-normal text-slate-400">&mdash; Ambil Data Produk Milik User (Isolasi Data)</span>
                </h3>
                <span class="text-[11px] px-2.5 py-0.5 rounded bg-cyan-500/10 text-cyan-300 border border-cyan-500/30">
                    Tidak Perlu Key &bull; Wajib id_users
                </span>
            </div>

            <div class="p-3.5 bg-blue-950/30 border border-blue-500/20 rounded-xl text-xs text-blue-200 leading-relaxed space-y-1">
                <strong>Aturan Keamanan Data Produk:</strong> Endpoint ini <u>tidak memerlukan key</u>, namun <strong>WAJIB menyertakan <code class="code-font text-cyan-300">id_users</code></strong> (atau header <code class="code-font text-cyan-300">X-USER-ID</code>). Hal ini untuk memastikan bahwa ketika 1 user mengambil data, mereka <strong>hanya mendapatkan produk miliknya sendiri</strong> dan tidak dapat melihat data produk pengguna lain.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                    <span class="font-bold text-white block">Metode Pemanggilan:</span>
                    <ul class="list-disc list-inside space-y-1 text-slate-300 text-[11px]">
                        <li><code class="code-font text-amber-300">?id_users=POS-XXXX</code> : Parameter query identifier pengguna (Wajib)</li>
                        <li><code class="code-font text-amber-300">/v1/products/user/{id_users}</code> : Endpoint path alternatif</li>
                        <li><code class="code-font text-amber-300">X-USER-ID: POS-XXXX</code> : Header HTTP identifier</li>
                        <li><code class="code-font text-slate-400">?search=keyword</code> : Filter nama / slug (Opsional)</li>
                        <li><code class="code-font text-slate-400">?per_page=10</code> : Paginasi data (Opsional)</li>
                    </ul>
                    <pre class="p-2.5 rounded bg-slate-900 code-font text-[11px] text-cyan-300 overflow-x-auto">curl -X GET "{{ url('/v1/products?id_users=POS-X8K1N9Q4') }}" \
  -H "X-API-KEY: {{ $apiKey }}"</pre>
                </div>

                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                    <span class="font-bold text-white block">Detail Produk by ID / UUID / Slug:</span>
                    <p class="text-slate-400 text-[11px]">Dapat dipanggil menggunakan ID integer, UUID, maupun slug URL produk.</p>
                    <pre class="p-2.5 rounded bg-slate-900 code-font text-[11px] text-slate-300 overflow-x-auto">curl -X GET "{{ url('/v1/products/1') }}" \
  -H "X-API-KEY: {{ $apiKey }}"</pre>
                    <p class="text-slate-500 text-[10px]">Kirimkan juga <code class="code-font text-slate-400">?id_users=...</code> untuk memverifikasi kepemilikan produk.</p>
                </div>
            </div>
        </div>

        <!-- 3.3 PUT & DELETE -->
        <div class="space-y-4 pt-4 border-t border-slate-800">
            <div class="p-3.5 bg-amber-950/30 border border-amber-500/20 rounded-xl text-xs text-amber-200 leading-relaxed space-y-1">
                <strong>Ketentuan Keamanan Update & Hapus Produk:</strong> Baik <code class="code-font text-amber-300">PUT</code> maupun <code class="code-font text-rose-300">DELETE</code> <strong>WAJIB menyertakan <code class="code-font text-white">key</code> dari tabel users</strong> (via header <code class="code-font text-amber-300">X-USER-KEY</code> atau body JSON). Sistem secara ketat memverifikasi kepemilikan produk (<code class="code-font text-white">product.user_id === user.id</code>). Upaya mengubah atau menghapus produk milik orang lain otomatis ditolak dengan kode respon <strong class="text-rose-400">403 Forbidden</strong>.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-amber-400 code-font">PUT /v1/products/{id}</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300">200 / 401 / 403 / 404 / 422</span>
                    </div>
                    <p class="text-slate-400 text-[11px]">Update nama, harga, stok, file_url, gambar (ImageKit.io), atau isi metadata JSON value.</p>
                    <pre class="p-2.5 rounded bg-slate-900 code-font text-[11px] text-slate-300 overflow-x-auto">curl -X PUT "{{ url('/v1/products/1') }}" \
  -H "X-API-KEY: {{ $apiKey }}" \
  -H "X-USER-KEY: &lt;key_dari_tabel_users&gt;" \
  -H "Content-Type: application/json" \
  -d '{"name": "E-Book Master Pro", "price": 99000, "stock": 50, "is_active": true}'</pre>
                </div>

                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-rose-400 code-font">DELETE /v1/products/{id}</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-rose-500/20 text-rose-300">200 / 401 / 403 / 404</span>
                    </div>
                    <p class="text-slate-400 text-[11px]">Melakukan Soft Delete produk digital tanpa menghapus histori transaksi.</p>
                    <pre class="p-2.5 rounded bg-slate-900 code-font text-[11px] text-slate-300 overflow-x-auto">curl -X DELETE "{{ url('/v1/products/1') }}" \
  -H "X-API-KEY: {{ $apiKey }}" \
  -H "X-USER-KEY: &lt;key_dari_tabel_users&gt;"</pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: Cara Upload Data ke Kolom 'value' (JSON) -->
    <div id="upload-value" class="glass-card rounded-2xl p-6 sm:p-8 shadow-xl space-y-4">
        <h2 class="text-lg font-bold text-white flex items-center space-x-2">
            <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
            <span>4. Panduan Upload & Update Data ke Kolom 'value' (JSON)</span>
        </h2>
        <p class="text-xs text-slate-300 leading-relaxed">
            Kolom <code class="code-font text-cyan-300">value</code> dapat menyimpan struktur JSON fleksibel apa pun (objek bersarang, array, string, number, boolean). Untuk memperbarui atau mengunggah data ke kolom ini, gunakan endpoint <code class="code-font text-white">POST /v1/auth/update-value</code>.
        </p>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400">
                        <th class="py-2 px-3">Field</th>
                        <th class="py-2 px-3">Tipe</th>
                        <th class="py-2 px-3">Wajib?</th>
                        <th class="py-2 px-3">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 code-font">
                    <tr><td class="py-2 px-3 text-white">id_users</td><td class="py-2 px-3 text-indigo-300">string</td><td class="py-2 px-3 text-rose-400">Ya</td><td class="py-2 px-3 font-sans text-slate-300">Identifier pengguna (misal: POS-X8K1N9Q4 atau username)</td></tr>
                    <tr><td class="py-2 px-3 text-cyan-300">value</td><td class="py-2 px-3 text-indigo-300">object / string</td><td class="py-2 px-3 text-rose-400">Ya</td><td class="py-2 px-3 font-sans text-slate-300">Objek JSON atau String JSON yang ingin diupload</td></tr>
                    <tr><td class="py-2 px-3 text-amber-300">mode</td><td class="py-2 px-3 text-indigo-300">string</td><td class="py-2 px-3 text-slate-400">Opsional</td><td class="py-2 px-3 font-sans text-slate-300"><code class="code-font text-emerald-400">replace</code> (menimpa) atau <code class="code-font text-cyan-400">merge</code> (menggabungkan tanpa menghapus field lama)</td></tr>
                </tbody>
            </table>
        </div>

        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
            <span class="text-xs font-bold text-cyan-400 uppercase tracking-wider">Contoh Request cURL Upload ke Kolom 'value':</span>
            <pre class="text-xs code-font text-slate-300 overflow-x-auto p-2 bg-slate-900 rounded">curl -X POST "{{ url('/v1/auth/update-value') }}" \
  -H "X-API-KEY: {{ $apiKey }}" \
  -H "Content-Type: application/json" \
  -d '{
    "id_users": "POS-X8K1N9Q4",
    "mode": "merge",
    "value": {
      "whatsapp": "081234567890",
      "alamat_toko": "Jl. Gatot Subroto No. 88, Jakarta",
      "bank_account": {
        "bank": "BCA",
        "account_number": "1234567890",
        "account_name": "Budi Santoso"
      },
      "saldo_tersedia": 2500000
    }
  }'</pre>
        </div>
    </div>

    <!-- Section 5: REST API Wallet & Saldo Penjual (/v1/wallet) -->
    <div id="wallet-api" class="glass-card rounded-2xl p-6 sm:p-8 shadow-xl space-y-6 border border-amber-500/20">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-800 pb-4">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                    <span>5. REST API Wallet & Saldo Penjual (/v1/wallet)</span>
                </h2>
                <p class="text-xs text-slate-400 mt-1">
                    Sesuai ERD (<code class="code-font text-white">tbl_wallets</code> & <code class="code-font text-white">tbl_withdrawals</code>) &mdash; Menyimpan saldo hasil penjualan produk digital, mutasi kredit/debit, rekening bank, serta penarikan dana penjual.
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-500/10 text-amber-300 border border-amber-500/30">
                    Wajib Key dari tabel users
                </span>
                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-500/10 text-emerald-300 border border-emerald-500/30">
                    Auto-Credit Saat Lunas
                </span>
            </div>
        </div>

        <div class="p-3.5 bg-amber-500/10 border border-amber-500/20 rounded-xl text-xs text-amber-200 leading-relaxed space-y-1">
            <strong>Kewajiban Key Penjual:</strong> Seluruh endpoint wallet wajib menyertakan nilai <code class="code-font text-white">users.key</code> (yang didapat saat login). Dikirim via header <code class="code-font text-amber-300">X-USER-KEY</code> atau body parameter <code class="code-font text-amber-300">key</code>, serta <code class="code-font text-white">X-API-KEY</code> pada header.
        </div>

        <!-- 5.1 GET /v1/wallet -->
        <div class="space-y-3 pt-2">
            <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                <h3 class="text-sm font-bold text-white flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 font-mono font-bold text-xs">GET</span>
                    <span class="code-font text-amber-300">/v1/wallet</span>
                    <span class="text-xs font-normal text-slate-400">&mdash; Ambil Data Saldo & Histori Mutasi</span>
                </h3>
                <span class="text-xs text-slate-400">Respon: 200 OK</span>
            </div>

            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                <span class="text-xs font-bold text-amber-400 uppercase tracking-wider">Contoh Request cURL:</span>
                <pre class="p-2.5 rounded bg-slate-900 code-font text-[11px] text-slate-300 overflow-x-auto">curl -X GET "{{ url('/v1/wallet') }}" \
  -H "X-API-KEY: {{ $apiKey }}" \
  -H "X-USER-KEY: &lt;key_dari_tabel_users&gt;"</pre>
            </div>

            <div>
                <span class="text-xs font-semibold text-slate-300 mb-1 block">Contoh Respon (200 OK):</span>
                <pre class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs code-font text-amber-300 overflow-x-auto">{
  "status": "success",
  "code": 200,
  "message": "Data wallet penjual berhasil diambil.",
  "data": {
    "wallet": {
      "uuid": "4c9d784a-bf60-4927-b6db-5374e50eb1c8",
      "currency": "IDR",
      "balance": 250000.00,
      "pending_balance": 0.00,
      "bank_account": {
        "bank_name": "BCA",
        "account_number": "8830192831",
        "account_holder": "Ahmad Dani"
      },
      "mutations": [
        {
          "type": "credit",
          "amount": 125000,
          "balance_before": 125000,
          "balance_after": 250000,
          "description": "Pembayaran lunas invoice INV-20261118-8F2A1B",
          "timestamp": "2026-11-18T10:15:30.000000Z"
        }
      ]
    },
    "recent_withdrawals": []
  }
}</pre>
            </div>
        </div>

        <!-- 5.2 POST /v1/wallet/bank-account -->
        <div class="space-y-3 pt-4 border-t border-slate-800">
            <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                <h3 class="text-sm font-bold text-white flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-mono font-bold text-xs">POST</span>
                    <span class="code-font text-amber-300">/v1/wallet/bank-account</span>
                    <span class="text-xs font-normal text-slate-400">&mdash; Update Rekening Bank Tujuan Pencairan</span>
                </h3>
                <span class="text-xs text-slate-400">Respon: 200 OK</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <span class="text-xs font-semibold text-slate-300 mb-1 block">Request Body (JSON):</span>
                    <pre class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs code-font text-slate-200 overflow-x-auto">{
  "bank_name": "BCA",
  "account_number": "8830192831",
  "account_holder": "Ahmad Dani",
  "key": "&lt;key_dari_tabel_users&gt;"
}</pre>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-300 mb-1 block">Contoh Respon (200 OK):</span>
                    <pre class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs code-font text-emerald-300 overflow-x-auto">{
  "status": "success",
  "code": 200,
  "message": "Rekening bank penarikan saldo berhasil disimpan.",
  "data": {
    "bank_account": {
      "bank_name": "BCA",
      "account_number": "8830192831",
      "account_holder": "Ahmad Dani"
    }
  }
}</pre>
                </div>
            </div>
        </div>

        <!-- 5.3 POST /v1/wallet/withdraw -->
        <div class="space-y-3 pt-4 border-t border-slate-800">
            <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                <h3 class="text-sm font-bold text-white flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-mono font-bold text-xs">POST</span>
                    <span class="code-font text-amber-300">/v1/wallet/withdraw</span>
                    <span class="text-xs font-normal text-slate-400">&mdash; Tarik Saldo Penjual</span>
                </h3>
                <span class="text-xs text-slate-400">Respon: 201 Created</span>
            </div>

            <p class="text-xs text-slate-300 leading-relaxed">
                Penarikan saldo minimal <strong class="text-white">Rp 50.000</strong>. Saldo wallet penjual langsung didebit secara real-time dan histori penarikan dicatat di tabel <code class="code-font text-white">tbl_withdrawals</code> dengan status <code class="code-font text-amber-300">pending</code> untuk verifikasi payout.
            </p>

            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                <span class="text-xs font-bold text-amber-400 uppercase tracking-wider">Contoh Request cURL:</span>
                <pre class="p-2.5 rounded bg-slate-900 code-font text-[11px] text-slate-300 overflow-x-auto">curl -X POST "{{ url('/v1/wallet/withdraw') }}" \
  -H "X-API-KEY: {{ $apiKey }}" \
  -H "X-USER-KEY: &lt;key_dari_tabel_users&gt;" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 100000,
    "notes": "Penarikan ke rekening BCA"
  }'</pre>
            </div>
        </div>
    </div>

    <!-- Section 6: REST API Transaksi, Checkout & QRIS Midtrans (/v1/transactions) -->
    <div id="transactions-api" class="glass-card rounded-2xl p-6 sm:p-8 shadow-xl space-y-6 border border-sky-500/20">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-800 pb-4">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-sky-400"></span>
                    <span>6. REST API Transaksi, Checkout & QRIS Midtrans (/v1/transactions)</span>
                </h2>
                <p class="text-xs text-slate-400 mt-1">
                    Alur Otomatis: <strong class="text-white">Checkout</strong> &rarr; <strong class="text-sky-300">Bayar QRIS Midtrans</strong> &rarr; <strong class="text-emerald-300">Stok Berkurang (-1)</strong> &rarr; <strong class="text-amber-300">Saldo Penjual Bertambah (+Balance)</strong> &rarr; <strong class="text-cyan-300">Kirim Email Berisi File Produk</strong>.
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-sky-500/10 text-sky-300 border border-sky-500/30">
                    Midtrans QRIS
                </span>
                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-purple-500/10 text-purple-300 border border-purple-500/30">
                    N+1 Concurrency Lock
                </span>
                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-500/10 text-emerald-300 border border-emerald-500/30">
                    Wasmer Compatible
                </span>
            </div>
        </div>

        <div class="p-3.5 bg-sky-950/40 border border-sky-500/20 rounded-xl text-xs text-sky-200 leading-relaxed space-y-1">
            <strong>Antisipasi Klik Checkout Bersamaan (N+1 Concurrency Guard):</strong>
            <ul class="list-disc list-inside space-y-0.5 text-slate-300 mt-1 pl-1 text-[11px]">
                <li><strong>Nomor Invoice N+1 Berurutan:</strong> Dihasilkan secara atomik: <code class="code-font text-sky-300">INV-YYYYMMDD-0001-...</code>, <code class="code-font text-sky-300">0002</code>, dst.</li>
                <li><strong>Pessimistic Row Lock (<code class="code-font text-amber-300">lockForUpdate</code>):</strong> Mengunci baris produk saat checkout untuk mencegah stok minus akibat checkout di detik yang sama.</li>
                <li><strong>Atomic Cache Lock (Anti Double-Click):</strong> Mencegah klik berulang dari pembeli yang sama dengan respon <code class="code-font text-rose-300">429 Too Many Requests</code>.</li>
                <li><strong>Pencegahan N+1 Query:</strong> Eager loading otomatis (<code class="code-font text-emerald-300">with(['items.product', 'latestPayment'])</code>) & proteksi <code class="code-font text-white">preventLazyLoading</code>.</li>
            </ul>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <div class="p-3.5 bg-slate-950 rounded-xl border border-slate-800 space-y-1">
                <div class="font-bold text-sky-400">1. Checkout Pembeli</div>
                <p class="text-slate-400 text-[11px]">Pembeli memasukkan nama, email, dan memilih produk digital yang dibeli.</p>
            </div>
            <div class="p-3.5 bg-slate-950 rounded-xl border border-slate-800 space-y-1">
                <div class="font-bold text-amber-400">2. Generate QRIS</div>
                <p class="text-slate-400 text-[11px]">Sistem menghubungi Midtrans Core API dan membangkitkan QRIS asli / URL gambar.</p>
            </div>
            <div class="p-3.5 bg-slate-950 rounded-xl border border-slate-800 space-y-1">
                <div class="font-bold text-emerald-400">3. Settlement Otomatis</div>
                <p class="text-slate-400 text-[11px]">Saat QRIS dibayar (webhook/simulasi): stok -1 dan saldo wallet penjual bertambah.</p>
            </div>
            <div class="p-3.5 bg-slate-950 rounded-xl border border-slate-800 space-y-1">
                <div class="font-bold text-cyan-400">4. Kirim Email Otomatis</div>
                <p class="text-slate-400 text-[11px]">Invoice & file unduhan digital langsung dikirim ke email pembeli secara instan.</p>
            </div>
        </div>

        <!-- 6.1 POST /v1/transactions/checkout -->
        <div class="space-y-3 pt-2">
            <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                <h3 class="text-sm font-bold text-white flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-mono font-bold text-xs">POST</span>
                    <span class="code-font text-sky-300">/v1/transactions/checkout</span>
                    <span class="text-xs font-normal text-slate-400">&mdash; Checkout & Buat QRIS Midtrans</span>
                </h3>
                <span class="text-xs text-slate-400">Respon: 201 Created</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400">
                            <th class="py-2 px-3">Field</th>
                            <th class="py-2 px-3">Tipe</th>
                            <th class="py-2 px-3">Wajib?</th>
                            <th class="py-2 px-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 code-font">
                        <tr><td class="py-2 px-3 text-white">product_id</td><td class="py-2 px-3 text-indigo-300">integer / string</td><td class="py-2 px-3 text-rose-400">Ya</td><td class="py-2 px-3 font-sans text-slate-300">ID / UUID / Slug produk digital yang dibeli</td></tr>
                        <tr><td class="py-2 px-3 text-white">buyer_name</td><td class="py-2 px-3 text-indigo-300">string</td><td class="py-2 px-3 text-rose-400">Ya</td><td class="py-2 px-3 font-sans text-slate-300">Nama lengkap pembeli</td></tr>
                        <tr><td class="py-2 px-3 text-white">buyer_email</td><td class="py-2 px-3 text-indigo-300">string</td><td class="py-2 px-3 text-rose-400">Ya</td><td class="py-2 px-3 font-sans text-slate-300">Email valid pembeli (tujuan pengiriman produk digital)</td></tr>
                        <tr><td class="py-2 px-3 text-white">buyer_phone</td><td class="py-2 px-3 text-indigo-300">string</td><td class="py-2 px-3 text-slate-400">Opsional</td><td class="py-2 px-3 font-sans text-slate-300">Nomor WhatsApp / HP pembeli</td></tr>
                        <tr><td class="py-2 px-3 text-white">qty</td><td class="py-2 px-3 text-indigo-300">integer</td><td class="py-2 px-3 text-slate-400">Opsional</td><td class="py-2 px-3 font-sans text-slate-300">Jumlah kuantitas (default: 1)</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <span class="text-xs font-semibold text-slate-300 mb-1 block">Contoh Request Body:</span>
                    <pre class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs code-font text-sky-300 overflow-x-auto">{
  "product_id": 1,
  "buyer_name": "Rian Kusuma",
  "buyer_email": "rian.kusuma@gmail.com",
  "buyer_phone": "081298765432"
}</pre>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-300 mb-1 block">Contoh Respon (201 Created):</span>
                    <pre class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs code-font text-emerald-300 overflow-x-auto">{
  "status": "success",
  "code": 201,
  "message": "Invoice QRIS berhasil dibangkitkan.",
  "data": {
    "invoice_number": "INV-20261118-8F2A1B",
    "status": "pending",
    "total_amount": 125000,
    "payment": {
      "payment_gateway": "midtrans",
      "payment_method": "qris",
      "qr_string": "00020101021226670016ID.CO.GOPAY.WWW...",
      "qr_code_url": "https://api.qrserver.com/v1/create-qr-code/?size=350x350&data=...",
      "transaction_status": "pending"
    },
    "product": {
      "name": "E-Book Master REST API Laravel 12",
      "price": 125000
    }
  }
}</pre>
                </div>
            </div>
        </div>

        <!-- 6.2 GET /v1/transactions/{invoice}/status -->
        <div class="space-y-3 pt-4 border-t border-slate-800">
            <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                <h3 class="text-sm font-bold text-white flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 font-mono font-bold text-xs">GET</span>
                    <span class="code-font text-sky-300">/v1/transactions/{invoice}/status</span>
                    <span class="text-xs font-normal text-slate-400">&mdash; Polling Status Invoice & Download File</span>
                </h3>
                <span class="text-xs text-slate-400">Respon: 200 OK</span>
            </div>

            <p class="text-xs text-slate-300 leading-relaxed">
                Dipanggil oleh frontend klien setiap 3-5 detik saat menampilkan QRIS. Jika status berubah menjadi <code class="code-font text-emerald-300">paid</code>, properti <code class="code-font text-cyan-300">delivery.file_url</code> akan muncul berisi URL unduhan produk digital.
            </p>
        </div>

        <!-- 6.3 POST /v1/transactions/{invoice}/simulate-payment -->
        <div class="space-y-3 pt-4 border-t border-slate-800">
            <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                <h3 class="text-sm font-bold text-white flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded bg-purple-500/20 text-purple-400 font-mono font-bold text-xs">POST</span>
                    <span class="code-font text-sky-300">/v1/transactions/{invoice}/simulate-payment</span>
                    <span class="text-xs font-normal text-slate-400">&mdash; Simulasi Bayar Sandbox (Tanpa Scan HP)</span>
                </h3>
                <span class="text-xs text-slate-400">Respon: 200 OK</span>
            </div>

            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                <p class="text-xs text-slate-300">Gunakan endpoint ini untuk menguji secara otomatis alur settlement pembayaran lunas pada Midtrans sandbox:</p>
                <pre class="p-2.5 rounded bg-slate-900 code-font text-[11px] text-purple-300 overflow-x-auto">curl -X POST "{{ url('/v1/transactions/INV-20261118-8F2A1B/simulate-payment') }}" \
  -H "X-API-KEY: {{ $apiKey }}"</pre>
            </div>
        </div>

        <!-- 6.4 POST /v1/payments/midtrans-notification -->
        <div class="space-y-3 pt-4 border-t border-slate-800">
            <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                <h3 class="text-sm font-bold text-white flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-mono font-bold text-xs">POST</span>
                    <span class="code-font text-sky-300">/v1/payments/midtrans-notification</span>
                    <span class="text-xs font-normal text-slate-400">&mdash; Webhook Web Notification dari Server Midtrans</span>
                </h3>
                <span class="text-xs text-slate-400">Respon: 200 OK</span>
            </div>

            <div class="p-3.5 bg-slate-950 rounded-xl border border-slate-800 text-xs text-slate-300 space-y-2">
                <div class="text-sky-300 font-bold">Mekanisme Verifikasi Keamanan Signature Hash:</div>
                <code class="block p-2 bg-slate-900 rounded text-emerald-400 text-[11px]">signature_key = SHA512(order_id + status_code + gross_amount + MIDTRANS_SERVER_KEY)</code>
                <p class="text-slate-400 text-[11px]">Ketika status transaksi bernilai <code class="code-font text-emerald-300">settlement</code> atau <code class="code-font text-emerald-300">capture</code>, sistem otomatis: (1) Mengurangi stok produk sebanyak 1, (2) Mengkreditkan saldo wallet penjual, dan (3) Mengirimkan produk digital via email pembeli.</p>
            </div>
        </div>

        <!-- 6.5 GET /v1/transactions -->
        <div class="space-y-3 pt-4 border-t border-slate-800">
            <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                <h3 class="text-sm font-bold text-white flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 font-mono font-bold text-xs">GET</span>
                    <span class="code-font text-sky-300">/v1/transactions</span>
                    <span class="text-xs font-normal text-slate-400">&mdash; Lihat Transaksi pada Produk Saya (Isolasi Akun & Omzet)</span>
                </h3>
                <span class="text-xs text-slate-400">Respon: 200 OK</span>
            </div>

            <p class="text-xs text-slate-300 leading-relaxed">
                Menampilkan seluruh daftar transaksi pada produk digital yang dibuat oleh penjual. Data terisolasi penuh sehingga 1 penjual tidak dapat melihat transaksi produk milik penjual lain. Wajib menyertakan otentikasi via header <code class="code-font text-amber-300">X-USER-KEY</code> (atau body/query <code class="code-font text-amber-300">key</code>) atau parameter <code class="code-font text-cyan-300">id_users</code>.
            </p>

            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                <span class="text-xs font-bold text-sky-400 uppercase tracking-wider">Contoh Request cURL:</span>
                <pre class="p-2.5 rounded bg-slate-900 code-font text-[11px] text-slate-300 overflow-x-auto">curl -X GET "{{ url('/v1/transactions?status=paid') }}" \
  -H "X-API-KEY: {{ $apiKey }}" \
  -H "X-USER-KEY: &lt;key_dari_tabel_users&gt;"</pre>
            </div>

            <div>
                <span class="text-xs font-semibold text-slate-300 mb-1 block">Contoh Respon (200 OK):</span>
                <pre class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs code-font text-sky-300 overflow-x-auto">{
  "status": "success",
  "code": 200,
  "message": "Daftar transaksi produk milik 'Budi Santoso' berhasil diambil.",
  "data": {
    "seller": {
      "id": 2,
      "id_users": "POS-X8K1N9Q4",
      "nama": "Budi Santoso",
      "username": "budi"
    },
    "summary": {
      "total_transactions": 5,
      "paid_transactions": 4,
      "unpaid_transactions": 1,
      "total_revenue": 600000,
      "formatted_total_revenue": "Rp 600.000"
    },
    "transactions": [
      {
        "id": 12,
        "uuid": "7f8e9d0c-1234-5678-9abc-def012345678",
        "invoice_number": "INV-20261118-8F2K9A",
        "status": "paid",
        "is_paid": true,
        "total_amount": 150000,
        "formatted_amount": "Rp 150.000",
        "buyer": {
          "email": "ahmaddani@example.com",
          "name": "Ahmad Dani",
          "phone": "081234567890"
        },
        "items": [
          {
            "product_id": 1,
            "product_name": "E-Book Panduan Master REST API Laravel 12",
            "qty": 1,
            "unit_price": 150000,
            "subtotal": 150000,
            "download_url": "https://storage.googleapis.com/assets/ebook-master-laravel.pdf"
          }
        ],
        "payment": {
          "gateway": "midtrans",
          "method": "qris",
          "status": "settlement"
        },
        "paid_at": "2026-11-18T13:20:00+07:00",
        "created_at": "2026-11-18T13:15:00+07:00"
      }
    ]
  }
}</pre>
            </div>
        </div>

        <!-- 6.6 GET /v1/transactions/{invoice} -->
        <div class="space-y-3 pt-4 border-t border-slate-800">
            <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                <h3 class="text-sm font-bold text-white flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 font-mono font-bold text-xs">GET</span>
                    <span class="code-font text-sky-300">/v1/transactions/{invoice}</span>
                    <span class="text-xs font-normal text-slate-400">&mdash; Detail Transaksi Produk Penjual</span>
                </h3>
                <span class="text-xs text-slate-400">Respon: 200 OK</span>
            </div>

            <p class="text-xs text-slate-300 leading-relaxed">
                Melihat detail lengkap satu transaksi produk. Jika diakses dengan autentikasi key penjual, sistem memastikan bahwa invoice tersebut benar milik produk penjual yang bersangkutan. Jika mencoba melihat transaksi produk milik penjual lain, sistem merespon dengan status <code class="code-font text-rose-400">403 Forbidden</code>.
            </p>
        </div>
    </div>

    <!-- Section 7: Katalog Semua Respon HTTP Status Code -->
    <div id="status-codes" class="glass-card rounded-2xl p-6 sm:p-8 shadow-xl space-y-6">
        <div>
            <h2 class="text-lg font-bold text-white flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-400"></span>
                <span>7. Katalog Lengkap Respon Status Code (200, 201, 203, 400, 401, ...)</span>
            </h2>
            <p class="text-xs text-slate-400 mt-1">
                Anda dapat menguji respon status kode apa pun secara langsung lewat endpoint simulasi: <code class="code-font text-purple-300">GET /v1/auth/status-demo/{kode}</code>
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <!-- 200 OK -->
            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-emerald-400 code-font">200 OK</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300">Success</span>
                </div>
                <p class="text-slate-400 text-[11px]">Request berhasil diproses secara normal (misal saat login, melihat profil, atau update data).</p>
                <pre class="p-2 rounded bg-slate-900 code-font text-[11px] text-slate-300 overflow-x-auto">{
  "status": "success",
  "code": 200,
  "message": "Login successful.",
  "data": { ... }
}</pre>
            </div>

            <!-- 201 Created -->
            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-emerald-400 code-font">201 Created</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300">Created</span>
                </div>
                <p class="text-slate-400 text-[11px]">Resource baru berhasil dibuat di database (misal saat register akun baru).</p>
                <pre class="p-2 rounded bg-slate-900 code-font text-[11px] text-slate-300 overflow-x-auto">{
  "status": "success",
  "code": 201,
  "message": "User successfully registered.",
  "data": { "user": { ... } }
}</pre>
            </div>

            <!-- 203 Non-Authoritative Information -->
            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-cyan-400 code-font">203 Non-Authoritative Information</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-cyan-500/20 text-cyan-300">Transformed</span>
                </div>
                <p class="text-slate-400 text-[11px]">Permintaan berhasil, namun metadata payload berasal dari proxy/cache termodifikasi.</p>
                <pre class="p-2 rounded bg-slate-900 code-font text-[11px] text-slate-300 overflow-x-auto">{
  "status": "success",
  "code": 203,
  "message": "Metadata berasal dari cache proxy sekunder.",
  "meta": { "source": "cache-proxy" }
}</pre>
            </div>

            <!-- 400 Bad Request -->
            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-amber-400 code-font">400 Bad Request</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300">Client Error</span>
                </div>
                <p class="text-slate-400 text-[11px]">Sintaks request rusak atau parameter identitas wajib tidak disertakan.</p>
                <pre class="p-2 rounded bg-slate-900 code-font text-[11px] text-slate-300 overflow-x-auto">{
  "status": "error",
  "code": 400,
  "message": "Bad Request: Identifier pengguna wajib disertakan.",
  "error": "MISSING_IDENTIFIER"
}</pre>
            </div>

            <!-- 401 Unauthorized -->
            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-rose-400 code-font">401 Unauthorized</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-rose-500/20 text-rose-300">Unauthorized</span>
                </div>
                <p class="text-slate-400 text-[11px]">API Key hilang/salah, atau kombinasi username dan password salah.</p>
                <pre class="p-2 rounded bg-slate-900 code-font text-[11px] text-slate-300 overflow-x-auto">{
  "status": "error",
  "code": 401,
  "message": "Unauthorized: The provided API Key is invalid.",
  "error": "INVALID_API_KEY"
}</pre>
            </div>

            <!-- 403 Forbidden -->
            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-rose-400 code-font">403 Forbidden</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-rose-500/20 text-rose-300">Forbidden</span>
                </div>
                <p class="text-slate-400 text-[11px]">Klien terautentikasi tetapi tidak memiliki hak akses/otoritas (role privilege).</p>
                <pre class="p-2 rounded bg-slate-900 code-font text-[11px] text-slate-300 overflow-x-auto">{
  "status": "error",
  "code": 403,
  "message": "Forbidden: Anda tidak memiliki akses ke fitur ini.",
  "error": "FORBIDDEN"
}</pre>
            </div>

            <!-- 404 Not Found -->
            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-rose-400 code-font">404 Not Found</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-rose-500/20 text-rose-300">Not Found</span>
                </div>
                <p class="text-slate-400 text-[11px]">Data user dengan ID Users tersebut atau endpoint tidak ditemukan.</p>
                <pre class="p-2 rounded bg-slate-900 code-font text-[11px] text-slate-300 overflow-x-auto">{
  "status": "error",
  "code": 404,
  "message": "Not Found: User with specified ID does not exist.",
  "error": "USER_NOT_FOUND"
}</pre>
            </div>

            <!-- 422 Unprocessable Content -->
            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-rose-400 code-font">422 Unprocessable Content</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-rose-500/20 text-rose-300">Validation</span>
                </div>
                <p class="text-slate-400 text-[11px]">Data request gagal lolos validasi (email sudah dipakai, password kurang dari 6 digit, dsb).</p>
                <pre class="p-2 rounded bg-slate-900 code-font text-[11px] text-slate-300 overflow-x-auto">{
  "status": "error",
  "code": 422,
  "message": "Validation error.",
  "errors": {
    "email": ["Email ini sudah terdaftar."],
    "password": ["Password minimal 6 karakter."]
  }
}</pre>
            </div>

            <!-- 500 Internal Server Error -->
            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2 md:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-red-500 code-font">500 Internal Server Error</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-red-500/20 text-red-300">Server Error</span>
                </div>
                <p class="text-slate-400 text-[11px]">Terjadi kendala pada database atau logika internal server.</p>
                <pre class="p-2 rounded bg-slate-900 code-font text-[11px] text-slate-300 overflow-x-auto">{
  "status": "error",
  "code": 500,
  "message": "Internal Server Error: Terjadi kegagalan server.",
  "error": "SERVER_ERROR"
}</pre>
            </div>
        </div>
    </div>
</div>
@endsection
