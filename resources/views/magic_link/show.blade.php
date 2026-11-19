@extends('layouts.app')

@section('title', 'Magic Link Produk Digital - ' . ($seller->nama_toko ?: $seller->nama))

@section('content')
<div class="max-w-2xl mx-auto py-6 sm:py-10 px-4 space-y-6">

    <!-- Store & Brand Identity Header -->
    <div class="text-center space-y-2">
        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-cyan-950/70 border border-cyan-500/30 text-cyan-300 text-xs font-semibold">
            <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
            <span>Magic Link Gateway Produk Digital</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
            {{ $seller->nama_toko ?: $seller->nama }}
        </h1>
        <p class="text-xs text-slate-400">
            Penjual: <span class="text-slate-200 font-semibold">{{ $seller->nama }}</span> &bull; 
            ID Penjual: <span class="code-font text-cyan-300">{{ $seller->id_users }}</span>
            @if (!empty($seller->id_toko))
                &bull; ID Toko: <span class="code-font text-amber-300">{{ $seller->id_toko }}</span>
            @endif
        </p>
    </div>

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-950/70 border border-rose-500/50 text-rose-200 text-sm flex items-start space-x-3">
            <svg class="w-5 h-5 text-rose-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
                <strong class="font-bold">Akses Ditolak:</strong>
                <p class="mt-0.5 text-xs">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Product & Transaction Summary Card -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-800 space-y-6">
        
        <div class="flex flex-col sm:flex-row items-center gap-5 p-4 rounded-2xl bg-slate-900/80 border border-slate-800">
            @if (!empty($productImage))
                <img src="{{ $productImage }}" alt="{{ $productName }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border border-slate-700/80 shadow-md">
            @else
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-tr from-brand-600/30 to-cyan-600/30 border border-brand-500/30 flex items-center justify-center text-brand-300">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            @endif

            <div class="flex-1 text-center sm:text-left space-y-1">
                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-purple-500/10 text-purple-300 border border-purple-500/30">
                    Produk Digital
                </span>
                <h2 class="text-lg sm:text-xl font-bold text-white">{{ $productName }}</h2>
                <div class="text-xs text-slate-400 space-y-0.5">
                    <p>No. Invoice: <span class="code-font text-cyan-400 font-semibold select-all">{{ $transaction->invoice_number }}</span></p>
                    <p>Total Transaksi: <strong class="text-emerald-400 font-bold">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</strong></p>
                </div>
            </div>
        </div>

        @if (!$isPaid)
            <!-- State 1: Belum Lunas -->
            <div class="p-5 rounded-2xl bg-amber-950/40 border border-amber-500/40 text-center space-y-3">
                <div class="w-12 h-12 mx-auto rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">Menunggu Pembayaran</h3>
                <p class="text-xs text-slate-300 max-w-md mx-auto">
                    Transaksi ini belum lunas. Selesaikan pembayaran QRIS terlebih dahulu untuk mengaktifkan Magic Link unduhan file produk ini.
                </p>
                <div class="pt-2">
                    <a href="{{ route('checkout.invoice', ['invoice' => $transaction->invoice_number]) }}" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition shadow-lg shadow-amber-500/20">
                        <span>Lanjutkan Pembayaran QRIS</span>
                        <svg class="w-4 h-4 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </div>

        @elseif ($isVerified)
            <!-- State 2: Sudah Terverifikasi Google -->
            <div class="p-6 rounded-2xl bg-emerald-950/40 border border-emerald-500/40 text-center space-y-4">
                <div class="w-14 h-14 mx-auto rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center border border-emerald-500/30">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        ✓ Akun Google Terverifikasi
                    </span>
                    <h3 class="text-lg font-bold text-white mt-2">Akses Produk Digital Terbuka!</h3>
                    <p class="text-xs text-slate-300 mt-1">
                        Terverifikasi atas email: <strong class="text-emerald-300">{{ session('magic_auth_email_' . $transaction->id, $transaction->buyer_email) }}</strong>
                    </p>
                </div>

                <div class="pt-3 space-y-3">
                    <a href="{{ route('magic.link.download', [$seller->id_users, $transaction->magic_token]) }}" class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 to-cyan-600 hover:from-emerald-500 hover:to-cyan-500 text-white font-black text-sm sm:text-base shadow-xl shadow-emerald-500/30 transition flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        <span>Menuju Tautan Produk Digital &rarr;</span>
                    </a>
                    <p class="text-xs text-emerald-300 font-medium">
                        Mengalihkan otomatis ke link produk dalam <span id="countdownSec" class="font-bold underline text-white">3</span> detik...
                    </p>
                    <p class="text-[11px] text-slate-400">
                        Sistem mengalihkan (redirect) Anda langsung ke tautan produk digital asli (Google Drive, Cloud Storage, Notion, atau link akses penjual).
                    </p>
                </div>
            </div>

            <script>
                // Auto redirect ke tautan produk digital setelah verifikasi
                let secondsLeft = 3;
                const countEl = document.getElementById('countdownSec');
                const redirectTargetUrl = "{{ route('magic.link.download', [$seller->id_users, $transaction->magic_token]) }}";
                const redirectTimer = setInterval(function() {
                    secondsLeft--;
                    if (countEl) countEl.textContent = secondsLeft;
                    if (secondsLeft <= 0) {
                        clearInterval(redirectTimer);
                        window.location.href = redirectTargetUrl;
                    }
                }, 1000);
            </script>

        @else
            <!-- State 3: Pembayaran LUNAS, Menunggu Verifikasi Google Pembeli -->
            <div class="space-y-5">
                <div class="p-5 rounded-2xl bg-slate-900/90 border border-slate-700/80 space-y-3">
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-cyan-500/20 text-cyan-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div class="text-xs text-slate-300 leading-relaxed">
                            <h4 class="font-bold text-white text-sm">Verifikasi Identitas Google (Magic Link)</h4>
                            <p class="mt-1">
                                Untuk alasan keamanan dan memastikan hanya pembeli yang berhak yang menerima file, silakan masuk (Sign-In) menggunakan akun Google dengan email yang sama seperti saat checkout:
                            </p>
                            <div class="mt-2 inline-block px-3 py-1.5 rounded-xl bg-slate-950 border border-amber-500/40 text-amber-300 font-mono text-xs font-bold">
                                Email Terdaftar: {{ $maskedEmail }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert Feedback Box -->
                <div id="authAlert" class="hidden p-4 rounded-2xl text-xs flex items-start space-x-3 transition-all"></div>

                <!-- Google Sign In Button Container -->
                <div class="space-y-3">
                    <button 
                        type="button" 
                        id="googleLoginBtn" 
                        onclick="startGoogleLogin()" 
                        class="w-full py-3.5 px-5 rounded-2xl bg-white hover:bg-slate-100 text-slate-900 font-bold text-sm shadow-xl shadow-white/10 transition flex items-center justify-center space-x-3 cursor-pointer group"
                    >
                        <!-- Official Google "G" SVG Icon -->
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" />
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" />
                        </svg>
                        <span id="googleLoginText">Masuk dengan Akun Google</span>
                    </button>

                    <!-- Development / Manual Email Test Helper (If Firebase is waiting for user config) -->
                    <div id="devSimulationBox" class="mt-3 p-3 rounded-xl bg-slate-900/60 border border-slate-800 text-[11px] text-slate-400 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-slate-300">Pengujian Simulasi Cepat (Testing Mode):</span>
                            <span class="text-[9px] uppercase px-1.5 py-0.5 rounded bg-brand-500/20 text-brand-300 code-font">Dev Test</span>
                        </div>
                        <div class="flex gap-2">
                            <input 
                                type="email" 
                                id="testEmailInput" 
                                placeholder="Masukkan email untuk verifikasi..." 
                                value="{{ $transaction->buyer_email }}"
                                class="flex-1 px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-700 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                            >
                            <button 
                                type="button" 
                                onclick="verifyEmailDirectly(document.getElementById('testEmailInput').value)" 
                                class="px-3 py-1.5 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs transition"
                            >
                                Tes Verifikasi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Card Footer -->
        <div class="pt-4 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <span class="code-font">POS Market &bull; Firebase Google Auth Protection</span>
            <a href="{{ route('products.index') }}" class="text-brand-400 hover:text-brand-300 hover:underline">
                &larr; Lihat Produk Lainnya
            </a>
        </div>
    </div>
</div>

<!-- Firebase App & Firebase Auth Compat SDKs -->
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-auth-compat.js"></script>

<script>
    const firebaseConfig = @json($firebaseConfig);
    const verifyUrl = "{{ route('magic.link.verify', [$seller->id_users, $transaction->magic_token]) }}";
    const csrfToken = "{{ csrf_token() }}";

    let firebaseInitialized = false;

    // Inisialisasi Firebase jika config telah diisi
    if (firebaseConfig && firebaseConfig.apiKey && firebaseConfig.apiKey.trim() !== '') {
        try {
            if (!firebase.apps.length) {
                firebase.initializeApp(firebaseConfig);
            }
            firebaseInitialized = true;
        } catch (e) {
            console.warn("Inisialisasi Firebase gagal: ", e.message);
        }
    }

    function showAlert(type, title, message) {
        const box = document.getElementById('authAlert');
        if (!box) return;

        box.className = type === 'success' 
            ? 'p-4 rounded-2xl bg-emerald-950/70 border border-emerald-500/50 text-emerald-200 text-xs flex items-start space-x-3 transition-all'
            : 'p-4 rounded-2xl bg-rose-950/70 border border-rose-500/50 text-rose-200 text-xs flex items-start space-x-3 transition-all';

        const iconSvg = type === 'success'
            ? `<svg class="w-5 h-5 text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>`
            : `<svg class="w-5 h-5 text-rose-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`;

        box.innerHTML = `${iconSvg}<div><strong class="font-bold block">${title}</strong><span class="mt-0.5 leading-relaxed">${message}</span></div>`;
        box.classList.remove('hidden');
    }

    function setBtnLoading(isLoading, text = 'Memverifikasi Akun Google...') {
        const btn = document.getElementById('googleLoginBtn');
        const span = document.getElementById('googleLoginText');
        if (!btn || !span) return;

        btn.disabled = isLoading;
        if (isLoading) {
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            span.innerText = text;
        } else {
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
            span.innerText = 'Masuk dengan Akun Google';
        }
    }

    function startGoogleLogin() {
        if (!firebaseInitialized) {
            showAlert('error', 'Konfigurasi Firebase Belum Diisi', 
                'Firebase API Key belum diisi di berkas .env server. Anda dapat mengisi konfigurasi Firebase Anda di .env atau menggunakan form uji cepat di bawah.');
            return;
        }

        setBtnLoading(true, 'Membuka Login Google...');
        const provider = new firebase.auth.GoogleAuthProvider();
        provider.setCustomParameters({ prompt: 'select_account' });

        firebase.auth().signInWithPopup(provider)
            .then((result) => {
                const user = result.user;
                setBtnLoading(true, 'Memverifikasi email ke server POS Market...');
                return user.getIdToken().then(idToken => {
                    return verifyEmailWithBackend(user.email, user.uid, idToken);
                });
            })
            .catch((error) => {
                setBtnLoading(false);
                console.error("Firebase Login Error: ", error);
                if (error.code === 'auth/popup-closed-by-user') {
                    showAlert('error', 'Login Dibatalkan', 'Jendela popup Google ditutup sebelum proses login selesai.');
                } else if (error.code === 'auth/unauthorized-domain') {
                    showAlert('error', 'Domain Belum Diizinkan di Firebase', 'Silakan tambahkan domain (contoh: localhost atau 127.0.0.1) ke menu Authentication -> Settings -> Authorized Domains di Firebase Console.');
                } else {
                    showAlert('error', 'Gagal Login Google', error.message || 'Terjadi kesalahan saat otentikasi Google.');
                }
            });
    }

    function verifyEmailDirectly(email) {
        if (!email) {
            alert('Silakan masukkan email.');
            return;
        }
        setBtnLoading(true, 'Memverifikasi...');
        verifyEmailWithBackend(email, 'test_google_sub_123', null);
    }

    function verifyEmailWithBackend(email, uid, idToken) {
        fetch(verifyUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                email: email,
                google_uid: uid,
                id_token: idToken
            })
        })
        .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, body: data })))
        .then(result => {
            setBtnLoading(false);
            if (result.ok && result.body.success) {
                showAlert('success', 'Verifikasi Berhasil!', 'Mengarahkan langsung ke tautan produk...');
                setTimeout(() => {
                    const targetLink = result.body.redirect_url || result.body.download_url;
                    if (targetLink) {
                        window.location.href = targetLink;
                    } else {
                        window.location.reload();
                    }
                }, 1000);
            } else {
                showAlert('error', 'Akses Ditolak', result.body.message || 'Email tidak cocok dengan pembeli saat checkout.');
            }
        })
        .catch(err => {
            setBtnLoading(false);
            console.error(err);
            showAlert('error', 'Koneksi Gagal', 'Gagal memverifikasi ke server: ' + err.message);
        });
    }
</script>
@endsection
