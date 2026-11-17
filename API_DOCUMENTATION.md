# Dokumentasi Lengkap API & Sistem Autentikasi POS Market

Dokumentasi ini menjelaskan secara komprehensif implementasi halaman Login/Register, tabel database `users`, mekanisme keamanan API Key, pembentukan kolom `key` saat login, pengelolaan data JSON pada kolom `value`, serta seluruh katalog status kode respon HTTP.

---

## 1. Spesifikasi Tabel `users`

Tabel `users` dirancang sesuai kebutuhan sistem dengan skema kolom berikut:

| Nama Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | `BIGINT` (Auto Increment) | Primary Key unik tiap baris data. |
| `id_users` | `VARCHAR(50)` (Unique) | ID khusus pengguna (otomatis digenerate sistem: `POS-` + huruf & angka acak >5 karakter, misal `POS-K8A9B2C4`). |
| `nama` | `VARCHAR(255)` | Nama lengkap pengguna. |
| `username` | `VARCHAR(50)` (Unique) | Username unik untuk login. |
| `email` | `VARCHAR(255)` (Unique) | Alamat email resmi pengguna. |
| `password` | `VARCHAR(255)` | Password pengguna yang telah di-hash dengan aman (Bcrypt). |
| `role` | `VARCHAR(50)` (Default: `'user'`) | Hak akses pengguna: `user`, `admin`, `manager`, `cashier`. |
| `key` | `TEXT` (Nullable) | **Dibuat hanya ketika user login**. Berisi hash dari 12 karakter acak (huruf + angka) digabung dengan username. |
| `value` | `JSON` / `TEXT` (Nullable) | Tempat penyimpanan data dinamis berformat JSON (profil tambahan, alamat, preferensi, dsb). |
| `created_at` | `DATETIME` | Waktu data dibuat. |
| `updated_at` | `DATETIME` | Waktu data diperbarui. |

---

## 2. Keamanan API Key (`.env`)

Untuk mengakses seluruh endpoint API pada grup `/v1/auth`, klien **wajib menyertakan API Key**.

### Konfigurasi di `.env`:
```env
API_KEY=pos_secret_token_1234567890
```

### Cara Menyertakan API Key pada Request:
Klien dapat memilih salah satu cara berikut:
1. **Header HTTP (Direkomendasikan):**
   ```http
   X-API-KEY: pos_secret_token_1234567890
   ```
2. **Authorization Bearer Header:**
   ```http
   Authorization: Bearer pos_secret_token_1234567890
   ```
3. **Query Parameter:**
   ```http
   GET /v1/auth/me?api_key=pos_secret_token_1234567890
   ```

*Jika API Key tidak dikirim atau salah, server akan mengembalikan respon `401 Unauthorized`.*

---

## 3. Logika Khusus Kolom `key` (Dibuat Saat Login)

Sesuai aturan spesifikasi:
1. **Saat Registrasi:** Nilai kolom `key` di tabel `users` bernilai `null`.
2. **Saat Login Berhasil:**
   - Sistem membangkitkan tepat **12 karakter acak** yang terdiri dari kombinasi huruf (besar/kecil) dan angka acak.
   - String acak ini digabungkan langsung dengan `username` user.
   - Contoh mentah:
     - 12 Karakter Acak: `k8L9m2P0qR1x`
     - Username: `budisantoso`
     - Kombinasi: `k8L9m2P0qR1xbudisantoso`
   - Kombinasi tersebut kemudian di-hash menggunakan algoritma Bcrypt (`Hash::make(...)`).
   - Nilai hash disimpan ke kolom `key` pada record user di tabel `users`.
   - Informasi detail hasil generate dikembalikan pada respon login untuk transparansi pengujian.

---

## 4. Cara Mengisi & Mengunggah Data ke Kolom `value` (JSON)

Kolom `value` bertipe data JSON di database. Anda dapat mengunggah data ke kolom ini lewat **Endpoint Register** maupun **Endpoint Update Value**.

### Bentuk Data yang Didukung:
Anda dapat menyimpan data JSON apa pun, seperti preferensi, alamat lengkap, riwayat telepon, atau metadata tambahan:
```json
{
  "telepon": "081234567890",
  "alamat": "Jl. Sudirman No. 45, Jakarta",
  "preferences": {
    "theme": "dark",
    "bahasa": "id",
    "notifikasi_email": true
  },
  "membership": "Gold",
  "poin_reward": 1500
}
```

### Format Pengiriman Data:
1. **Via `application/json` (Body Raw JSON):**
   Kirim langsung sebagai sub-object `value`:
   ```json
   {
     "id_users": "USR-BUDI001",
     "value": {
       "telepon": "081234567890",
       "alamat": "Jl. Gatot Subroto No. 10"
     }
   }
   ```
2. **Via `multipart/form-data` atau `application/x-www-form-urlencoded`:**
   Kirimkan string JSON pada field `value`:
   ```
   id_users: USR-BUDI001
   value: {"telepon":"081234567890","alamat":"Jl. Gatot Subroto No. 10"}
   ```
3. **Pilihan Mode Update (`mode`):**
   - `replace` (default): Menimpa seluruh isi kolom `value` dengan yang baru.
   - `merge`: Menggabungkan (*merge*) data baru ke dalam data JSON yang lama tanpa menghapus field lama yang tidak disebutkan.

---

## 5. Dokumentasi Endpoint API

Base URL lokal: `http://localhost:8000`  
Endpoint dapat diakses baik melalui `/v1/auth/...` maupun `/api/v1/auth/...`.

### 5.1. Register Pengguna Baru

- **Method:** `POST`
- **URL:** `http://localhost:8000/v1/auth/register`
- **Headers:**
  - `X-API-KEY: pos_secret_token_1234567890`
  - `Content-Type: application/json`

#### Form / Parameter yang Diisi:
| Field | Tipe | Wajib? | Keterangan |
| :--- | :--- | :--- | :--- |
| `nama` | String | Ya | Nama lengkap pengguna (maks. 255 karakter). |
| `username` | String | Ya | Username unik (huruf, angka, dash, min 3 karakter). |
| `email` | String | Ya | Alamat email unik dan valid. |
| `password` | String | Ya | Password (minimal 6 karakter). |
| `password_confirmation` | String | Ya | Konfirmasi password (harus sama dengan `password`). |
| `id_users` | String | Tidak | ID pengguna kustom. Jika kosong, digenerate otomatis: `USR-XXXXXXXX`. |
| `role` | String | Tidak | Default: `user`. Pilihan: `user`, `admin`, `manager`, `cashier`. |
| `value` | Object/JSON | Tidak | Data profil tambahan berformat JSON. |

#### Contoh Payload Request:
```json
{
  "nama": "Budi Santoso",
  "username": "budisantoso",
  "email": "budi@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "user",
  "value": {
    "phone": "081234567890",
    "alamat": "Jl. Merdeka No. 12, Surabaya",
    "preferensi": {
      "theme": "dark"
    }
  }
}
```

#### Respon Sukses (`201 Created`):
```json
{
  "status": "success",
  "code": 201,
  "message": "User successfully registered.",
  "data": {
    "user": {
      "id": 3,
      "id_users": "USR-K8A9B2C1",
      "nama": "Budi Santoso",
      "username": "budisantoso",
      "email": "budi@example.com",
      "role": "user",
      "key": null,
      "value": {
        "phone": "081234567890",
        "alamat": "Jl. Merdeka No. 12, Surabaya",
        "preferensi": {
          "theme": "dark"
        }
      },
      "created_at": "2026-11-17T11:00:00+07:00",
      "updated_at": "2026-11-17T11:00:00+07:00"
    }
  }
}
```

#### Respon Gagal Validasi (`422 Unprocessable Content`):
```json
{
  "status": "error",
  "code": 422,
  "message": "Validation error.",
  "errors": {
    "username": [
      "Username sudah digunakan oleh pengguna lain."
    ],
    "password": [
      "Konfirmasi password tidak cocok."
    ]
  }
}
```

---

### 5.2. Login Pengguna

- **Method:** `POST`
- **URL:** `http://localhost:8000/v1/auth/login`
- **Headers:**
  - `X-API-KEY: pos_secret_token_1234567890`
  - `Content-Type: application/json`

#### Form / Parameter yang Diisi:
| Field | Tipe | Wajib? | Keterangan |
| :--- | :--- | :--- | :--- |
| `login` (atau `username` / `email`) | String | Ya | Username atau email pengguna yang terdaftar. |
| `password` | String | Ya | Password akun pengguna. |

#### Contoh Payload Request:
```json
{
  "login": "budisantoso",
  "password": "password123"
}
```

#### Respon Sukses (`200 OK`):
*(Perhatikan kolom `key` yang langsung diisi dengan hasil hash)*
```json
{
  "status": "success",
  "code": 200,
  "message": "Login successful.",
  "data": {
    "user": {
      "id": 3,
      "id_users": "USR-K8A9B2C1",
      "nama": "Budi Santoso",
      "username": "budisantoso",
      "email": "budi@example.com",
      "role": "user",
      "key": "$2y$12$N9HwJc8K0pE9b1Y... (Bcrypt Hash tersimpan di DB)",
      "value": {
        "phone": "081234567890",
        "alamat": "Jl. Merdeka No. 12, Surabaya"
      },
      "updated_at": "2026-11-17T11:05:00+07:00"
    },
    "login_key_details": {
      "random_12": "x9L2m7K0p1Qa",
      "username": "budisantoso",
      "raw_key_combined": "x9L2m7K0p1Qabudisantoso",
      "hashed_key_stored": "$2y$12$N9HwJc8K0pE9b1Y...",
      "explanation": "Tepat 12 karakter acak (huruf + angka) digabung dengan username lalu di-hash dan disimpan pada kolom key di tabel users."
    }
  }
}
```

#### Respon Gagal Kredensial Salah (`401 Unauthorized`):
```json
{
  "status": "error",
  "code": 401,
  "message": "Unauthorized: Invalid username/email or password.",
  "error": "INVALID_CREDENTIALS"
}
```

---

### 5.3. Profil User / Me

- **Method:** `GET`
- **URL:** `http://localhost:8000/v1/auth/me`
- **Headers:**
  - `X-API-KEY: pos_secret_token_1234567890`
  - `X-USER-ID: USR-K8A9B2C1` *(atau via query parameter `?id_users=USR-K8A9B2C1`)*

#### Respon Sukses (`200 OK`):
```json
{
  "status": "success",
  "code": 200,
  "message": "User profile retrieved successfully.",
  "data": {
    "user": {
      "id": 3,
      "id_users": "USR-K8A9B2C1",
      "nama": "Budi Santoso",
      "username": "budisantoso",
      "email": "budi@example.com",
      "role": "user",
      "key": "$2y$12$N9HwJc8K0pE9b1Y...",
      "value": {
        "phone": "081234567890",
        "alamat": "Jl. Merdeka No. 12, Surabaya"
      },
      "created_at": "2026-11-17T11:00:00+07:00",
      "updated_at": "2026-11-17T11:05:00+07:00"
    }
  }
}
```

#### Respon Gagal Tidak Ditemukan (`404 Not Found`):
```json
{
  "status": "error",
  "code": 404,
  "message": "Not Found: User with the specified identifier does not exist.",
  "error": "USER_NOT_FOUND"
}
```

---

### 5.4. Update / Upload Data Kolom `value` (JSON)

- **Method:** `POST`
- **URL:** `http://localhost:8000/v1/auth/update-value`
- **Headers:**
  - `X-API-KEY: pos_secret_token_1234567890`
  - `Content-Type: application/json`

#### Form / Parameter yang Diisi:
| Field | Tipe | Wajib? | Keterangan |
| :--- | :--- | :--- | :--- |
| `id_users` (atau `username`) | String | Ya | Identifier pengguna yang akan di-update. |
| `value` | Object/String | Ya | Data JSON baru yang ingin disimpan. |
| `mode` | String | Tidak | `replace` (menimpa) atau `merge` (menggabungkan). Default: `replace`. |

#### Contoh Payload Request:
```json
{
  "id_users": "USR-K8A9B2C1",
  "mode": "merge",
  "value": {
    "rekening_bank": "BCA 1234567890",
    "saldo_poin": 50000,
    "verifikasi_ktp": true
  }
}
```

#### Respon Sukses (`200 OK`):
```json
{
  "status": "success",
  "code": 200,
  "message": "User JSON value updated successfully.",
  "data": {
    "id_users": "USR-K8A9B2C1",
    "username": "budisantoso",
    "mode": "merge",
    "value": {
      "phone": "081234567890",
      "alamat": "Jl. Merdeka No. 12, Surabaya",
      "rekening_bank": "BCA 1234567890",
      "saldo_poin": 50000,
      "verifikasi_ktp": true
    }
  }
}
```

---

### 5.5. Logout API

- **Method:** `POST`
- **URL:** `http://localhost:8000/v1/auth/logout`
- **Headers:**
  - `X-API-KEY: pos_secret_token_1234567890`

#### Respon Sukses (`200 OK`):
```json
{
  "status": "success",
  "code": 200,
  "message": "User successfully logged out."
}
```

---

## 6. Katalog Lengkap Respon Status Code HTTP

Aplikasi telah dilengkapi endpoint simulasi langsung:
`GET /v1/auth/status-demo/{kode}` (Contoh: `/v1/auth/status-demo/203`)

Berikut adalah dokumentasi seluruh variasi status kode yang didukung:

### 🟢 Status 2xx (Success)

#### 1. `200 OK`
- **Kondisi:** Permintaan berhasil dijalankan (Login berhasil, Get Me berhasil, Update Value berhasil).
- **Contoh Format Respon:**
  ```json
  {
    "status": "success",
    "code": 200,
    "message": "Permintaan berhasil diproses secara normal.",
    "data": { ... }
  }
  ```

#### 2. `201 Created`
- **Kondisi:** Resource data baru berhasil dibuat di database (misal saat register akun baru).
- **Contoh Format Respon:**
  ```json
  {
    "status": "success",
    "code": 201,
    "message": "User successfully registered.",
    "data": { "user": { ... } }
  }
  ```

#### 3. `203 Non-Authoritative Information`
- **Kondisi:** Permintaan berhasil diproses, namun informasi yang dikembalikan berasal dari sumber sekunder, cache proxy, atau telah mengalami transformasi metadata.
- **Contoh Format Respon:**
  ```json
  {
    "status": "success",
    "code": 203,
    "message": "203 Non-Authoritative Information: Metadata berasal dari proxy/cache sekunder.",
    "meta": {
      "source": "cache-proxy",
      "timestamp": "2026-11-17T11:00:00+07:00"
    }
  }
  ```

---

### 🔴 Status 4xx (Client Error)

#### 4. `400 Bad Request`
- **Kondisi:** Parameter wajib tidak lengkap, format data payload salah, atau sintaks JSON rusak.
- **Contoh Format Respon:**
  ```json
  {
    "status": "error",
    "code": 400,
    "message": "Bad Request: Identifier pengguna (id_users atau username) wajib disertakan.",
    "error": "MISSING_USER_IDENTIFIER"
  }
  ```

#### 5. `401 Unauthorized`
- **Kondisi:** Klien gagal melewati otentikasi.
  1. API Key tidak disertakan pada header/query.
  2. API Key salah atau tidak cocok dengan `.env`.
  3. Kombinasi Username/Email dan Password salah saat login.
- **Contoh Format Respon (API Key Salah):**
  ```json
  {
    "status": "error",
    "code": 401,
    "message": "Unauthorized: The provided API Key is invalid.",
    "error": "INVALID_API_KEY"
  }
  ```

#### 6. `403 Forbidden`
- **Kondisi:** Klien telah teridentifikasi namun tidak memiliki hak akses/izin (*role privilege*) untuk melakukan aksi tertentu.
- **Contoh Format Respon:**
  ```json
  {
    "status": "error",
    "code": 403,
    "message": "403 Forbidden: Anda tidak memiliki otoritas untuk mengakses data ini.",
    "error": "FORBIDDEN"
  }
  ```

#### 7. `404 Not Found`
- **Kondisi:** Data pengguna atau endpoint URL yang diminta tidak ditemukan di server.
- **Contoh Format Respon:**
  ```json
  {
    "status": "error",
    "code": 404,
    "message": "Not Found: User with the specified identifier does not exist.",
    "error": "USER_NOT_FOUND"
  }
  ```

#### 8. `422 Unprocessable Content (Validation Error)`
- **Kondisi:** Data yang dikirim klien tidak lolos validasi input (misal: format email tidak valid, username sudah dipakai orang lain, password kurang dari 6 digit, format JSON value tidak valid).
- **Contoh Format Respon:**
  ```json
  {
    "status": "error",
    "code": 422,
    "message": "Validation error.",
    "errors": {
      "email": [
        "Email sudah terdaftar."
      ],
      "password": [
        "Password minimal 6 karakter."
      ]
    }
  }
  ```

---

### 🔴 Status 5xx (Server Error)

#### 9. `500 Internal Server Error`
- **Kondisi:** Terjadi kendala internal pada sisi server atau database.
- **Contoh Format Respon:**
  ```json
  {
    "status": "error",
    "code": 500,
    "message": "500 Internal Server Error: Terjadi kesalahan internal pada server.",
    "error": "SERVER_ERROR"
  }
  ```

---

## 7. Halaman Web (User Interface Blade)

Tersedia antarmuka web modern, responsif, dan elegan:

1. **Halaman Login:** `http://localhost:8000/login`
   - Form input Username / Email dan Password.
   - Tombol Show/Hide Password interaktif.
   - Kotak informasi mekanisme otomatis pembentukan kunci login.
2. **Halaman Register:** `http://localhost:8000/register`
   - Form input Nama, Username, Email, Password, Konfirmasi Password, Pilihan Role.
   - Input opsional ID Users kustom (atau otomatis jika kosong).
   - Input khusus data kolom `value` JSON: No. HP, Alamat, serta editor textarea JSON langsung dengan template sampel.
3. **Halaman Dashboard:** `http://localhost:8000/dashboard`
   - Menampilkan detail profil user saat ini.
   - **Card Status Kolom `key`**: Menampilkan nilai hash dari 12 karakter acak + username yang terbentuk saat login.
   - **Card Kolom `value`**: Menampilkan JSON yang tersimpan dengan format rapi (*pretty print*).
   - **Tabel Daftar Pengguna**: Menampilkan tabel pengguna yang tersimpan di database.
   - Quick copy snippet perintah cURL untuk pengujian API.

---

## 8. Panduan Menjalankan Aplikasi di Lokal

Untuk menjalankan server Laravel di lingkungan Laragon:
```powershell
# Jalankan server
php -c php.ini artisan serve
```
Aplikasi akan aktif di `http://127.0.0.1:8000`.

### Akun Uji Coba Bawaan (Seeder):
- **User Biasa:**
  - Username: `budi`
  - Password: `password123`
  - Role: `user`
- **Admin:**
  - Username: `admin`
  - Password: `admin123`
  - Role: `admin`
