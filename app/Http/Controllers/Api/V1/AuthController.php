<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * API Register: POST /v1/auth/register
     * Expected Response: 201 Created or 422 Unprocessable Content
     */
    public function register(Request $request): JsonResponse
    {
        // Accept value as either string or array
        $rawVal = $request->input('value');
        if (is_string($rawVal)) {
            $decoded = json_decode($rawVal, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge(['value' => $decoded]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'code' => 422,
                    'message' => 'The value field must be a valid JSON string or JSON object.',
                    'errors' => [
                        'value' => ['The value format is invalid JSON.']
                    ]
                ], 422);
            }
        }

        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'id_users' => ['nullable', 'string', 'max:50', 'unique:users,id_users'],
            'role' => ['nullable', 'string', Rule::in(['user', 'admin', 'manager', 'cashier'])],
            'value' => ['nullable', 'array'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan oleh pengguna lain.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'id_users.unique' => 'ID Users sudah digunakan.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        $idUsers = $request->filled('id_users')
            ? trim($request->input('id_users'))
            : AuthHelper::generateUserId();

        $user = User::create([
            'id_users' => $idUsers,
            'nama' => $request->input('nama'),
            'username' => strtolower(trim($request->input('username'))),
            'email' => strtolower(trim($request->input('email'))),
            'password' => $request->input('password'), // Automatically hashed by cast
            'role' => $request->input('role', 'user'),
            'key' => null, // Generated only upon login
            'value' => $request->input('value'),
        ]);

        return response()->json([
            'status' => 'success',
            'code' => 201,
            'message' => 'User successfully registered.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'id_users' => $user->id_users,
                    'nama' => $user->nama,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'key' => $user->key, // null at registration
                    'value' => $user->value,
                    'created_at' => $user->created_at?->toIso8601String(),
                    'updated_at' => $user->updated_at?->toIso8601String(),
                ]
            ]
        ], 201);
    }

    /**
     * API Login: POST /v1/auth/login
     * Generates: 12 random alphanumeric + username -> hashed -> saved in users.key
     * Expected Response: 200 OK or 401 Unauthorized or 422 Unprocessable Content
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'login' => ['nullable', 'string'],
            'username' => ['nullable', 'string'],
            'email' => ['nullable', 'string'],
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Password wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        $loginIdentifier = $request->input('login')
            ?? $request->input('username')
            ?? $request->input('email');

        if (empty($loginIdentifier)) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Please provide a username, email, or login parameter.',
                'errors' => [
                    'login' => ['Field username atau email harus diisi.']
                ]
            ], 422);
        }

        // Search user by username or email
        $user = User::where('username', strtolower(trim($loginIdentifier)))
            ->orWhere('email', strtolower(trim($loginIdentifier)))
            ->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'status' => 'error',
                'code' => 401,
                'message' => 'Unauthorized: Invalid username/email or password.',
                'error' => 'INVALID_CREDENTIALS'
            ], 401);
        }

        // Generate the required login key: 12 alphanumeric characters + username -> hashed
        $keyData = AuthHelper::generateLoginKey($user->username);

        // Update the key column in the users table
        $user->key = $keyData['hashed_key'];
        $user->save();

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Login successful.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'id_users' => $user->id_users,
                    'nama' => $user->nama,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'key' => $user->key,
                    'value' => $user->value,
                    'updated_at' => $user->updated_at?->toIso8601String(),
                ],
                'login_key_details' => [
                    'random_12' => $keyData['random_12'],
                    'username' => $keyData['username'],
                    'raw_key_combined' => $keyData['raw_key'],
                    'hashed_key_stored' => $keyData['hashed_key'],
                    'explanation' => 'Tepat 12 karakter acak (huruf + angka) digabung dengan username lalu di-hash dan disimpan pada kolom key di tabel users.',
                ]
            ]
        ], 200);
    }

    /**
     * API Get User Profile / Current User: GET /v1/auth/me
     * Can query by header X-USER-ID or query parameter `id_users` or `username`
     */
    public function me(Request $request): JsonResponse
    {
        $idUsers = $request->header('X-USER-ID')
            ?? $request->query('id_users')
            ?? $request->query('username');

        if (!$idUsers) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => 'Bad Request: Please provide an identifier via X-USER-ID header or ?id_users= parameter.',
                'error' => 'MISSING_IDENTIFIER'
            ], 400);
        }

        $user = User::where('id_users', $idUsers)
            ->orWhere('username', $idUsers)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'Not Found: User with the specified identifier does not exist.',
                'error' => 'USER_NOT_FOUND'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'User profile retrieved successfully.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'id_users' => $user->id_users,
                    'nama' => $user->nama,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'key' => $user->key,
                    'value' => $user->value,
                    'created_at' => $user->created_at?->toIso8601String(),
                    'updated_at' => $user->updated_at?->toIso8601String(),
                ]
            ]
        ], 200);
    }

    /**
     * API Upload / Update User JSON Value: POST /v1/auth/update-value
     * Allows uploading or patching custom data into the `value` JSON column.
     */
    public function updateValue(Request $request): JsonResponse
    {
        $identifier = $request->header('X-USER-ID')
            ?? $request->input('id_users')
            ?? $request->input('username');

        if (!$identifier) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => 'Bad Request: User identifier (id_users or username) is required.',
                'error' => 'MISSING_USER_IDENTIFIER'
            ], 400);
        }

        $user = User::where('id_users', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'Not Found: User not found.',
                'error' => 'USER_NOT_FOUND'
            ], 404);
        }

        $valueInput = $request->input('value');
        if (is_string($valueInput)) {
            $decoded = json_decode($valueInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'status' => 'error',
                    'code' => 422,
                    'message' => 'The value provided is not valid JSON.',
                    'errors' => ['value' => ['JSON syntax error: ' . json_last_error_msg()]]
                ], 422);
            }
            $valueInput = $decoded;
        }

        if (!is_array($valueInput)) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'The value parameter must be a JSON object/array.',
                'error' => 'INVALID_VALUE_TYPE'
            ], 422);
        }

        $mode = $request->input('mode', 'replace'); // 'merge' or 'replace'
        if ($mode === 'merge' && is_array($user->value)) {
            $user->value = array_replace_recursive($user->value, $valueInput);
        } else {
            $user->value = $valueInput;
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'User JSON value updated successfully.',
            'data' => [
                'id_users' => $user->id_users,
                'username' => $user->username,
                'mode' => $mode,
                'value' => $user->value
            ]
        ], 200);
    }

    /**
     * API Logout: POST /v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $identifier = $request->header('X-USER-ID')
            ?? $request->input('id_users')
            ?? $request->input('username');

        if ($identifier) {
            $user = User::where('id_users', $identifier)
                ->orWhere('username', $identifier)
                ->first();

            if ($user && $request->boolean('clear_key', false)) {
                $user->key = null;
                $user->save();
            }
        }

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'User successfully logged out.',
        ], 200);
    }

    /**
     * Status Demo Endpoint: GET /v1/auth/status-demo/{code}
     * Demonstrates any requested HTTP status code (200, 201, 203, 400, 401, 403, 404, 422, 500)
     */
    public function statusDemo(int $code): JsonResponse
    {
        return match ($code) {
            200 => response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => '200 OK: Request berhasil diproses secara normal.',
                'data' => ['sample' => 'data']
            ], 200),

            201 => response()->json([
                'status' => 'success',
                'code' => 201,
                'message' => '201 Created: Data resource baru berhasil dibuat di database (misal: Register).',
                'data' => ['created_id' => 1]
            ], 201),

            203 => response()->json([
                'status' => 'success',
                'code' => 203,
                'message' => '203 Non-Authoritative Information: Permintaan berhasil, namun metadata/informasi berasal dari sumber sekunder/proxy/cache termodifikasi.',
                'meta' => [
                    'source' => 'cache-proxy',
                    'timestamp' => now()->toIso8601String()
                ]
            ], 203),

            400 => response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => '400 Bad Request: Permintaan tidak dapat diproses karena sintaks request salah atau format JSON rusak.',
                'error' => 'BAD_REQUEST'
            ], 400),

            401 => response()->json([
                'status' => 'error',
                'code' => 401,
                'message' => '401 Unauthorized: Kredensial tidak valid atau API Key salah / tidak disertakan.',
                'error' => 'UNAUTHORIZED'
            ], 401),

            403 => response()->json([
                'status' => 'error',
                'code' => 403,
                'message' => '403 Forbidden: Akses ditolak. Akun tidak memiliki hak akses/otoritas yang cukup.',
                'error' => 'FORBIDDEN'
            ], 403),

            404 => response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => '404 Not Found: Endpoint atau pengguna dengan ID tersebut tidak ditemukan.',
                'error' => 'NOT_FOUND'
            ], 404),

            422 => response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => '422 Unprocessable Content: Data yang dikirim gagal lolos validasi (misal: email duplikat, password kurang dari 6 digit).',
                'errors' => [
                    'email' => ['The email has already been taken.'],
                    'password' => ['The password must be at least 6 characters.']
                ]
            ], 422),

            500 => response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => '500 Internal Server Error: Terjadi kegagalan sistem internal pada server.',
                'error' => 'SERVER_ERROR'
            ], 500),

            default => response()->json([
                'status' => 'info',
                'code' => $code,
                'message' => "HTTP status code: $code"
            ], $code)
        };
    }
}
