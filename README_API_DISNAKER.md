# DISNAKER API

Dokumentasi REST API backend untuk aplikasi **DISNAKER Kota Tasikmalaya**.

Dokumen ini ditujukan untuk frontend developer yang mengintegrasikan web frontend dengan backend Laravel API.

> **Backend:** Laravel 13 + Laravel Sanctum + MySQL  
> **Development API:** `http://localhost:8000/api`

---

## 1. Gambaran Umum

Backend menangani beberapa kebutuhan utama:

- autentikasi dan role pengguna;
- registrasi pencari kerja dan perusahaan;
- verifikasi perusahaan oleh Admin;
- pengelolaan dan verifikasi lowongan kerja;
- profil profesional pencari kerja;
- skills, pendidikan, dan pengalaman kerja;
- upload foto dan CV;
- direktori pencari kerja publik;
- akses perusahaan terhadap profil pencari kerja publik dan CV sesuai hak akses;
- notifications;
- dashboard Admin dan Perusahaan;
- content API untuk news, training, announcements, pages, dan services;
- employment statistics.

### Catatan alur pekerjaan

Website **tidak menyediakan online job application** melalui API ini.

Pencari kerja menggunakan website untuk melihat lowongan dan informasi pekerjaan. Cara melamar mengikuti informasi yang dicantumkan pada detail lowongan, misalnya email atau instruksi dari perusahaan.

---

# 2. Teknologi

- Laravel 13
- Laravel Sanctum
- MySQL
- REST API
- Laravel Storage
- Database Notifications

---

# 3. Base URL

### Development

```text
http://localhost:8000/api
```

Contoh:

```http
GET http://localhost:8000/api/jobs
```

Jika frontend dan backend berjalan pada komputer berbeda, ganti `localhost` dengan IP komputer yang menjalankan backend.

---

# 4. Role Pengguna

Backend menggunakan tiga role utama:

| Role | Keterangan |
|---|---|
| `admin` | Admin/pegawai Disnaker yang mengelola data dan melakukan verifikasi |
| `perusahaan` | Perusahaan yang mengelola profil dan lowongan |
| `pencari_kerja` | Pencari kerja yang mengelola profil profesional |

Pengunjung umum dapat mengakses endpoint publik tanpa login.

## Aturan registrasi role

Public registration hanya menerima:

```text
pencari_kerja
perusahaan
```

Role `admin` **tidak dapat dibuat melalui public registration**.

---

# 5. Authentication

API menggunakan **Laravel Sanctum Bearer Token**.

Untuk endpoint yang membutuhkan authentication:

```http
Authorization: Bearer TOKEN
Accept: application/json
```

Contoh:

```http
GET /api/me
Authorization: Bearer 1|xxxxxxxxxxxxxxxx
Accept: application/json
```

Frontend perlu menyimpan token hasil login dan mengirimkannya pada request protected.

---

# 6. Authentication API

## 6.1 Register

Public registration menggunakan satu endpoint:

```http
POST /api/register
```

### Register pencari kerja

```json
{
  "name": "Budi",
  "email": "budi@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "pencari_kerja"
}
```

Setelah berhasil:

- User dibuat dengan role `pencari_kerja`;
- Job Seeker Profile dibuat otomatis;
- `full_name` profile mengikuti `name`;
- profile baru dibuat dengan `is_public = false`;
- akun dapat langsung digunakan untuk login;
- tidak membutuhkan approval Admin.

### Register perusahaan

```json
{
  "name": "Nama Pemilik",
  "email": "owner@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "perusahaan",
  "company_name": "PT Maju Jaya",
  "description": "Perusahaan yang bergerak di bidang teknologi.",
  "address": "Tasikmalaya",
  "phone": "081234567890",
  "company_email": "hrd@majubersama.com",
  "website": "https://example.com"
}
```

Setelah berhasil:

- User dibuat dengan role `perusahaan`;
- Company dibuat otomatis;
- status Company dimulai dari `pending`;
- perusahaan dapat login;
- operasi yang membutuhkan perusahaan terverifikasi harus menunggu approval Admin.

### Role yang tidak diperbolehkan

Request seperti:

```json
{
  "name": "Fake Admin",
  "email": "fake-admin@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "admin"
}
```

harus ditolak oleh validation.

---

## 6.2 Login

```http
POST /api/login
```

### Request

```json
{
  "email": "admin@disnaker.test",
  "password": "password"
}
```

### Response contoh

```json
{
  "success": true,
  "message": "Login berhasil.",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin Disnaker",
      "email": "admin@disnaker.test",
      "role": "admin"
    },
    "token": "1|xxxxxxxxxxxxxxxx"
  }
}
```

---

## 6.3 Current User

```http
GET /api/me
```

Authentication:

```text
auth:sanctum
```

---

## 6.4 Logout

```http
POST /api/logout
```

Authentication:

```text
auth:sanctum
```

---

# 7. Legacy Company Registration

Endpoint lama tetap dipertahankan untuk kompatibilitas implementasi sebelumnya:

```http
POST /api/company/register
```

Endpoint ini tidak membutuhkan login.

Contoh request:

```json
{
  "name": "Nama Pemilik",
  "email": "owner@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "company_name": "PT Maju Jaya",
  "description": "Perusahaan yang bergerak di bidang teknologi.",
  "address": "Tasikmalaya",
  "phone": "081234567890",
  "company_email": "hrd@majubersama.com",
  "website": "https://example.com"
}
```

> **Rekomendasi frontend baru:** gunakan `POST /api/register` untuk registrasi pencari kerja maupun perusahaan. Endpoint `/api/company/register` dipertahankan untuk kompatibilitas.

---

# 8. Public API

Semua endpoint pada bagian ini tidak membutuhkan authentication.

## 8.1 Lowongan

```http
GET /api/jobs
GET /api/jobs/{job}
```

Public API hanya menampilkan lowongan yang memenuhi kondisi publik, termasuk status approval dan masa berlaku.

Fitur yang tersedia pada daftar lowongan meliputi pencarian/filter sesuai implementasi endpoint.

Contoh:

```text
GET /api/jobs?search=Backend
GET /api/jobs?location=Tasikmalaya
GET /api/jobs?per_page=5
GET /api/jobs?search=Backend&location=Tasikmalaya&per_page=5
```

Detail lowongan:

```http
GET /api/jobs/{job}
```

Detail dapat digunakan untuk menampilkan informasi seperti:

- poster;
- judul posisi;
- nama perusahaan;
- lokasi;
- tanggal dibuat;
- masa berlaku;
- deskripsi;
- jumlah views;
- informasi cara melamar jika tersedia pada data lowongan.

> Backend tidak menyediakan endpoint untuk submit lamaran kerja online.

---

## 8.2 News

```http
GET /api/news
GET /api/news/{news}
```

Public API hanya menampilkan news yang sudah dipublikasikan.

---

## 8.3 Training

```http
GET /api/trainings
GET /api/trainings/{training}
```

Public API hanya menampilkan training yang sudah dipublikasikan.

---

## 8.4 Announcement

```http
GET /api/announcements
GET /api/announcements/{announcement}
```

Public API hanya menampilkan announcement yang sudah dipublikasikan.

---

## 8.5 Pages

```http
GET /api/pages
GET /api/pages/{slug}
```

Detail page menggunakan `slug`.

Contoh:

```text
GET /api/pages/profil
```

---

## 8.6 Services

```http
GET /api/services
GET /api/services/{slug}
```

Detail service menggunakan `slug`.

---

## 8.7 Employment Statistics

```http
GET /api/employment-statistics
```

Endpoint mengembalikan data statistik ketenagakerjaan dalam bentuk aggregate/public data.

Frontend tidak boleh menganggap endpoint ini sebagai endpoint untuk mengambil data pribadi pencari kerja.

---

# 9. Public Job Seeker API

Profil pencari kerja dapat ditemukan pada public API hanya jika:

```text
is_public = true
```

## 9.1 Daftar pencari kerja

```http
GET /api/job-seekers
```

### Query parameter

| Parameter | Contoh | Keterangan |
|---|---|---|
| `search` | `developer` | Mencari nama, headline, atau bio |
| `city` | `Tasikmalaya` | Filter kota |
| `skill` | `Laravel` | Filter berdasarkan skill |
| `page` | `2` | Pagination |

Contoh:

```text
GET /api/job-seekers?search=developer&city=Tasikmalaya&skill=Laravel
```

Pagination menggunakan 12 profil per halaman.

## 9.2 Detail profile publik

```http
GET /api/job-seekers/{jobSeeker}
```

Data yang dapat ditampilkan:

- nama;
- foto;
- headline;
- bio;
- kota;
- gender;
- portfolio;
- LinkedIn;
- skills;
- pendidikan;
- pengalaman kerja.

Data sensitif yang tidak ditampilkan pada public profile:

- nomor telepon;
- alamat lengkap;
- tanggal lahir;
- user ID internal;
- CV;
- password.

Profile private menghasilkan `404` pada endpoint publik.

---

# 10. Job Seeker API

Endpoint berikut membutuhkan:

```http
Authorization: Bearer TOKEN_PENCARI_KERJA
Accept: application/json
```

Role wajib:

```text
pencari_kerja
```

---

## 10.1 Profile sendiri

### Lihat profile

```http
GET /api/job-seeker/profile
```

### Buat / update profile

```http
PUT /api/job-seeker/profile
```

Gunakan `multipart/form-data` jika mengupload foto atau CV.

### Field profile

| Field | Tipe | Keterangan |
|---|---|---|
| `full_name` | string | Wajib |
| `photo` | file | JPG/JPEG/PNG/WEBP, maksimal 5 MB |
| `cv` | file | PDF/DOC/DOCX, maksimal 5 MB |
| `headline` | string | Headline profesional |
| `bio` | string | Deskripsi diri |
| `phone` | string | Nomor telepon |
| `city` | string | Kota |
| `address` | string | Alamat |
| `birth_date` | date | Tanggal lahir |
| `gender` | string | Jenis kelamin |
| `portfolio_url` | URL | Portfolio |
| `linkedin_url` | URL | LinkedIn |
| `is_public` | boolean | Visibilitas profile |

Untuk `multipart/form-data`, `is_public` dapat dikirim sebagai:

```text
true
false
1
0
```

### Hapus profile

```http
DELETE /api/job-seeker/profile
```

---

## 10.2 CV sendiri

```http
GET /api/job-seeker/profile/cv
```

CV disimpan pada private storage dan diberikan melalui endpoint terautentikasi.

---

# 11. Job Seeker Skills

Authentication:

```text
Bearer TOKEN_PENCARI_KERJA
```

### Daftar

```http
GET /api/job-seeker/skills
```

### Tambah

```http
POST /api/job-seeker/skills
```

```json
{
  "skill_name": "Laravel",
  "level": "Intermediate"
}
```

### Update

```http
PUT /api/job-seeker/skills/{skill}
```

```json
{
  "skill_name": "Laravel",
  "level": "Advanced"
}
```

### Hapus

```http
DELETE /api/job-seeker/skills/{skill}
```

Satu profile tidak boleh memiliki nama skill yang sama lebih dari satu kali.

---

# 12. Job Seeker Education

Authentication:

```text
Bearer TOKEN_PENCARI_KERJA
```

### Daftar

```http
GET /api/job-seeker/educations
```

### Tambah

```http
POST /api/job-seeker/educations
```

```json
{
  "institution": "Universitas Contoh",
  "degree": "S1",
  "field_of_study": "Sistem Informasi",
  "start_date": "2022-09-01",
  "end_date": "2026-07-30",
  "is_current": false,
  "description": "Program studi Sistem Informasi."
}
```

Jika:

```text
is_current = true
```

backend menyimpan:

```text
end_date = null
```

### Update

```http
PUT /api/job-seeker/educations/{education}
```

### Hapus

```http
DELETE /api/job-seeker/educations/{education}
```

`end_date` tidak boleh lebih awal daripada `start_date`.

---

# 13. Job Seeker Experience

Authentication:

```text
Bearer TOKEN_PENCARI_KERJA
```

### Daftar

```http
GET /api/job-seeker/experiences
```

### Tambah

```http
POST /api/job-seeker/experiences
```

```json
{
  "company_name": "PT Maju Jaya",
  "position": "Backend Developer",
  "employment_type": "Full-time",
  "location": "Tasikmalaya",
  "start_date": "2025-01-01",
  "end_date": "2026-08-31",
  "is_current": false,
  "description": "Mengembangkan REST API menggunakan Laravel."
}
```

Jika:

```text
is_current = true
```

backend menyimpan:

```text
end_date = null
```

### Update

```http
PUT /api/job-seeker/experiences/{experience}
```

### Hapus

```http
DELETE /api/job-seeker/experiences/{experience}
```

`end_date` tidak boleh lebih awal daripada `start_date`.

---

# 14. Company API

Semua endpoint company membutuhkan:

```http
Authorization: Bearer TOKEN_PERUSAHAAN
Accept: application/json
```

Role wajib:

```text
perusahaan
```

Perusahaan dengan status `pending` tidak dapat menjalankan operasi yang membutuhkan perusahaan terverifikasi.

Status Company yang digunakan:

```text
pending
approved
rejected
suspended
```

---

## 14.1 Company Dashboard

```http
GET /api/company/dashboard
```

---

## 14.2 Company Profile

### Lihat profile

```http
GET /api/company/profile
```

### Update profile

```http
PUT /api/company/profile
```

Perubahan data profile perusahaan dapat menyebabkan perusahaan perlu diverifikasi kembali sesuai aturan backend.

### Ganti password

```http
PATCH /api/company/password
```

### Hapus akun

```http
DELETE /api/company/account
```

---

# 15. Company Jobs API

## 15.1 Daftar lowongan milik perusahaan

```http
GET /api/company/jobs
```

Pagination:

```text
10 data per halaman
```

## 15.2 Detail lowongan

```http
GET /api/company/jobs/{job}
```

## 15.3 Buat lowongan

```http
POST /api/company/jobs
```

Gunakan `multipart/form-data` bila mengupload poster.

### Field

| Field | Tipe | Keterangan |
|---|---|---|
| `title` | string | Wajib |
| `poster` | file | JPG/JPEG/PNG/WEBP, maksimal 5 MB |
| `location` | string | Wajib |
| `description` | string | Wajib |
| `expires_at` | date | Opsional |

Lowongan baru dimulai sebagai:

```text
status = draft
```

## 15.4 Update lowongan

```http
PUT /api/company/jobs/{job}
```

Ketentuan utama:

- lowongan `pending` atau `expired` tidak dapat diedit;
- lowongan `approved` atau `rejected` yang diubah dapat kembali menjadi `draft` untuk diproses ulang sesuai aturan backend.

## 15.5 Submit lowongan

```http
POST /api/company/jobs/{job}/submit
```

Alur status:

```text
draft
  ↓
pending
  ├── approved
  └── rejected
```

Lowongan harus melalui verifikasi Admin sebelum tampil pada public API.

## 15.6 Hapus lowongan

```http
DELETE /api/company/jobs/{job}
```

Lowongan yang sedang `pending` tidak dapat dihapus.

---

# 16. Company — Melihat Pencari Kerja

Perusahaan yang sudah `approved` dapat melihat profile pencari kerja jika:

```text
is_public = true
```

## 16.1 Detail pencari kerja

```http
GET /api/company/job-seekers/{jobSeeker}
```

Data yang diberikan kepada perusahaan mencakup informasi profesional dan kontak yang disediakan backend, termasuk ketersediaan CV.

Profile private menghasilkan:

```text
404
```

Perusahaan `pending` tidak memiliki akses.

## 16.2 Download CV

```http
GET /api/company/job-seekers/{jobSeeker}/cv
```

CV tidak diberikan sebagai public storage URL. File diambil melalui backend dan hanya dapat diakses perusahaan yang memenuhi syarat.

---

# 17. Company Notifications

Authentication:

```text
Bearer TOKEN_PERUSAHAAN
```

## Semua notification

```http
GET /api/company/notifications
```

Response menyediakan:

```text
unread_count
pagination
data
```

## Unread count

```http
GET /api/company/notifications/unread-count
```

Contoh:

```json
{
  "success": true,
  "unread_count": 2
}
```

## Tandai satu sebagai dibaca

```http
PATCH /api/company/notifications/{notification}/read
```

## Tandai semua sebagai dibaca

```http
PATCH /api/company/notifications/read-all
```

Notification perusahaan digunakan antara lain untuk:

- lowongan disetujui;
- lowongan ditolak;
- alasan penolakan lowongan;
- informasi proses verifikasi sesuai implementasi backend.

---

# 18. Admin API

Semua endpoint Admin membutuhkan:

```http
Authorization: Bearer TOKEN_ADMIN
Accept: application/json
```

Role wajib:

```text
admin
```

---

# 19. Admin Dashboard

```http
GET /api/admin/dashboard
```

Dashboard menyediakan antara lain:

- statistik perusahaan;
- statistik lowongan;
- statistik pencari kerja;
- statistik content;
- pending companies;
- pending jobs;
- latest news;
- jumlah notification yang belum dibaca.

Contoh struktur utama:

```json
{
  "success": true,
  "message": "Data dashboard admin berhasil diambil.",
  "data": {
    "companies": {},
    "jobs": {},
    "job_seekers": {},
    "content": {},
    "notifications": {
      "unread": 0
    },
    "pending_companies": [],
    "pending_jobs": [],
    "latest_news": []
  }
}
```

---

# 20. Admin Management

## 20.1 Admin users

```http
GET    /api/admin/admins
POST   /api/admin/admins
GET    /api/admin/admins/{user}
PUT    /api/admin/admins/{user}
DELETE /api/admin/admins/{user}
```

Public registration tidak digunakan untuk membuat Admin.

---

# 21. Admin Company Management

## Daftar perusahaan

```http
GET /api/admin/companies
```

## Perusahaan pending

```http
GET /api/admin/companies/pending
```

## Detail

```http
GET /api/admin/companies/{company}
```

## Approve

```http
PATCH /api/admin/companies/{company}/approve
```

## Reject

```http
PATCH /api/admin/companies/{company}/reject
```

Request:

```json
{
  "rejection_reason": "Data perusahaan belum lengkap untuk proses verifikasi."
}
```

## Suspend

```http
PATCH /api/admin/companies/{company}/suspend
```

Status perusahaan:

```text
pending
approved
rejected
suspended
```

---

# 22. Admin Job Management

## Semua lowongan

```http
GET /api/admin/jobs
```

## Lowongan pending

```http
GET /api/admin/jobs/pending
```

## Approve

```http
PATCH /api/admin/jobs/{job}/approve
```

## Reject

```http
PATCH /api/admin/jobs/{job}/reject
```

Request:

```json
{
  "rejection_reason": "Informasi lowongan belum lengkap."
}
```

## Delete

```http
DELETE /api/admin/jobs/{job}
```

Setelah lowongan di-approve, lowongan dapat tampil pada public API jika juga memenuhi kondisi publik lainnya, misalnya belum expired.

---

# 23. Admin Job Seeker Management

Endpoint ini digunakan Admin untuk management data pencari kerja dari sisi Admin:

```http
GET    /api/admin/job-seekers
POST   /api/admin/job-seekers
GET    /api/admin/job-seekers/{jobSeeker}
PUT    /api/admin/job-seekers/{jobSeeker}
DELETE /api/admin/job-seekers/{jobSeeker}
PATCH  /api/admin/job-seekers/{jobSeeker}/verify
PATCH  /api/admin/job-seekers/{jobSeeker}/reject
```

> Endpoint Admin Job Seeker Management berbeda dari `/api/job-seeker/...` yang digunakan pencari kerja untuk mengelola profile miliknya sendiri.

---

# 24. Admin Notifications

Authentication:

```text
Bearer TOKEN_ADMIN
```

## Semua notification

```http
GET /api/admin/notifications
```

## Unread count

```http
GET /api/admin/notifications/unread-count
```

## Tandai satu sebagai dibaca

```http
PATCH /api/admin/notifications/{notification}/read
```

## Tandai semuanya sebagai dibaca

```http
PATCH /api/admin/notifications/read-all
```

Admin mendapatkan notification untuk aktivitas yang memerlukan perhatian, antara lain:

- perusahaan baru mendaftar;
- lowongan baru dikirim untuk verifikasi.

---

# 25. Admin CRUD Content

Semua endpoint berikut membutuhkan authentication Admin.

## 25.1 Announcements

```http
GET    /api/admin/announcements
POST   /api/admin/announcements
GET    /api/admin/announcements/{announcement}
POST   /api/admin/announcements/{announcement}
DELETE /api/admin/announcements/{announcement}
```

## 25.2 News

```http
GET    /api/admin/news
POST   /api/admin/news
GET    /api/admin/news/{news}
POST   /api/admin/news/{news}
DELETE /api/admin/news/{news}
```

## 25.3 Trainings

```http
GET    /api/admin/trainings
POST   /api/admin/trainings
GET    /api/admin/trainings/{training}
POST   /api/admin/trainings/{training}
DELETE /api/admin/trainings/{training}
```

## 25.4 Pages

```http
GET    /api/admin/pages
POST   /api/admin/pages
GET    /api/admin/pages/{page}
POST   /api/admin/pages/{page}
DELETE /api/admin/pages/{page}
```

## 25.5 Services

```http
GET    /api/admin/services
POST   /api/admin/services
GET    /api/admin/services/{service}
POST   /api/admin/services/{service}
DELETE /api/admin/services/{service}
```

> **Catatan:** berdasarkan route backend saat ini, operasi update pada resource Admin content menggunakan `POST`, bukan `PUT`.

---

# 26. Response Convention

Sebagian besar endpoint custom menggunakan struktur:

```json
{
  "success": true,
  "message": "...",
  "data": {}
}
```

Untuk Laravel Resource Collection, response dapat memiliki:

```json
{
  "data": [],
  "links": {},
  "meta": {}
}
```

## Validation error

Validation menggunakan HTTP `422`.

Contoh:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email has already been taken."
    ]
  }
}
```

## Status HTTP yang umum

| Status | Makna |
|---:|---|
| `200` | Request berhasil |
| `201` | Resource berhasil dibuat |
| `401` | Belum terautentikasi |
| `403` | Tidak memiliki izin |
| `404` | Resource tidak ditemukan / tidak tersedia |
| `422` | Validation atau business rule gagal |

> Response error detail dapat berbeda sesuai middleware, controller, dan validation yang menangani request.

---

# 27. File Upload

## 27.1 Public files

File publik menggunakan:

```text
storage/app/public
```

Jenis file publik yang digunakan backend antara lain:

- foto pencari kerja;
- poster lowongan;
- gambar news;
- gambar pages;
- gambar training.

Akses public storage menggunakan:

```text
/storage/...
```

Pastikan symbolic link dibuat:

```powershell
php artisan storage:link
```

## 27.2 Private files

CV pencari kerja menggunakan private storage:

```text
storage/app/private/job-seekers/cv
```

CV **tidak boleh** diakses melalui direct public storage URL.

Akses dilakukan melalui endpoint:

```http
GET /api/job-seeker/profile/cv
GET /api/company/job-seekers/{jobSeeker}/cv
```

---

# 28. CORS Development

Frontend development saat ini diizinkan dari:

```text
http://localhost:5173
http://127.0.0.1:5173
```

Backend:

```text
http://localhost:8000
```

Frontend dapat menggunakan:

```env
VITE_API_URL=http://localhost:8000/api
```

Contoh public request:

```javascript
const response = await fetch(
    `${import.meta.env.VITE_API_URL}/jobs`
);

const result = await response.json();
```

Contoh protected request:

```javascript
const response = await fetch(
    `${import.meta.env.VITE_API_URL}/company/jobs`,
    {
        headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
        },
    }
);
```

---

# 29. Flow Bisnis Utama

## 29.1 Company

```text
Register
   ↓
Company = pending
   ↓
Admin approve / reject
   ↓
approved
   ↓
Create job
   ↓
draft
   ↓
Submit job
   ↓
pending
   ↓
Admin approve / reject
   ├── approved → public
   └── rejected → notification + rejection_reason
```

## 29.2 Job Seeker

```text
Register
   ↓
Login
   ↓
Profile dibuat otomatis
   ↓
Lengkapi profile
   ↓
Skills
   ↓
Education
   ↓
Experience
   ↓
Upload CV
   ↓
Atur is_public
   ↓
Jika is_public = true
profile dapat ditemukan pada public directory
```

## 29.3 Public

```text
Pengunjung
   ├── Lihat lowongan
   ├── Lihat news
   ├── Lihat training
   ├── Lihat announcements
   ├── Lihat pages
   ├── Lihat services
   ├── Lihat employment statistics
   └── Lihat profile pencari kerja yang public
```

---

# 30. Aturan Akses Penting

| Fitur | Public | Pencari Kerja | Perusahaan | Admin |
|---|:---:|:---:|:---:|:---:|
| Lihat lowongan publik | ✅ | ✅ | ✅ | ✅ |
| Registrasi | ✅ | - | - | - |
| Profile pribadi pencari kerja | - | ✅ | ❌ | Endpoint Admin |
| Skills sendiri | - | ✅ | ❌ | Endpoint Admin |
| Education sendiri | - | ✅ | ❌ | Endpoint Admin |
| Experience sendiri | - | ✅ | ❌ | Endpoint Admin |
| Company profile | - | ❌ | ✅ | ✅ |
| Company jobs | - | ❌ | ✅ | ✅ |
| Approval company | ❌ | ❌ | ❌ | ✅ |
| Approval job | ❌ | ❌ | ❌ | ✅ |
| Lihat public job seeker | ✅ | ✅ | ✅* | ✅ |
| Download CV | ❌ | Pemilik | ✅* | sesuai endpoint Admin |
| Notifications | ❌ | sesuai endpoint yang tersedia | ✅ | ✅ |

`*` Perusahaan harus memenuhi syarat access, terutama status `approved`, dan profile pencari kerja harus `is_public = true`.

---

# 31. Cara Menjalankan Project

## 31.1 Clone / project setup

Setelah source code tersedia:

```powershell
composer install
```

## 31.2 Environment

Buat `.env` berdasarkan `.env.example`.

Contoh development:

```env
APP_URL=http://localhost:8000
FILESYSTEM_DISK=local
```

Sesuaikan konfigurasi database dengan MySQL lokal.

## 31.3 Generate application key

Jika project baru dan `APP_KEY` belum tersedia:

```powershell
php artisan key:generate
```

Jangan memasukkan nilai `APP_KEY` ke repository atau dokumentasi publik.

## 31.4 Database

Untuk menjalankan migration biasa:

```powershell
php artisan migrate
```

Untuk membangun database development/testing dari nol:

```powershell
php artisan migrate:fresh --seed
```

> `migrate:fresh --seed` menghapus tabel pada database/connection aktif. Gunakan hanya pada database development/testing yang memang boleh di-reset.

## 31.5 Storage

```powershell
php artisan storage:link
```

## 31.6 Bersihkan cache

```powershell
php artisan optimize:clear
```

## 31.7 Jalankan server

```powershell
php artisan serve
```

Backend:

```text
http://localhost:8000
```

---

# 32. Testing

Jalankan seluruh test:

```powershell
php artisan test
```

Checkpoint backend saat README ini diperbarui:

```text
Tests:    122 passed (400 assertions)
```

Test suite mencakup antara lain:

- Authentication;
- Registration;
- Role access;
- Company;
- Company registration;
- Company jobs;
- Admin dashboard;
- Admin verification;
- Notifications;
- Job seeker profile;
- Skills;
- Education;
- Experience;
- Public job seeker API;
- Company access to job seeker;
- API contract;
- End-to-end job flow;
- Public content API.

Database juga telah berhasil diuji dari kondisi kosong menggunakan:

```powershell
php artisan migrate:fresh --seed
```

dan seluruh migration selesai tanpa conflict.

---

# 33. Route Debug Endpoint

Backend masih memiliki endpoint development:

```http
GET /api/test
```

Endpoint ini digunakan untuk debugging/development dan sebaiknya dipertimbangkan untuk dihapus sebelum deployment production.

---

# 34. Frontend Integration Checklist

Sebelum integrasi frontend:

- [ ] Backend berjalan pada port `8000`
- [ ] Frontend berjalan pada port `5173`
- [ ] Set `VITE_API_URL=http://localhost:8000/api`
- [ ] Pastikan CORS mengizinkan origin frontend
- [ ] Implement register dengan pilihan `pencari_kerja` / `perusahaan`
- [ ] Implement login
- [ ] Simpan Bearer Token setelah login
- [ ] Implement routing/guard berdasarkan role
- [ ] Implement public jobs
- [ ] Implement public job seeker directory
- [ ] Implement public news
- [ ] Implement public training
- [ ] Implement public announcements
- [ ] Implement public pages
- [ ] Implement public services
- [ ] Implement employment statistics
- [ ] Implement company dashboard
- [ ] Implement company profile
- [ ] Implement company jobs
- [ ] Implement company notifications
- [ ] Implement company job seeker access
- [ ] Implement admin dashboard
- [ ] Implement admin company verification
- [ ] Implement admin job verification
- [ ] Implement admin notifications
- [ ] Implement admin content management
- [ ] Implement job seeker profile
- [ ] Implement skills
- [ ] Implement education
- [ ] Implement experience
- [ ] Implement CV upload/download
- [ ] Pastikan CV tidak diperlakukan sebagai public storage URL

---

# 35. Ringkasan Endpoint

## Public

```text
GET /api/jobs
GET /api/jobs/{job}

GET /api/news
GET /api/news/{news}

GET /api/trainings
GET /api/trainings/{training}

GET /api/announcements
GET /api/announcements/{announcement}

GET /api/pages
GET /api/pages/{slug}

GET /api/services
GET /api/services/{slug}

GET /api/employment-statistics

GET /api/job-seekers
GET /api/job-seekers/{jobSeeker}
```

## Authentication

```text
POST /api/register
POST /api/login
POST /api/logout
GET  /api/me

POST /api/company/register
```

`POST /api/company/register` adalah endpoint legacy dan dipertahankan untuk kompatibilitas. Frontend baru disarankan menggunakan `POST /api/register`.

## Job Seeker

```text
GET    /api/job-seeker/profile
PUT    /api/job-seeker/profile
DELETE /api/job-seeker/profile
GET    /api/job-seeker/profile/cv

GET    /api/job-seeker/skills
POST   /api/job-seeker/skills
PUT    /api/job-seeker/skills/{skill}
DELETE /api/job-seeker/skills/{skill}

GET    /api/job-seeker/educations
POST   /api/job-seeker/educations
PUT    /api/job-seeker/educations/{education}
DELETE /api/job-seeker/educations/{education}

GET    /api/job-seeker/experiences
POST   /api/job-seeker/experiences
PUT    /api/job-seeker/experiences/{experience}
DELETE /api/job-seeker/experiences/{experience}
```

## Company

```text
GET    /api/company/dashboard
GET    /api/company/profile
PUT    /api/company/profile
PATCH  /api/company/password
DELETE /api/company/account

GET    /api/company/jobs
POST   /api/company/jobs
GET    /api/company/jobs/{job}
PUT    /api/company/jobs/{job}
DELETE /api/company/jobs/{job}
POST   /api/company/jobs/{job}/submit

GET    /api/company/job-seekers/{jobSeeker}
GET    /api/company/job-seekers/{jobSeeker}/cv

GET    /api/company/notifications
GET    /api/company/notifications/unread-count
PATCH  /api/company/notifications/{notification}/read
PATCH  /api/company/notifications/read-all
```

## Admin

```text
GET    /api/admin/dashboard

GET    /api/admin/admins
POST   /api/admin/admins
GET    /api/admin/admins/{user}
PUT    /api/admin/admins/{user}
DELETE /api/admin/admins/{user}

GET    /api/admin/companies
GET    /api/admin/companies/pending
GET    /api/admin/companies/{company}
PATCH  /api/admin/companies/{company}/approve
PATCH  /api/admin/companies/{company}/reject
PATCH  /api/admin/companies/{company}/suspend

GET    /api/admin/jobs
GET    /api/admin/jobs/pending
PATCH  /api/admin/jobs/{job}/approve
PATCH  /api/admin/jobs/{job}/reject
DELETE /api/admin/jobs/{job}

GET    /api/admin/job-seekers
POST   /api/admin/job-seekers
GET    /api/admin/job-seekers/{jobSeeker}
PUT    /api/admin/job-seekers/{jobSeeker}
DELETE /api/admin/job-seekers/{jobSeeker}
PATCH  /api/admin/job-seekers/{jobSeeker}/verify
PATCH  /api/admin/job-seekers/{jobSeeker}/reject

GET    /api/admin/notifications
GET    /api/admin/notifications/unread-count
PATCH  /api/admin/notifications/{notification}/read
PATCH  /api/admin/notifications/read-all

GET    /api/admin/news
POST   /api/admin/news
GET    /api/admin/news/{news}
POST   /api/admin/news/{news}
DELETE /api/admin/news/{news}

GET    /api/admin/announcements
POST   /api/admin/announcements
GET    /api/admin/announcements/{announcement}
POST   /api/admin/announcements/{announcement}
DELETE /api/admin/announcements/{announcement}

GET    /api/admin/trainings
POST   /api/admin/trainings
GET    /api/admin/trainings/{training}
POST   /api/admin/trainings/{training}
DELETE /api/admin/trainings/{training}

GET    /api/admin/pages
POST   /api/admin/pages
GET    /api/admin/pages/{page}
POST   /api/admin/pages/{page}
DELETE /api/admin/pages/{page}

GET    /api/admin/services
POST   /api/admin/services
GET    /api/admin/services/{service}
POST   /api/admin/services/{service}
DELETE /api/admin/services/{service}
```

---

# 36. Security Rules

1. Jangan commit `.env`.
2. Jangan mengirim `APP_KEY` melalui chat, README, Git, atau Postman collection.
3. Gunakan Bearer Token untuk endpoint protected.
4. Public registration hanya mengizinkan role `pencari_kerja` dan `perusahaan`.
5. Perusahaan harus `approved` untuk operasi yang membutuhkan verifikasi.
6. Profil pencari kerja harus `is_public = true` agar dapat ditemukan publik.
7. CV disimpan pada private storage.
8. Perusahaan tidak boleh mengakses profile private.
9. User tidak boleh mengubah data milik user lain.
10. Endpoint Admin harus menggunakan role `admin`.
11. Endpoint Company harus menggunakan role `perusahaan`.
12. Endpoint Job Seeker pribadi harus menggunakan role `pencari_kerja`.
13. Ownership check tetap berlaku meskipun token valid.
14. Jangan mengandalkan frontend saja untuk membatasi role atau permission; backend tetap menjadi sumber aturan akses.

---

# 37. Status Backend

Checkpoint saat README ini diperbarui:

```text
Authentication             ✅
Public registration        ✅
Role & middleware          ✅
Company registration       ✅
Company management         ✅
Company jobs               ✅
Admin verification         ✅
Job rejection              ✅
Company rejection          ✅
Rejection reason           ✅
Public jobs                ✅
Public content             ✅
Job seeker profile         ✅
Job seeker skills          ✅
Job seeker education       ✅
Job seeker experience      ✅
Job seeker CV              ✅
Public job seeker          ✅
Company → Job seeker       ✅
Notifications              ✅
Admin dashboard            ✅
API contract tests         ✅
Role/ownership tests       ✅
CORS development           ✅
Private CV storage         ✅
Fresh migration + seeder   ✅
Full test suite            ✅
```

## Verification checkpoint

```text
php artisan migrate:fresh --seed
```

berhasil dijalankan pada database development.

```text
php artisan test
```

hasil:

```text
122 passed
400 assertions
```

---

# 38. Catatan untuk Frontend Developer

Urutan integrasi yang disarankan:

```text
1. Public API
   ↓
2. Register + Login
   ↓
3. Role guard
   ↓
4. Job Seeker
   ↓
5. Company
   ↓
6. Admin
   ↓
7. Notifications
   ↓
8. File upload/download
   ↓
9. Final integration testing
```

Untuk masalah integrasi, gunakan source of truth berikut:

```powershell
php artisan route:list --path=api
```

dan dokumentasi ini untuk method, role, authorization, serta alur bisnis.
