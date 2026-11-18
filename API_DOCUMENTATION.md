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

## 7. Dokumentasi Lengkap API Produk Digital (`/v1/products`)

Sesuai dengan skema tabel **`products`** pada file ERD POS Market Kita, sistem menyediakan endpoint REST API khusus untuk **Produk Digital** (E-Book, Source Code, Template, Lisensi, Kursus, Asset Digital).

### Daftar Endpoint:
| Metode | Endpoint URL | Keterangan | Autentikasi |
| :--- | :--- | :--- | :--- |
| `POST` | `/v1/products` | **Input / Tambah Produk Digital Baru** | Wajib `X-API-KEY` & `key` user |
| `GET` | `/v1/products` | Ambil Daftar Produk (Pagination & Search) | Wajib `X-API-KEY` & `id_users` (tidak perlu key) |
| `GET` | `/v1/products/{id}` | Detail Produk (Berdasarkan ID / UUID / Slug) | Wajib `X-API-KEY` |
| `PUT` | `/v1/products/{id}` | **Perbarui / Edit Data Produk Digital** | Wajib `X-API-KEY` & `key` user |
| `DELETE` | `/v1/products/{id}` | **Hapus Produk Digital (Soft Delete)** | Wajib `X-API-KEY` & `key` user |

---

### A. Endpoint Input Produk Baru: `POST /v1/products`

Digunakan untuk mendaftarkan produk digital baru ke dalam sistem katalog POS Market Kita.

#### Header HTTP Wajib:
```http
Content-Type: application/json
Accept: application/json
X-API-KEY: pos_secret_token_1234567890
```

#### Form Body Parameter (Payload JSON):
| Field | Tipe Data | Wajib? | Keterangan |
| :--- | :--- | :--- | :--- |
| `name` | `string` (max: 200) | **Wajib** | Nama produk digital (contoh: "E-Book Master Laravel 2026"). |
| `price` | `numeric` (min: 0) | **Wajib** | Harga jual produk dalam Rupiah (contoh: `150000`). |
| `stock` | `integer` (min: 0) | Opsional | Jumlah ketersediaan unit produk. Kosongkan / null jika tak terbatas (unlimited). |
| `key` | `string` | **Wajib** | Key dari tabel `users` (`users.key`) yang didapat saat login. Dapat juga dikirim via header `X-USER-KEY`. |
| `file_url` | `string` (URL) | Opsional | Link download asset digital (Google Drive, S3, link unduhan). Masuk ke kolom JSON `value.file_url`. |
| `deskripsi` | `string` (max: 5000)| Opsional | Deskripsi lengkap produk. Masuk ke kolom JSON `value.deskripsi`. |
| `gambar` | `string` (URL) | Opsional | URL gambar / thumbnail produk. Masuk ke kolom JSON `value.gambar`. |
| `store_website_id`| `integer` | Opsional | ID toko website penjual (`store_websites.id`). |
| `id_users` | `string` | Opsional | ID penjual (misal `POS-K8A9B2C4`). Opsional jika `key` sudah unik. |
| `is_active` | `boolean` | Opsional | Status ketersediaan produk (default: `true`). |
| `value` | `object` (JSON) | Opsional | Object kustom jika ingin menambahkan metadata JSON lain ke kolom `value`. |

#### Pengelolaan Kolom `value` (JSON) & Kolom `key`:
Sesuai rancangan ERD:
- `value : JSON (file_url, gambar, deskripsi, user_key)`
- Saat Anda mengirimkan `file_url`, `gambar`, `deskripsi`, serta `key`, sistem secara otomatis menyimpannya ke kolom `products.value` JSON dan menghubungkan kepemilikan produk ke user yang memiliki `key` tersebut di tabel `users`.

#### Contoh Request (cURL):
```bash
curl -X POST "http://localhost:8000/v1/products" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-API-KEY: pos_secret_token_1234567890" \
  -H "X-USER-KEY: $2y$12$N9HwJc8K0pE9b1Y..." \
  -d '{
    "name": "Source Code POS Market Pro 2026",
    "price": 250000,
    "key": "$2y$12$N9HwJc8K0pE9b1Y...",
    "file_url": "https://storage.googleapis.com/assets/pos-pro-v1.zip",
    "deskripsi": "Full source code aplikasi POS berbasis Laravel 12 & Tailwind CSS.",
    "gambar": "https://images.unsplash.com/photo-1556742049-0a67c5574f73",
    "is_active": true
  }'
```

---

### B. Katalog Respon Lengkap untuk Input Produk (`POST /v1/products`)

#### 1. `201 Created` (Produk Berhasil Ditambahkan)
```json
{
  "status": "success",
  "code": 201,
  "message": "Produk digital baru berhasil disimpan ke database.",
  "data": {
    "id": 1,
    "uuid": "4e758a5c-5cf1-45bc-9df2-51a8d05cf5bf",
    "user_id": 1,
    "user_key": "$2y$12$N9HwJc8K0pE9b1Y...",
    "user": {
      "id": 1,
      "id_users": "POS-X8K1N9Q4",
      "username": "budi",
      "nama": "Budi Santoso",
      "key": "$2y$12$N9HwJc8K0pE9b1Y..."
    },
    "store_website_id": null,
    "name": "Source Code POS Market Pro 2026",
    "slug": "source-code-pos-market-pro-2026-f7a1b9",
    "product_type": "digital",
    "price": "250000.00",
    "stock": null,
    "is_active": true,
    "value": {
      "file_url": "https://storage.googleapis.com/assets/pos-pro-v1.zip",
      "gambar": "https://images.unsplash.com/photo-1556742049-0a67c5574f73",
      "deskripsi": "Full source code aplikasi POS berbasis Laravel 12 & Tailwind CSS.",
      "user_key": "$2y$12$N9HwJc8K0pE9b1Y..."
    },
    "created_at": "2026-11-18T11:45:00+07:00",
    "updated_at": "2026-11-18T11:45:00+07:00"
  }
}
```

#### 2. `401 Unauthorized` (Key User Hilang / Tidak Valid)
Terjadi jika field `key` atau header `X-USER-KEY` tidak disertakan atau nilainya tidak cocok dengan kolom `key` di tabel `users`:
```json
{
  "status": "error",
  "code": 401,
  "message": "Key dari tabel user wajib disertakan untuk input produk. Sertakan field \"key\" pada body atau header \"X-USER-KEY\".",
  "error": "MISSING_USER_KEY"
}
```
Atau jika key yang dikirim tidak cocok:
```json
{
  "status": "error",
  "code": 401,
  "message": "Key pengguna tidak valid atau tidak cocok dengan data di tabel users.",
  "error": "INVALID_USER_KEY"
}
```

#### 2. `422 Unprocessable Content` (Validasi Form Gagal)
Terjadi jika field wajib tidak diisi atau format harga salah:
```json
{
  "status": "error",
  "code": 422,
  "message": "Validasi input produk gagal.",
  "errors": {
    "name": [
      "Nama produk wajib diisi."
    ],
    "price": [
      "Harga produk harus berupa angka numerik."
    ]
  }
}
```

#### 3. `401 Unauthorized` (API Key Hilang atau Salah)
Terjadi jika header `X-API-KEY` tidak disertakan atau nilainya tidak cocok dengan `.env`:
```json
{
  "status": "error",
  "code": 401,
  "message": "Unauthorized: API Key is required. Please provide it via X-API-KEY header, Bearer token, or api_key parameter.",
  "error": "MISSING_API_KEY"
}
```

#### 4. `400 Bad Request` (Format Body JSON Salah)
Terjadi jika payload JSON yang dikirimkan rusak/tidak valid:
```json
{
  "status": "error",
  "code": 400,
  "message": "Bad Request: Format JSON tidak valid atau malformed.",
  "error": "BAD_JSON_PAYLOAD"
}
```

#### 5. `500 Internal Server Error` (Kesalahan Server / Database)
```json
{
  "status": "error",
  "code": 500,
  "message": "500 Internal Server Error: Terjadi kesalahan internal pada server saat menyimpan produk.",
  "error": "SERVER_ERROR"
}
```

---

### C. Endpoint Ambil Data Produk (Isolasi Khusus per ID User)

#### 1. Ambil Data Produk Milik User: `GET /v1/products` atau `GET /v1/products/user/{id_users}`

> **Aturan Keamanan Data:**
> - **Tidak memerlukan `key`** dari tabel user (gausah masukin key login).
> - **WAJIB memasukkan identifier user (`id_users` / `X-USER-ID`)** agar data produk terisolasi per user dan tidak dapat dilihat oleh user lain.
> - Jika dipanggil tanpa `id_users`, sistem mengembalikan error `400 Bad Request` (`MISSING_USER_IDENTIFIER`).

- **Metode Pemanggilan:**
  1. **Query Parameter:** `GET http://localhost:8000/v1/products?id_users=POS-X8K1N9Q4`
  2. **URL Path Langsung:** `GET http://localhost:8000/v1/products/user/POS-X8K1N9Q4`
  3. **Header HTTP:** `GET http://localhost:8000/v1/products` dengan header `X-USER-ID: POS-X8K1N9Q4`
- **Filter Tambahan (Opsional):**
  - `?search=keyword` : Filter pencarian nama atau slug produk
  - `?is_active=1` / `?is_active=0` : Filter status aktif atau draf
  - `?per_page=10` : Jumlah item per halaman (1–100)

- **Contoh Request (cURL):**
```bash
curl -X GET "http://localhost:8000/v1/products?id_users=POS-X8K1N9Q4" \
  -H "X-API-KEY: pos_secret_token_1234567890"
```

- **Respon Sukses `200 OK` (Hanya Produk Milik User Tersebut):**
```json
{
  "status": "success",
  "code": 200,
  "message": "Daftar data produk digital milik user 'Budi Santoso' (POS-X8K1N9Q4) berhasil diambil.",
  "user": {
    "id": 2,
    "id_users": "POS-X8K1N9Q4",
    "username": "budi",
    "nama": "Budi Santoso"
  },
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 1,
    "last_page": 1
  },
  "data": [
    {
      "id": 1,
      "uuid": "4e758a5c-5cf1-45bc-9df2-51a8d05cf5bf",
      "user_id": 2,
      "name": "Source Code POS Market Pro 2026",
      "slug": "source-code-pos-market-pro-2026-f7a1b9",
      "product_type": "digital",
      "price": "250000.00",
      "stock": null,
      "is_active": true,
      "value": {
        "file_url": "https://storage.googleapis.com/assets/pos-pro-v1.zip",
        "gambar": "https://images.unsplash.com/photo-1556742049-0a67c5574f73",
        "deskripsi": "Full source code aplikasi POS berbasis Laravel 12 & Tailwind CSS."
      }
    }
  ]
}
```

- **Respon Error `400 Bad Request` (Jika `id_users` Tidak Disertakan):**
```json
{
  "status": "error",
  "code": 400,
  "message": "Identifier user (id_users) wajib disertakan untuk mengambil data produk agar data tidak terlihat oleh pengguna lain. Gunakan parameter \"?id_users=POS-XXXX\", header \"X-USER-ID\", atau endpoint \"/v1/products/user/{id_users}\".",
  "error": "MISSING_USER_IDENTIFIER"
}
```

- **Respon Error `404 Not Found` (Jika `id_users` Tidak Ditemukan):**
```json
{
  "status": "error",
  "code": 404,
  "message": "Pengguna dengan identifier 'POS-SALAH' tidak ditemukan di database.",
  "error": "USER_NOT_FOUND"
}
```

---

#### 2. Detail Produk: `GET /v1/products/{id_or_uuid_or_slug}`
- **Query Parameter Opsional:** `?id_users=POS-X8K1N9Q4` (untuk memastikan produk tersebut milik user yang bersangkutan).
- **Respon `200 OK`:** Berhasil mengembalikan detail lengkap produk.
- **Respon `404 Not Found`:** Jika produk tidak ditemukan atau bukan milik user yang bersangkutan.
- **Respon `404 Not Found`:** Jika produk tidak ditemukan:
```json
{
  "status": "error",
  "code": 404,
  "message": "Produk digital dengan identifier '999' tidak ditemukan.",
  "error": "PRODUCT_NOT_FOUND"
}
```

---

### D. Endpoint Update / Edit Produk: `PUT /v1/products/{id}`

Digunakan untuk mengubah atau memperbarui informasi produk digital milik user (termasuk harga, nama, stok, link asset download, gambar, dan metadata JSON).

> **Ketentuan Keamanan Ketat:**
> - **Wajib menyertakan `key`** dari tabel `users` (dapat dikirim pada body JSON `key`, query parameter `?key=...`, atau header `X-USER-KEY`).
> - **Verifikasi Kepemilikan Produk (Data Ownership):** Sistem memvalidasi bahwa `key` yang dikirim benar-benar milik pengguna yang memiliki produk tersebut (`product.user_id === user.id`). Jika user A mencoba mengedit produk user B, server mengembalikan status `403 Forbidden` (`FORBIDDEN_NOT_OWNER`).

#### Header HTTP Wajib:
```http
Content-Type: application/json
Accept: application/json
X-API-KEY: pos_secret_token_1234567890
X-USER-KEY: $2y$12$N9HwJc8K0pE9b1Y...
```

#### Parameter Body Request (JSON):
| Field | Tipe Data | Wajib? | Keterangan |
| :--- | :--- | :--- | :--- |
| `key` | `string` | **Wajib\*** | Key otentikasi dari tabel `users`. (\*Wajib di body jika tidak dikirim via header `X-USER-KEY`). |
| `name` | `string` (max: 200) | Opsional | Nama baru produk digital. |
| `price` | `numeric` (min: 0) | Opsional | Harga jual baru dalam Rupiah. |
| `stock` | `integer` (min: 0) | Opsional | Stok produk baru. Masukkan angka bulat atau `null` untuk unlimited. |
| `file_url` | `string` (URL) | Opsional | Link download asset digital baru (masuk ke JSON `value.file_url`). |
| `gambar` | `string` (URL) | Opsional | URL gambar / thumbnail baru dari ImageKit.io (masuk ke JSON `value.gambar`). |
| `deskripsi` | `string` (max: 5000)| Opsional | Deskripsi produk baru (masuk ke JSON `value.deskripsi`). |
| `is_active` | `boolean` | Opsional | Status aktif (`true`) atau draf (`false`). |
| `value` | `object` (JSON) | Opsional | Payload JSON kustom tambahan untuk disimpan ke kolom `value`. |

#### Contoh Request Update (cURL):
```bash
curl -X PUT "http://localhost:8000/v1/products/1" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-API-KEY: pos_secret_token_1234567890" \
  -H "X-USER-KEY: $2y$12$N9HwJc8K0pE9b1Y..." \
  -d '{
    "key": "$2y$12$N9HwJc8K0pE9b1Y...",
    "name": "Source Code POS Market Pro 2026 - Versi 2.0",
    "price": 275000,
    "stock": 100,
    "file_url": "https://storage.googleapis.com/assets/pos-pro-v2.zip",
    "gambar": "https://ik.imagekit.io/p0v1tjsucu/pos/pos-pro-preview.jpg",
    "deskripsi": "Update fitur printer thermal Bluetooth dan webhook pembayaran otomatis.",
    "is_active": true
  }'
```

#### Katalog Respon Lengkap untuk Update Produk (`PUT /v1/products/{id}`):

##### 1. `200 OK` (Pembaruan Berhasil)
```json
{
  "status": "success",
  "code": 200,
  "message": "Data produk digital berhasil diperbarui.",
  "data": {
    "id": 1,
    "uuid": "4e758a5c-5cf1-45bc-9df2-51a8d05cf5bf",
    "user_id": 2,
    "user_key": "$2y$12$N9HwJc8K0pE9b1Y...",
    "name": "Source Code POS Market Pro 2026 - Versi 2.0",
    "slug": "source-code-pos-market-pro-2026-f7a1b9",
    "product_type": "digital",
    "price": "275000.00",
    "stock": 100,
    "is_active": true,
    "value": {
      "file_url": "https://storage.googleapis.com/assets/pos-pro-v2.zip",
      "gambar": "https://ik.imagekit.io/p0v1tjsucu/pos/pos-pro-preview.jpg",
      "deskripsi": "Update fitur printer thermal Bluetooth dan webhook pembayaran otomatis.",
      "user_key": "$2y$12$N9HwJc8K0pE9b1Y..."
    },
    "created_at": "2026-11-18T11:45:00+07:00",
    "updated_at": "2026-11-18T12:30:00+07:00"
  }
}
```

##### 2. `401 Unauthorized` (Key User Tidak Disertakan / Tidak Valid)
- Jika field `key` atau header `X-USER-KEY` tidak dikirim:
  ```json
  {
    "status": "error",
    "code": 401,
    "message": "Key dari tabel user wajib disertakan untuk memperbarui produk. Sertakan field \"key\" pada body atau header \"X-USER-KEY\".",
    "error": "MISSING_USER_KEY"
  }
  ```
- Jika nilai `key` salah atau tidak terdaftar di database:
  ```json
  {
    "status": "error",
    "code": 401,
    "message": "Key pengguna tidak valid atau tidak cocok dengan data di tabel users.",
    "error": "INVALID_USER_KEY"
  }
  ```

##### 3. `403 Forbidden` (Bukan Pemilik Produk)
Terjadi jika pengguna memiliki `key` yang valid, namun mencoba mengubah produk milik user lain:
```json
{
  "status": "error",
  "code": 403,
  "message": "Akses ditolak. Anda tidak memiliki hak akses untuk memperbarui produk milik pengguna lain.",
  "error": "FORBIDDEN_NOT_OWNER"
}
```

##### 4. `404 Not Found` (Produk Tidak Ditemukan)
Terjadi jika ID, UUID, atau slug produk tidak ada di katalog:
```json
{
  "status": "error",
  "code": 404,
  "message": "Produk digital dengan identifier '999' tidak ditemukan.",
  "error": "PRODUCT_NOT_FOUND"
}
```

##### 5. `422 Unprocessable Content` (Validasi Gagal)
Terjadi jika isian tidak sesuai kriteria (misalnya harga negatif atau nama kosong):
```json
{
  "status": "error",
  "code": 422,
  "message": "Validasi pembaruan produk gagal.",
  "errors": {
    "price": [
      "Harga produk tidak boleh bernilai negatif."
    ],
    "stock": [
      "Stok produk harus berupa bilangan bulat."
    ]
  }
}
```

##### 6. `500 Internal Server Error`
```json
{
  "status": "error",
  "code": 500,
  "message": "500 Internal Server Error: Terjadi kesalahan internal saat memperbarui data produk.",
  "error": "SERVER_ERROR"
}
```

---

### E. Endpoint Hapus Produk (Soft Delete): `DELETE /v1/products/{id}`

Digunakan untuk menghapus produk dari katalog aktif tanpa menghilangkan jejak transaksi (Soft Delete pada kolom `deleted_at`).

> **Ketentuan Keamanan Ketat:**
> - **Wajib menyertakan `key`** dari tabel `users` (bisa melalui header `X-USER-KEY`, body JSON `{"key": "..."}`, atau parameter `?key=...`).
> - **Verifikasi Kepemilikan Produk:** Hanya pemilik produk yang dapat menghapus produk miliknya. Upaya menghapus produk milik orang lain akan ditolak dengan kode `403 Forbidden` (`FORBIDDEN_NOT_OWNER`).

#### Header HTTP Wajib:
```http
Accept: application/json
X-API-KEY: pos_secret_token_1234567890
X-USER-KEY: $2y$12$N9HwJc8K0pE9b1Y...
```

#### Parameter Request:
| Field | Tipe Data | Wajib? | Keterangan |
| :--- | :--- | :--- | :--- |
| `key` | `string` | **Wajib\*** | Key pengguna dari tabel `users`. (\*Wajib di body/query jika tidak dikirim via header `X-USER-KEY`). |

#### Contoh Request Hapus (cURL):
```bash
curl -X DELETE "http://localhost:8000/v1/products/1" \
  -H "Accept: application/json" \
  -H "X-API-KEY: pos_secret_token_1234567890" \
  -H "X-USER-KEY: $2y$12$N9HwJc8K0pE9b1Y..."
```
Atau menyertakan body JSON:
```bash
curl -X DELETE "http://localhost:8000/v1/products/1" \
  -H "Content-Type: application/json" \
  -H "X-API-KEY: pos_secret_token_1234567890" \
  -d '{"key": "$2y$12$N9HwJc8K0pE9b1Y..."}'
```

#### Katalog Respon Lengkap untuk Hapus Produk (`DELETE /v1/products/{id}`):

##### 1. `200 OK` (Soft Delete Berhasil)
```json
{
  "status": "success",
  "code": 200,
  "message": "Produk digital berhasil dihapus (Soft Delete).",
  "data": {
    "id": 1,
    "uuid": "4e758a5c-5cf1-45bc-9df2-51a8d05cf5bf",
    "name": "Source Code POS Market Pro 2026",
    "user_id": 2,
    "user_key": "$2y$12$N9HwJc8K0pE9b1Y...",
    "deleted_at": "2026-11-18T12:35:00+07:00"
  }
}
```

##### 2. `401 Unauthorized` (Key User Hilang / Tidak Cocok)
- Jika key tidak disertakan:
  ```json
  {
    "status": "error",
    "code": 401,
    "message": "Key dari tabel user wajib disertakan untuk menghapus produk. Sertakan field \"key\" pada body atau header \"X-USER-KEY\".",
    "error": "MISSING_USER_KEY"
  }
  ```
- Jika key salah / tidak ditemukan:
  ```json
  {
    "status": "error",
    "code": 401,
    "message": "Key pengguna tidak valid atau tidak cocok dengan data di tabel users.",
    "error": "INVALID_USER_KEY"
  }
  ```

##### 3. `403 Forbidden` (Bukan Pemilik Produk)
```json
{
  "status": "error",
  "code": 403,
  "message": "Akses ditolak. Anda tidak memiliki hak akses untuk menghapus produk milik pengguna lain.",
  "error": "FORBIDDEN_NOT_OWNER"
}
```

##### 4. `404 Not Found` (Produk Tidak Ditemukan)
```json
{
  "status": "error",
  "code": 404,
  "message": "Produk digital dengan identifier '999' tidak ditemukan.",
  "error": "PRODUCT_NOT_FOUND"
}
```

##### 5. `500 Internal Server Error`
```json
{
  "status": "error",
  "code": 500,
  "message": "500 Internal Server Error: Terjadi kesalahan internal saat menghapus data produk.",
  "error": "SERVER_ERROR"
}
```

---

## 8. Dokumentasi Lengkap API Wallet (`/v1/wallet`)

Sesuai rancangan skema tabel **`wallets`** dan **`withdrawals`** pada ERD POS Market Kita:
- Setiap pengguna penjual (seller) memiliki 1 dompet digital (`wallets`) yang terhubung dengan `user_id`.
- Saldo aktif (`balance`) otomatis bertambah setiap ada pembeli yang melunasi pembayaran produk digital via QRIS Midtrans.
- Penjual dapat mengatur rekening bank pencairan dan melakukan penarikan saldo (`withdraw`).

### Daftar Endpoint Wallet:
| Metode | Endpoint URL | Keterangan | Autentikasi |
| :--- | :--- | :--- | :--- |
| `GET` | `/v1/wallet` | **Cek Informasi Saldo & Mutasi Wallet** | Wajib `X-API-KEY` & `key` user |
| `POST` | `/v1/wallet/bank-account` | **Atur / Update Rekening Bank Pencairan** | Wajib `X-API-KEY` & `key` user |
| `POST` | `/v1/wallet/withdraw` | **Pengajuan Penarikan Saldo (Withdrawal)** | Wajib `X-API-KEY` & `key` user |

---

### 8.1. Cek Informasi Saldo Wallet: `GET /v1/wallet`

Mengambil data saldo aktif, saldo tertahan (pending), info rekening bank yang tersimpan di kolom JSON `value.bank_account`, serta riwayat log mutasi kredit/debit.

#### Header HTTP Wajib:
```http
Accept: application/json
X-API-KEY: pos_secret_token_1234567890
X-USER-KEY: $2y$12$N9HwJc8K0pE9b1Y...
```

#### Contoh Request (cURL):
```bash
curl -X GET "http://localhost:8000/v1/wallet" \
  -H "X-API-KEY: pos_secret_token_1234567890" \
  -H "X-USER-KEY: $2y$12$N9HwJc8K0pE9b1Y..."
```

#### Contoh Respon Sukses `200 OK`:
```json
{
  "status": "success",
  "code": 200,
  "message": "Informasi wallet milik 'Budi Santoso' berhasil diambil.",
  "data": {
    "wallet_id": 1,
    "uuid": "7a8b9c0d-1234-5678-9abc-def012345678",
    "user": {
      "id": 2,
      "id_users": "POS-X8K1N9Q4",
      "nama": "Budi Santoso",
      "username": "budi"
    },
    "balance": 250000.00,
    "pending_balance": 0.00,
    "currency": "IDR",
    "formatted_balance": "Rp 250.000",
    "formatted_pending_balance": "Rp 0",
    "bank_account": {
      "bank_name": "BCA",
      "account_number": "8830123456",
      "account_holder": "Budi Santoso",
      "updated_at": "2026-11-18T12:00:00+07:00"
    },
    "mutations": [
      {
        "id": "e4f5a6b7-8901-2345-6789-abcdef012345",
        "type": "credit",
        "amount": 250000,
        "description": "Penjualan produk digital (Invoice #INV-20261118-A1B2C3)",
        "reference_id": "INV-20261118-A1B2C3",
        "balance_after": 250000,
        "created_at": "2026-11-18T12:15:00+07:00"
      }
    ],
    "created_at": "2026-11-18T10:00:00+07:00",
    "updated_at": "2026-11-18T12:15:00+07:00"
  }
}
```

---

### 8.2. Atur Rekening Bank Pencairan: `POST /v1/wallet/bank-account`

Digunakan untuk menyimpan data rekening bank yang akan digunakan saat melakukan penarikan saldo.

#### Header HTTP Wajib:
```http
Content-Type: application/json
Accept: application/json
X-API-KEY: pos_secret_token_1234567890
X-USER-KEY: $2y$12$N9HwJc8K0pE9b1Y...
```

#### Parameter Body (JSON):
| Field | Tipe Data | Wajib? | Keterangan |
| :--- | :--- | :--- | :--- |
| `key` | `string` | **Wajib\*** | Key otentikasi user (\*bisa dikirim via header `X-USER-KEY`). |
| `bank_name` | `string` (max: 50) | **Wajib** | Nama bank penerima (contoh: `BCA`, `MANDIRI`, `BRI`, `BNI`). |
| `account_number` | `string` (max: 50) | **Wajib** | Nomor rekening tujuan. |
| `account_holder` | `string` (max: 100) | **Wajib** | Nama pemilik rekening sesuai buku tabungan. |

#### Contoh Request (cURL):
```bash
curl -X POST "http://localhost:8000/v1/wallet/bank-account" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-API-KEY: pos_secret_token_1234567890" \
  -H "X-USER-KEY: $2y$12$N9HwJc8K0pE9b1Y..." \
  -d '{
    "bank_name": "BCA",
    "account_number": "8830123456",
    "account_holder": "Budi Santoso"
  }'
```

---

### 8.3. Pengajuan Penarikan Dana (Withdrawal): `POST /v1/wallet/withdraw`

Digunakan untuk menarik saldo aktif ke rekening bank terdaftar.

#### Parameter Body (JSON):
| Field | Tipe Data | Wajib? | Keterangan |
| :--- | :--- | :--- | :--- |
| `key` | `string` | **Wajib\*** | Key otentikasi user (\*bisa dikirim via header `X-USER-KEY`). |
| `amount` | `numeric` (min: 10000) | **Wajib** | Nominal saldo yang ingin ditarik (minimal Rp 10.000). |
| `bank_name` | `string` | Opsional | Jika ingin override bank tujuan untuk penarikan ini saja. |
| `account_number` | `string` | Opsional | Jika ingin override nomor rekening tujuan. |
| `account_holder` | `string` | Opsional | Jika ingin override nama pemilik rekening. |

#### Contoh Respon Sukses `201 Created`:
```json
{
  "status": "success",
  "code": 201,
  "message": "Permintaan penarikan dana berhasil diajukan dan sedang diproses.",
  "data": {
    "withdrawal_id": 1,
    "uuid": "9b8c7d6e-5432-1098-7654-3210fedcba98",
    "reference_code": "WD-9B8C7D6E",
    "amount": 100000,
    "admin_fee": 2500,
    "net_amount": 97500,
    "status": "pending",
    "bank_destination": {
      "bank_name": "BCA",
      "account_number": "8830123456",
      "account_holder": "Budi Santoso"
    },
    "remaining_balance": 150000,
    "pending_balance": 100000,
    "requested_at": "2026-11-18T13:00:00+07:00"
  }
}
```

---

## 9. Dokumentasi API Transaksi & Checkout QRIS Midtrans (`/v1/transactions`)

Sistem menyediakan alur transaksi otomatis produk digital terintegrasi **QRIS Midtrans** yang kompatibel dengan **Wasmer** (WebAssembly PHP runtime), serta dilengkapi sistem **N+1 Concurrency Control** untuk mengantisipasi klik checkout bersamaan secara aman.

### Mekanisme N+1 & Antisipasi Klik Checkout Bersamaan:
1. **Nomor Invoice Berurutan N+1 (Sequential Sequence):**
   Setiap transaksi baru dihitung secara atomik berdasarkan jumlah transaksi harian (`N`), menghasilkan nomor invoice berurutan yang rapi: `INV-YYYYMMDD-0001-XXX`, `INV-YYYYMMDD-0002-XXX`, `INV-YYYYMMDD-0003-XXX`, dst.
2. **Pessimistic Row-Level Locking (`lockForUpdate`):**
   Seluruh proses pembuatan transaksi dan pengecekan stok dibungkus di dalam `DB::transaction()` dengan mengunci baris produk (`lockForUpdate`). Jika dua atau lebih pembeli mengklik checkout pada detik yang sama untuk sisa stok terakhir (misal stok = 1), sistem memprosesnya satu per satu secara bergiliran sehingga tidak akan pernah terjadi *overselling* (stok minus). Pembeli berikutnya otomatis menerima respon `400 PRODUCT_OUT_OF_STOCK`.
3. **Atomic Cache Lock Anti Double-Submit:**
   Sistem menerapkan kunci atomik (`Cache::lock`) berbasis kombinasi email pembeli dan ID produk selama 6 detik. Jika pembeli secara tidak sengaja mengklik tombol checkout berulang kali secara cepat, request paralel kedua ditolak secara instan dengan status `429 Too Many Requests` (`CONCURRENT_CHECKOUT_PREVENTED`).
4. **Idempotent Payment Settlement:**
   Saat webhook Midtrans atau simulasi pembayaran lunas dipanggil bersamaan, sistem mengunci baris transaksi (`lockForUpdate`). Transaksi yang sudah berstatus `paid` tidak akan memotong stok ganda atau mengkredit saldo wallet penjual lebih dari sekali.
5. **Pencegahan N+1 Query Problem (Eager Loading):**
   Semua query pemanggilan daftar transaksi produk menggunakan *eager loading* teroptimasi (`with(['items.product', 'latestPayment', 'seller', 'emailNotifications'])`) dan dilindungi oleh `Model::preventLazyLoading()` di `AppServiceProvider` untuk memastikan kueri database tetap ringan dan cepat.
6. **Proteksi Sisi Frontend UI:**
   Pada formulir checkout web, tombol *"Lanjut ke Pembayaran QRIS"* otomatis dinonaktifkan (`disabled = true`) segera setelah diklik pertama kali dan menampilkan animasi loading spinner, mencegah multi-klik dari browser pengguna.

---

### Alur Lengkap Transaksi:
1. **Pembeli Checkout:** Pembeli memasukkan `nama` dan `email` serta memilih produk digital.
2. **Invoice & QRIS Generation:** Laravel membuat record transaksi dan memanggil API Midtrans (`v2/charge` QRIS). Sistem mengembalikan payload resmi QRIS berupa `qr_string` dan link gambar QR Code `qr_code_url`.
3. **Pembayaran:** Pembeli memindai QR Code menggunakan aplikasi e-wallet atau mobile banking apa saja (GoPay, OVO, Dana, ShopeePay, BCA, Mandiri, BRI, dll).
4. **Verifikasi Otomatis (Webhook Callback):**
   - Transaksi dinyatakan **LUNAS** (`status: paid`).
   - **Stok produk otomatis dikurangi 1** (jika produk memiliki kuota stok terbatas).
   - **Saldo wallet penjual otomatis bertambah** sesuai nominal harga produk.
   - **File produk digital otomatis dikirimkan ke email pembeli** via mailable `DigitalProductDeliveryMail` dan dicatat di tabel `email_notifications`.

---

### 9.1. Checkout & Buat Tagihan QRIS: `POST /v1/transactions/checkout`

- **Endpoint:** `http://localhost:8000/v1/transactions/checkout`
- **Headers:**
  - `Content-Type: application/json`
  - `Accept: application/json`
  - `X-API-KEY: pos_secret_token_1234567890`

#### Parameter Request (JSON):
| Field | Tipe Data | Wajib? | Keterangan |
| :--- | :--- | :--- | :--- |
| `product_id` | `mixed` | **Wajib** | Identifier produk (bisa berupa ID numeric, UUID, atau Slug produk). |
| `customer_name` | `string` (max: 150) | **Wajib** | Nama lengkap pembeli. |
| `customer_email`| `string` (email) | **Wajib** | Alamat email pembeli untuk menerima kiriman file produk digital. |
| `customer_phone`| `string` | Opsional | Nomor telepon / WhatsApp pembeli. |
| `notes` | `string` | Opsional | Catatan tambahan untuk penjual. |

#### Contoh Request (cURL):
```bash
curl -X POST "http://localhost:8000/v1/transactions/checkout" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-API-KEY: pos_secret_token_1234567890" \
  -d '{
    "product_id": 1,
    "customer_name": "Ahmad Dani",
    "customer_email": "ahmaddani@example.com",
    "customer_phone": "081234567890"
  }'
```

#### Contoh Respon Sukses `201 Created`:
```json
{
  "status": "success",
  "code": 201,
  "message": "Invoice checkout berhasil dibuat. Silakan lakukan pembayaran dengan memindai kode QRIS berikut.",
  "data": {
    "invoice_number": "INV-20261118-8F2K9A",
    "uuid": "4c5d6e7f-8901-2345-6789-0123456789ab",
    "status": "unpaid",
    "total_amount": 150000,
    "formatted_amount": "Rp 150.000",
    "customer": {
      "name": "Ahmad Dani",
      "email": "ahmaddani@example.com",
      "phone": "081234567890"
    },
    "product": {
      "id": 1,
      "name": "E-Book Panduan Master REST API Laravel 12",
      "slug": "e-book-panduan-master-rest-api-laravel-12-a1b2c3",
      "price": 150000
    },
    "payment": {
      "gateway": "midtrans",
      "method": "qris",
      "qr_string": "00020101021226670016ID.CO.SHOPEE.WWW01189360091800000000000215...",
      "qr_code_url": "https://api.sandbox.midtrans.com/v2/qris/315a6b7d-8e9f-4a0b-bc1d-234567890abc/qr-code",
      "expiry_time": "2026-11-18 13:45:00",
      "is_simulated": false
    },
    "check_status_url": "http://localhost:8000/v1/transactions/INV-20261118-8F2K9A/status",
    "instructions": [
      "1. Buka aplikasi pembayaran atau mobile banking (GoPay, OVO, Dana, ShopeePay, BCA, Mandiri, BRI, dll).",
      "2. Pilih menu Bayar / Scan QRIS.",
      "3. Pindai kode QRIS yang tersedia atau gunakan link gambar qr_code_url.",
      "4. Pastikan nominal pembayaran sesuai dengan total tagihan, lalu konfirmasi pembayaran.",
      "5. Setelah pembayaran berhasil, produk digital akan otomatis dikirimkan ke email Anda dan stok berkurang."
    ]
  }
}
```

---

### 9.2. Cek Status Pembayaran: `GET /v1/transactions/{invoice_number}/status`

Digunakan untuk memeriksa apakah tagihan telah dibayar. Jika berstatus `paid`, respon akan menyertakan tautan unduhan produk digital (`download_url`).

#### Contoh Request (cURL):
```bash
curl -X GET "http://localhost:8000/v1/transactions/INV-20261118-8F2K9A/status" \
  -H "X-API-KEY: pos_secret_token_1234567890"
```

#### Contoh Respon (Jika Sudah LUNAS):
```json
{
  "status": "success",
  "code": 200,
  "message": "Status invoice 'INV-20261118-8F2K9A' adalah PAID.",
  "data": {
    "invoice_number": "INV-20261118-8F2K9A",
    "status": "paid",
    "is_paid": true,
    "total_amount": 150000,
    "formatted_amount": "Rp 150.000",
    "customer_email": "ahmaddani@example.com",
    "customer_name": "Ahmad Dani",
    "paid_at": "2026-11-18T13:20:00+07:00",
    "product": {
      "name": "E-Book Panduan Master REST API Laravel 12",
      "download_url": "https://storage.googleapis.com/assets/ebook-master-laravel.pdf"
    },
    "payment": {
      "gateway": "midtrans",
      "method": "qris",
      "status": "settlement"
    }
  }
}
```

---

### 9.3. Webhook Notifikasi Midtrans: `POST /v1/payments/midtrans-notification`

Endpoint resmi untuk menerima webhook notifikasi status dari server Midtrans.

- **URL Webhook:** `http://localhost:8000/v1/payments/midtrans-notification`
- **Method:** `POST`
- **Autentikasi:** Diverifikasi via `signature_key` (SHA512) Midtrans.

---

### 9.4. Simulasi Pembayaran Sukses Sandbox: `POST /v1/transactions/{invoice_number}/simulate-payment`

Endpoint ini disediakan khusus untuk mempermudah pengujian alur secara instan tanpa perlu membuka simulator Midtrans manual.

```bash
curl -X POST "http://localhost:8000/v1/transactions/INV-20261118-8F2K9A/simulate-payment" \
  -H "X-API-KEY: pos_secret_token_1234567890"
```

---

### 9.5. Lihat Seluruh Transaksi pada Produk Saya: `GET /v1/transactions`

Endpoint ini digunakan oleh penjual untuk melihat seluruh riwayat transaksi yang terjadi pada produk digital yang telah dibuatnya.

> **Aturan Keamanan & Isolasi Data:**
> - **Wajib menyertakan otentikasi penjual:** melalui parameter `key` / header `X-USER-KEY` (dari tabel `users.key`), atau parameter `id_users`.
> - **Isolasi Akun:** Penjual A hanya bisa melihat transaksi pada produk miliknya sendiri. Transaksi penjual lain tidak akan pernah bocor.
> - Mengembalikan ringkasan statistik pendapatan: total transaksi, transaksi lunas, transaksi pending, dan total omzet penjualan.

#### Header HTTP:
```http
Accept: application/json
X-API-KEY: pos_secret_token_1234567890
X-USER-KEY: $2y$12$N9HwJc8K0pE9b1Y...
```

#### Parameter Query (Opsional):
| Parameter | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `key` | `string` | Key user dari tabel `users` (jika tidak dikirim via header `X-USER-KEY`). |
| `id_users` | `string` | ID Pengguna penjual (contoh: `POS-X8K1N9Q4`) sebagai alternatif identitas. |
| `status` | `string` | Filter status transaksi: `paid`, `unpaid`, `cancelled`, atau `all` (default: `all`). |
| `product_id` | `mixed` | Filter transaksi untuk produk tertentu (ID, UUID, atau Slug). |
| `search` | `string` | Cari berdasarkan nomor invoice (`INV-...`), email pembeli, atau nama pembeli. |
| `date_from` | `date` (YYYY-MM-DD) | Filter awal rentang tanggal transaksi. |
| `date_to` | `date` (YYYY-MM-DD) | Filter akhir rentang tanggal transaksi. |
| `per_page` | `integer` (1-100) | Jumlah data per halaman paginasi (default: `15`). |

#### Contoh Request (cURL):
```bash
curl -X GET "http://localhost:8000/v1/transactions?status=paid" \
  -H "X-API-KEY: pos_secret_token_1234567890" \
  -H "X-USER-KEY: $2y$12$N9HwJc8K0pE9b1Y..."
```

#### Contoh Respon Sukses `200 OK`:
```json
{
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
          "phone": "081234567890",
          "notes": "Tolong kirimkan invoice ke email juga"
        },
        "items": [
          {
            "product_id": 1,
            "product_name": "E-Book Panduan Master REST API Laravel 12",
            "product_slug": "e-book-panduan-master-rest-api-laravel-12-a1b2c3",
            "qty": 1,
            "unit_price": 150000,
            "subtotal": 150000,
            "download_url": "https://storage.googleapis.com/assets/ebook-master-laravel.pdf",
            "gambar": "https://ik.imagekit.io/p0v1tjsucu/pos/cover-ebook.png"
          }
        ],
        "payment": {
          "gateway": "midtrans",
          "method": "qris",
          "status": "settlement",
          "qr_code_url": "https://api.sandbox.midtrans.com/v2/qris/315a6b7d-8e9f/qr-code",
          "external_ref_id": "315a6b7d-8e9f-4a0b-bc1d-234567890abc"
        },
        "paid_at": "2026-11-18T13:20:00+07:00",
        "created_at": "2026-11-18T13:15:00+07:00"
      }
    ],
    "pagination": {
      "total": 5,
      "per_page": 15,
      "current_page": 1,
      "last_page": 1,
      "has_more_pages": false
    }
  }
}
```

---

### 9.6. Detail Transaksi Tertentu: `GET /v1/transactions/{invoice}`

Melihat detail lengkap satu transaksi produk beserta data pembeli, item produk, payment QRIS, dan histori pengiriman email produk digital.

#### Contoh Request (cURL):
```bash
curl -X GET "http://localhost:8000/v1/transactions/INV-20261118-8F2K9A" \
  -H "X-API-KEY: pos_secret_token_1234567890" \
  -H "X-USER-KEY: $2y$12$N9HwJc8K0pE9b1Y..."
```

> **Verifikasi Hak Akses:** Jika penjual A mencoba membuka detail transaksi produk milik penjual B, sistem akan menolak dengan respon status `403 Forbidden`:
> ```json
> {
>   "status": "error",
>   "code": 403,
>   "message": "Anda tidak memiliki hak akses melihat transaksi produk milik penjual lain.",
>   "error": "FORBIDDEN_TRANSACTION_ACCESS"
> }
> ```

---

## 10. Halaman Web (User Interface Blade)

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
