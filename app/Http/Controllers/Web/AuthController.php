<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show Web Login Form
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle Web Login
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Username atau Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginIdentifier = strtolower(trim($credentials['login']));

        // Search user by username or email
        $user = User::where('username', $loginIdentifier)
            ->orWhere('email', $loginIdentifier)
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'login' => 'Username/Email atau password yang Anda masukkan salah.',
            ])->onlyInput('login');
        }

        // Generate the 12 alphanumeric random characters + username -> hashed
        $keyData = AuthHelper::generateLoginKey($user->username);

        // Save hashed key into users.key
        $user->key = $keyData['hashed_key'];
        $user->save();

        // Log the user into session
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Flash session data to show key creation details on dashboard
        return redirect()->intended(route('dashboard'))->with('status', [
            'type' => 'success',
            'title' => 'Login Berhasil!',
            'message' => 'Security key acak 12 digit + username berhasil dibuat dan disimpan di database.',
            'key_info' => $keyData,
        ]);
    }

    /**
     * Show Web Register Form
     */
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    /**
     * Handle Web Register
     */
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'value_json' => ['nullable', 'string'],
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah terdaftar, silakan pilih yang lain.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // Construct JSON value from custom input or JSON textarea
        $jsonValue = [];
        if ($request->filled('value_json')) {
            $decoded = json_decode($request->input('value_json'), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $jsonValue = $decoded;
            } else {
                return back()->withErrors([
                    'value_json' => 'Format JSON kustom tidak valid: ' . json_last_error_msg()
                ])->withInput();
            }
        }

        if ($request->filled('phone')) {
            $jsonValue['phone'] = $request->input('phone');
        }
        if ($request->filled('address')) {
            $jsonValue['address'] = $request->input('address');
        }

        // Generate automatic ID Users with format POS-XXXXXXXX (>5 chars)
        $idUsers = AuthHelper::generateUserId();

        User::create([
            'id_users' => $idUsers,
            'nama' => $request->input('nama'),
            'username' => strtolower(trim($request->input('username'))),
            'email' => strtolower(trim($request->input('email'))),
            'password' => $request->input('password'),
            'role' => 'user', // Default role is always user
            'key' => null, // Generated only upon login
            'value' => empty($jsonValue) ? null : $jsonValue,
        ]);

        return redirect()->route('login')->with('status', [
            'type' => 'success',
            'title' => 'Pendaftaran Berhasil!',
            'message' => 'Akun Anda berhasil dibuat dengan role "user". Silakan login untuk membuat security key Anda.',
        ]);
    }

    /**
     * Show Dashboard for Authenticated User
     */
    public function dashboard(): View
    {
        $user = Auth::user();
        $allUsers = User::latest()->take(10)->get();

        return view('dashboard', [
            'user' => $user,
            'allUsers' => $allUsers,
            'apiKey' => env('API_KEY', 'pos_secret_token_1234567890'),
        ]);
    }

    /**
     * Show API & System Documentation Page: /docs
     */
    public function docs(): View
    {
        $user = Auth::user();

        return view('docs', [
            'user' => $user,
            'apiKey' => env('API_KEY', 'pos_secret_token_1234567890'),
        ]);
    }

    /**
     * Handle Web Logout
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', [
            'type' => 'info',
            'title' => 'Logout Berhasil',
            'message' => 'Sesi Anda telah diakhiri dengan aman.',
        ]);
    }
}
