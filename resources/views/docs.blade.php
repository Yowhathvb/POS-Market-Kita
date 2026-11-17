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

    <!-- Section 5: Cara Upload Data ke Kolom 'value' (JSON) -->
    <div id="upload-value" class="glass-card rounded-2xl p-6 sm:p-8 shadow-xl space-y-4">
        <h2 class="text-lg font-bold text-white flex items-center space-x-2">
            <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
            <span>3. Panduan Upload & Update Data ke Kolom 'value' (JSON)</span>
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

    <!-- Section 6: Katalog Semua Respon HTTP Status Code -->
    <div id="status-codes" class="glass-card rounded-2xl p-6 sm:p-8 shadow-xl space-y-6">
        <div>
            <h2 class="text-lg font-bold text-white flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-400"></span>
                <span>4. Katalog Lengkap Respon Status Code (200, 201, 203, 400, 401, ...)</span>
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
