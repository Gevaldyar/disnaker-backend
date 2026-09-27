# DISNAKER API

Dokumentasi API backend untuk aplikasi DISNAKER.

Dokumen ini ditujukan untuk frontend developer yang mengintegrasikan web frontend dengan Laravel API.

---

## 1. Teknologi

- Laravel
- Laravel Sanctum
- MySQL
- REST API
- Storage filesystem Laravel
- Database Notifications

---

## 2. Base URL Development

```text
http://localhost:8000/api
```

Contoh:

```http
GET http://localhost:8000/api/jobs
```

Jika frontend dan backend berada di komputer yang berbeda, ganti `localhost` dengan IP komputer yang menjalankan backend.

---

## 3. Role Pengguna

Backend menggunakan tiga role utama:

| Role | Keterangan |
|---|---|
| `admin` | Admin/pegawai Disnaker yang mengelola data dan melakukan verifikasi |
| `perusahaan` | Perusahaan yang mengelola profil dan lowongan |
| `pencari_kerja` | Pencari kerja yang mengelola profil profesional |

Pengunjung umum dapat mengakses endpoint publik tanpa login.

---

## 4. Authentication

API menggunakan Laravel Sanctum dengan Bearer Token.

Setelah login, simpan token dari response login lalu kirim pada endpoint yang membutuhkan autentikasi:

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

---

# 5. Authentication API

## 5.1 Login

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

### Response berhasil

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

Gunakan token pada header:

```http
Authorization: Bearer TOKEN
```

## 5.2 Current User

```http
GET /api/me
```

Authentication: `auth:sanctum`

## 5.3 Logout

```http
POST /api/logout
```

Authentication: `auth:sanctum`

---

# 6. Company Registration

## 6.1 Registrasi perusahaan

```http
POST /api/company/register
```

Tidak membutuhkan login.

### Request

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

Setelah berhasil, akun perusahaan dibuat dengan status perusahaan:

```text
pending
```

Perusahaan belum dapat membuat lowongan sampai disetujui Admin.

---

# 7. Public API

Endpoint pada bagian ini tidak membutuhkan authentication.

## 7.1 Lowongan

### Daftar lowongan

```http
GET /api/jobs
```

### Detail lowongan

```http
GET /api/jobs/{job}
```

Lowongan yang belum disetujui / tidak memenuhi kondisi publik tidak ditampilkan pada public API.

---

## 7.2 News

```http
GET /api/news
GET /api/news/{news}
```

---

## 7.3 Training

```http
GET /api/trainings
GET /api/trainings/{training}
```

---

## 7.4 Announcement

```http
GET /api/announcements
GET /api/announcements/{announcement}
```

---

## 7.5 Pages

```http
GET /api/pages
GET /api/pages/{slug}
```

---

## 7.6 Services

```http
GET /api/services
GET /api/services/{slug}
```

---

## 7.7 Employment Statistics

```http
GET /api/employment-statistics
```

---

# 8. Public Job Seeker API

Profil pencari kerja bersifat publik hanya jika:

```text
is_public = true
```

## 8.1 Daftar pencari kerja

```http
GET /api/job-seekers
```

### Query parameter

| Parameter | Contoh | Keterangan |
|---|---|---|
| `search` | `developer` | Mencari nama, headline, atau bio |
| `city` | `Tasikmalaya` | Filter kota |
| `skill` | `Laravel` | Filter berdasarkan skill |
| `page` | `2` | Halaman pagination |

Contoh:

```text
GET /api/job-seekers?search=developer&city=Tasikmalaya&skill=Laravel
```

Pagination menggunakan 12 profil per halaman.

## 8.2 Detail profil publik

```http
GET /api/job-seekers/{jobSeeker}
```

### Data yang dapat ditampilkan

- Nama
- Foto
- Headline
- Bio
- Kota
- Gender
- Portfolio
- LinkedIn
- Skills
- Pendidikan
- Pengalaman kerja

### Data yang tidak ditampilkan pada public profile

- Nomor telepon
- Alamat lengkap
- Tanggal lahir
- User ID internal
- CV
- Password

Profil private akan menghasilkan `404` pada endpoint publik.

---

# 9. Job Seeker API

Endpoint berikut membutuhkan:

```http
Authorization: Bearer TOKEN_PENCARI_KERJA
Accept: application/json
```

Role wajib:

```text
pencari_kerja
```

## 9.1 Profile sendiri

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

Untuk `multipart/form-data`, `is_public` dapat dikirim sebagai `true`, `false`, `1`, atau `0`.

### Hapus profile

```http
DELETE /api/job-seeker/profile
```

## 9.2 CV sendiri

```http
GET /api/job-seeker/profile/cv
```

CV disimpan pada private storage dan diberikan melalui endpoint terautentikasi.

---

# 10. Job Seeker Skills

Authentication:

```text
Bearer TOKEN_PENCARI_KERJA
```

## Daftar skill

```http
GET /api/job-seeker/skills
```

## Tambah skill

```http
POST /api/job-seeker/skills
```

```json
{
  "skill_name": "Laravel",
  "level": "Intermediate"
}
```

## Update skill

```http
PUT /api/job-seeker/skills/{skill}
```

```json
{
  "skill_name": "Laravel",
  "level": "Advanced"
}
```

## Hapus skill

```http
DELETE /api/job-seeker/skills/{skill}
```

Satu profile tidak boleh memiliki nama skill yang sama lebih dari sekali.

---

# 11. Job Seeker Education

Authentication:

```text
Bearer TOKEN_PENCARI_KERJA
```

## Daftar pendidikan

```http
GET /api/job-seeker/educations
```

## Tambah pendidikan

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

backend akan menyimpan:

```text
end_date = null
```

## Update pendidikan

```http
PUT /api/job-seeker/educations/{education}
```

## Hapus pendidikan

```http
DELETE /api/job-seeker/educations/{education}
```

`end_date` tidak boleh lebih awal daripada `start_date`.

---

# 12. Job Seeker Experience

Authentication:

```text
Bearer TOKEN_PENCARI_KERJA
```

## Daftar pengalaman

```http
GET /api/job-seeker/experiences
```

## Tambah pengalaman

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

backend akan menyimpan:

```text
end_date = null
```

## Update pengalaman

```http
PUT /api/job-seeker/experiences/{experience}
```

## Hapus pengalaman

```http
DELETE /api/job-seeker/experiences/{experience}
```

`end_date` tidak boleh lebih awal daripada `start_date`.

---

# 13. Company API

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

---

## 13.1 Company Dashboard

```http
GET /api/company/dashboard
```

---

## 13.2 Company Profile

### Lihat profile

```http
GET /api/company/profile
```

### Update profile

```http
PUT /api/company/profile
```

Perubahan data profile perusahaan dapat menyebabkan status perlu diverifikasi kembali sesuai aturan backend.

### Ganti password

```http
PATCH /api/company/password
```

### Hapus akun

```http
DELETE /api/company/account
```

---

# 14. Company Jobs API

## Daftar lowongan milik perusahaan

```http
GET /api/company/jobs
```

Pagination: 10 data per halaman.

## Detail lowongan

```http
GET /api/company/jobs/{job}
```

## Buat lowongan

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

## Update lowongan

```http
PUT /api/company/jobs/{job}
```

Lowongan berstatus `pending` atau `expired` tidak dapat diedit.

Jika lowongan `approved` atau `rejected` diubah, statusnya dapat kembali menjadi `draft` untuk diproses ulang.

## Submit lowongan

```http
POST /api/company/jobs/{job}/submit
```

Alur status:

```text
Draft → Pending → Approved
             └→ Rejected
```

Lowongan harus melalui verifikasi Admin sebelum tampil di public API.

## Hapus lowongan

```http
DELETE /api/company/jobs/{job}
```

Lowongan yang sedang `pending` tidak dapat dihapus.

---

# 15. Company — Lihat Pencari Kerja

Perusahaan yang sudah `approved` dapat melihat profile pencari kerja yang `is_public = true`.

## Detail pencari kerja

```http
GET /api/company/job-seekers/{jobSeeker}
```

Data yang diberikan kepada perusahaan mencakup informasi profesional yang diperlukan untuk melihat kandidat, termasuk kontak dan ketersediaan CV.

Profil private menghasilkan `404`.

Perusahaan `pending` tidak memiliki akses.

## Download CV

```http
GET /api/company/job-seekers/{jobSeeker}/cv
```

CV tidak diberikan sebagai public storage URL. File diambil melalui backend dan hanya dapat diakses perusahaan yang memenuhi syarat akses.

---

# 16. Company Notifications

Authentication:

```text
Bearer TOKEN_PERUSAHAAN
```

## Semua notifikasi

```http
GET /api/company/notifications
```

Response menyediakan `unread_count` dan data pagination.

## Jumlah unread

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

## Tandai satu notification sebagai dibaca

```http
PATCH /api/company/notifications/{notification}/read
```

## Tandai semua sebagai dibaca

```http
PATCH /api/company/notifications/read-all
```

Notification perusahaan digunakan antara lain untuk:

- Lowongan disetujui
- Lowongan ditolak
- Alasan penolakan lowongan

---

# 17. Admin API

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

## 17.1 Admin Dashboard

```http
GET /api/admin/dashboard
```

Dashboard menyediakan statistik perusahaan, lowongan, pencari kerja, konten, daftar pending, berita terbaru, dan jumlah notification belum dibaca.

Struktur utama:

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

# 18. Admin Company Management

## Semua perusahaan

```http
GET /api/admin/companies
```

## Perusahaan pending

```http
GET /api/admin/companies/pending
```

## Detail perusahaan

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

## Suspend

```http
PATCH /api/admin/companies/{company}/suspend
```

---

# 19. Admin Job Management

## Semua lowongan

```http
GET /api/admin/jobs
```

## Lowongan pending

```http
GET /api/admin/jobs/pending
```

## Approve lowongan

```http
PATCH /api/admin/jobs/{job}/approve
```

Setelah approved, lowongan dapat dipublikasikan sesuai aturan backend dan perusahaan mendapat notification.

## Reject lowongan

```http
PATCH /api/admin/jobs/{job}/reject
```

Request:

```json
{
  "rejection_reason": "Informasi lowongan belum lengkap."
}
```

Setelah rejected, perusahaan mendapat notification yang berisi alasan penolakan.

## Delete

```http
DELETE /api/admin/jobs/{job}
```

---

# 20. Admin Job Seeker Management

Endpoint ini merupakan management data pencari kerja dari sisi Admin.

```http
GET    /api/admin/job-seekers
POST   /api/admin/job-seekers
GET    /api/admin/job-seekers/{jobSeeker}
PUT    /api/admin/job-seekers/{jobSeeker}
DELETE /api/admin/job-seekers/{jobSeeker}
PATCH  /api/admin/job-seekers/{jobSeeker}/verify
PATCH  /api/admin/job-seekers/{jobSeeker}/reject
```

Endpoint ini berbeda dari:

```text
/api/job-seeker/...
```

yang digunakan oleh pencari kerja untuk mengelola profile miliknya sendiri.

---

# 21. Admin Notifications

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

- Perusahaan baru mendaftar
- Lowongan baru menunggu verifikasi

---

# 22. Admin CRUD Content

Endpoint Admin untuk pengelolaan konten menggunakan authentication Admin.

## Announcements

```http
GET    /api/admin/announcements
POST   /api/admin/announcements
GET    /api/admin/announcements/{announcement}
POST   /api/admin/announcements/{announcement}
DELETE /api/admin/announcements/{announcement}
```

## News

```http
GET    /api/admin/news
POST   /api/admin/news
GET    /api/admin/news/{news}
POST   /api/admin/news/{news}
DELETE /api/admin/news/{news}
```

## Trainings

```http
GET    /api/admin/trainings
POST   /api/admin/trainings
GET    /api/admin/trainings/{training}
POST   /api/admin/trainings/{training}
DELETE /api/admin/trainings/{training}
```

## Pages

```http
GET    /api/admin/pages
POST   /api/admin/pages
GET    /api/admin/pages/{page}
POST   /api/admin/pages/{page}
DELETE /api/admin/pages/{page}
```

## Services

```http
GET    /api/admin/services
POST   /api/admin/services
GET    /api/admin/services/{service}
POST   /api/admin/services/{service}
DELETE /api/admin/services/{service}
```

> Catatan: berdasarkan route aktual backend, operasi update pada beberapa resource Admin content menggunakan method `POST`, bukan `PUT`.

---

# 23. Admin Management

## Admin users

```http
GET    /api/admin/admins
POST   /api/admin/admins
GET    /api/admin/admins/{user}
PUT    /api/admin/admins/{user}
DELETE /api/admin/admins/{user}
```

---

# 24. Response Convention

Sebagian besar API menggunakan struktur:

```json
{
  "success": true,
  "message": "...",
  "data": {}
}
```

Untuk Resource Collection Laravel, response dapat memiliki:

```json
{
  "data": [],
  "links": {},
  "meta": {}
}
```

Validation error menggunakan HTTP `422` dengan pola Laravel:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "..."
    ]
  }
}
```

Contoh status umum:

| Status | Makna |
|---:|---|
| `200` | Request berhasil |
| `201` | Resource berhasil dibuat |
| `403` | Tidak memiliki izin |
| `404` | Resource tidak ditemukan / tidak tersedia |
| `422` | Validation atau business rule gagal |

---

# 25. File Upload

## Public files

File publik menggunakan disk:

```text
storage/app/public
```

dan dapat diakses melalui:

```text
/storage/...
```

Contoh jenis file publik yang digunakan backend:

- Foto pencari kerja
- Poster lowongan
- Gambar news
- Gambar pages
- Gambar training

## Private files

CV pencari kerja menggunakan private storage:

```text
storage/app/private/job-seekers/cv
```

CV tidak boleh diakses dengan direct public storage URL.

Akses dilakukan melalui endpoint terautentikasi:

```text
GET /api/job-seeker/profile/cv
GET /api/company/job-seekers/{jobSeeker}/cv
```

---

# 26. CORS Development

Frontend development yang digunakan saat ini diizinkan dari:

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

Contoh fetch:

```javascript
const response = await fetch(
    `${import.meta.env.VITE_API_URL}/jobs`
);

const result = await response.json();
```

Untuk endpoint protected:

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

# 27. Flow Bisnis Utama

## Company

```text
Register
   ↓
pending
   ↓
Admin approve company
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

## Job Seeker

```text
Register/Login
   ↓
Create profile
   ↓
Add skills
   ↓
Add education
   ↓
Add experience
   ↓
Upload CV
   ↓
Set is_public
   ↓
Profile dapat ditemukan jika is_public = true
```

## Public

```text
Pengunjung
   ├── Lihat lowongan
   ├── Lihat news
   ├── Lihat training
   ├── Lihat pages
   ├── Lihat services
   └── Lihat profile pencari kerja publik
```

---

# 28. Security Rules Penting

1. Jangan commit `.env`.
2. Jangan mengirim `APP_KEY` melalui chat, README, Git, atau Postman collection.
3. Gunakan Bearer Token untuk endpoint protected.
4. Perusahaan harus `approved` untuk operasi yang memerlukan verifikasi.
5. Profil pencari kerja harus `is_public = true` agar dapat dilihat publik/perusahaan.
6. CV disimpan pada private storage.
7. Perusahaan tidak boleh mengakses profile private.
8. User tidak boleh mengubah skill, education, atau experience milik user lain.
9. Admin-only endpoint harus menggunakan role `admin`.
10. Company-only endpoint harus menggunakan role `perusahaan`.
11. Job seeker-only endpoint harus menggunakan role `pencari_kerja`.

---

# 29. Menjalankan Project

## Install dependency

```powershell
composer install
```

Jika frontend juga membutuhkan Node dependency, jalankan perintah Node pada project frontend secara terpisah.

## Environment

Buat `.env` berdasarkan `.env.example`, lalu konfigurasi database.

Contoh development:

```env
APP_URL=http://localhost:8000
FILESYSTEM_DISK=local
```

## Database

```powershell
php artisan migrate
```

Jika ingin membuat database development dari awal menggunakan seeder:

```powershell
php artisan migrate:fresh --seed
```

> `migrate:fresh --seed` menghapus seluruh tabel database yang digunakan oleh connection aktif. Gunakan hanya pada database development/testing.

## Storage

```powershell
php artisan storage:link
```

## Jalankan server

```powershell
php artisan serve
```

Backend tersedia di:

```text
http://localhost:8000
```

---

# 30. Testing

Jalankan seluruh test:

```powershell
php artisan test
```

Backend saat dokumentasi ini dibuat telah memiliki test suite yang mencakup:

- Authentication
- Role access
- Company
- Company registration
- Company jobs
- Admin dashboard
- Admin verification
- Notifications
- Job seeker profile
- Skills
- Education
- Experience
- Public job seeker API
- Company access to job seeker
- API contract
- End-to-end job flow
- Public content API

Jika test gagal setelah perubahan kode, selesaikan test terlebih dahulu sebelum melakukan integrasi frontend.

---

# 31. Route Debug Endpoint

Backend saat ini masih memiliki endpoint development:

```http
GET /api/test
```

Endpoint ini sebaiknya digunakan hanya untuk debugging/development dan dipertimbangkan untuk dihapus sebelum deployment production.

---

# 32. Frontend Integration Checklist

Sebelum mulai integrasi frontend:

- [ ] Set `VITE_API_URL`
- [ ] Pastikan backend berjalan pada port `8000`
- [ ] Pastikan frontend berjalan pada port `5173`
- [ ] Pastikan CORS mengizinkan origin frontend
- [ ] Implement login dan penyimpanan Bearer Token
- [ ] Implement public jobs
- [ ] Implement public job seeker
- [ ] Implement company dashboard
- [ ] Implement company jobs
- [ ] Implement company notifications
- [ ] Implement admin dashboard
- [ ] Implement admin notifications
- [ ] Implement job seeker profile
- [ ] Implement skills
- [ ] Implement education
- [ ] Implement experience
- [ ] Implement CV upload/download

---

# 33. Ringkasan Endpoint

## Public

```text
GET  /api/jobs
GET  /api/jobs/{job}
GET  /api/news
GET  /api/news/{news}
GET  /api/trainings
GET  /api/trainings/{training}
GET  /api/announcements
GET  /api/announcements/{announcement}
GET  /api/pages
GET  /api/pages/{slug}
GET  /api/services
GET  /api/services/{slug}
GET  /api/employment-statistics
GET  /api/job-seekers
GET  /api/job-seekers/{jobSeeker}
```

## Authentication

```text
POST /api/login
POST /api/logout
GET  /api/me
POST /api/company/register
```

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
DELETE /api/admin/jobs/{job}
PATCH  /api/admin/jobs/{job}/approve
PATCH  /api/admin/jobs/{job}/reject

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

# 34. Status Backend

Checkpoint saat ini:

```text
Authentication             ✅
Role & middleware          ✅
Company registration       ✅
Company management         ✅
Company jobs               ✅
Admin verification         ✅
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
API tests                  ✅
CORS development           ✅
Private CV storage         ✅
```

Backend dapat mulai diintegrasikan dengan frontend menggunakan endpoint di atas.
