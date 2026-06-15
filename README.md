# Leave Management API

Leave Management API adalah RESTful API untuk sistem manajemen cuti karyawan. Aplikasi ini dibuat untuk menangani proses pengajuan cuti, autentikasi pengguna, login OAuth, pembagian role Employee dan Admin, validasi kuota cuti tahunan, upload attachment, serta proses approval dan rejection oleh admin.

Project ini dibuat sebagai bagian dari Technical Test Backend Developer.

---

## Daftar Isi

* [Tech Stack](#tech-stack)
* [Fitur Utama](#fitur-utama)
* [Role dan Hak Akses](#role-dan-hak-akses)
* [Aturan Bisnis](#aturan-bisnis)
* [Alur Sistem](#alur-sistem)
* [Arsitektur Project](#arsitektur-project)
* [Struktur Database](#struktur-database)
* [Panduan Instalasi](#panduan-instalasi)
* [Konfigurasi Environment](#konfigurasi-environment)
* [Migration dan Seeder](#migration-dan-seeder)
* [Akun Default](#akun-default)
* [Daftar Endpoint API](#daftar-endpoint-api)
* [Dokumentasi Postman](#dokumentasi-postman)
* [Skenario Pengujian](#skenario-pengujian)
* [Catatan Keamanan](#catatan-keamanan)

---

## Tech Stack

Project ini menggunakan teknologi berikut:

* Laravel 11
* PostgreSQL
* Laravel Sanctum
* Laravel Socialite
* GitHub OAuth
* RESTful API
* Postman Documentation
* PHP 8.2

---

## Fitur Utama

### Authentication

Sistem menyediakan beberapa fitur autentikasi:

* Register menggunakan nama, email, dan password
* Login konvensional menggunakan email dan password
* Logout menggunakan token Laravel Sanctum
* Login OAuth menggunakan GitHub

### Role Management

Sistem memiliki dua role utama:

1. Employee
2. Admin

Setiap role memiliki hak akses yang berbeda.

### Leave Request Management

Employee dapat:

* Mengajukan cuti
* Mengunggah attachment atau bukti pendukung
* Melihat daftar pengajuan cuti miliknya sendiri
* Melihat detail pengajuan cuti miliknya sendiri

Admin dapat:

* Melihat semua pengajuan cuti
* Melihat detail pengajuan cuti
* Menyetujui pengajuan cuti
* Menolak pengajuan cuti

### Leave Request Workflow

Alur status pengajuan cuti:

```text
Pending -> Approved
Pending -> Rejected
```

Status default ketika employee mengajukan cuti adalah:

```text
pending
```

---

## Role dan Hak Akses

### Employee

Employee hanya dapat mengakses data cuti miliknya sendiri.

Hak akses Employee:

* Membuat pengajuan cuti
* Melihat daftar pengajuan cuti sendiri
* Melihat detail pengajuan cuti sendiri

Employee tidak dapat:

* Melihat semua pengajuan cuti karyawan lain
* Melakukan approve pengajuan cuti
* Melakukan reject pengajuan cuti
* Mengakses endpoint khusus admin

### Admin

Admin memiliki akses penuh terhadap data pengajuan cuti.

Hak akses Admin:

* Melihat semua pengajuan cuti
* Melihat detail pengajuan cuti
* Menyetujui pengajuan cuti
* Menolak pengajuan cuti

Admin tidak menggunakan endpoint employee untuk mengajukan cuti, karena endpoint pengajuan cuti dibatasi hanya untuk role employee.

---

## Aturan Bisnis

### Kuota Cuti Tahunan

Setiap employee memiliki batas maksimal cuti:

```text
12 hari per tahun
```

Sistem akan menghitung jumlah hari cuti berdasarkan:

```text
start_date sampai end_date
```

Contoh:

```text
start_date : 2026-07-01
end_date   : 2026-07-03
total_days : 3
```

Jika total cuti employee dalam satu tahun melebihi 12 hari, maka sistem akan menolak pengajuan tersebut.

Contoh kasus:

```text
Cuti yang sudah digunakan : 9 hari
Pengajuan baru            : 5 hari
Total                     : 14 hari
Batas maksimal            : 12 hari
Hasil                     : Ditolak oleh validasi sistem
```

### Validasi Pengajuan Cuti

Employee wajib mengisi:

* start_date
* end_date
* reason
* attachment

Attachment yang diperbolehkan:

* pdf
* doc
* docx
* jpg
* jpeg
* png

Maksimal ukuran attachment:

```text
2 MB
```

### Validasi Status

Hanya pengajuan dengan status `pending` yang dapat diproses oleh admin.

Jika pengajuan sudah berstatus `approved` atau `rejected`, maka status tidak dapat diubah lagi.

---

## Alur Sistem

### Alur Pengajuan Cuti Employee

```text
Employee login
↓
Employee mengajukan cuti
↓
Sistem melakukan validasi input
↓
Sistem menghitung total hari cuti
↓
Sistem mengecek kuota cuti tahunan
↓
Sistem menyimpan attachment
↓
Pengajuan cuti dibuat dengan status pending
```

### Alur Approval Admin

```text
Admin login
↓
Admin melihat daftar pengajuan cuti
↓
Admin memilih pengajuan dengan status pending
↓
Admin menyetujui pengajuan
↓
Status pengajuan berubah menjadi approved
```

### Alur Rejection Admin

```text
Admin login
↓
Admin melihat daftar pengajuan cuti
↓
Admin memilih pengajuan dengan status pending
↓
Admin menolak pengajuan
↓
Admin dapat memberikan catatan penolakan
↓
Status pengajuan berubah menjadi rejected
```

---

## Arsitektur Project

Project ini menggunakan struktur berlapis agar kode lebih rapi, mudah dikembangkan, dan mudah diuji.

```text
Route
↓
Controller
↓
Form Request Validation
↓
Service
↓
Repository
↓
Model
↓
PostgreSQL Database
```

### Penjelasan Arsitektur

#### Route

Route digunakan untuk mendefinisikan endpoint API.

#### Controller

Controller bertugas menerima HTTP request dan mengembalikan response dalam bentuk JSON.

#### Form Request

Form Request digunakan untuk memvalidasi input dari user, seperti validasi register, login, pengajuan cuti, dan reject cuti.

#### Service

Service berisi business logic utama, seperti:

* Proses register
* Proses login
* Perhitungan kuota cuti
* Pembuatan pengajuan cuti
* Approval cuti
* Rejection cuti

#### Repository

Repository digunakan untuk mengelola query database agar logic query tidak menumpuk di controller atau service.

#### Model

Model merepresentasikan tabel database dan relasi antar tabel.

---

## Struktur Folder

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── AuthController.php
│   │       ├── OAuthController.php
│   │       ├── LeaveRequestController.php
│   │       └── AdminLeaveRequestController.php
│   ├── Middleware/
│   │   └── RoleMiddleware.php
│   └── Requests/
│       ├── RegisterRequest.php
│       ├── LoginRequest.php
│       ├── StoreLeaveRequestRequest.php
│       └── RejectLeaveRequestRequest.php
├── Models/
│   ├── User.php
│   └── LeaveRequest.php
├── Services/
│   ├── AuthService.php
│   └── LeaveRequestService.php
└── Repositories/
    └── LeaveRequestRepository.php
```

---

## Struktur Database

### Tabel users

| Kolom       | Keterangan                                   |
| ----------- | -------------------------------------------- |
| id          | Primary key                                  |
| name        | Nama pengguna                                |
| email       | Email pengguna                               |
| password    | Password pengguna, nullable untuk user OAuth |
| role        | Role pengguna: employee atau admin           |
| provider    | Nama provider OAuth                          |
| provider_id | ID user dari provider OAuth                  |
| created_at  | Waktu data dibuat                            |
| updated_at  | Waktu data diperbarui                        |

### Tabel leave_requests

| Kolom           | Keterangan                                    |
| --------------- | --------------------------------------------- |
| id              | Primary key                                   |
| user_id         | ID employee yang mengajukan cuti              |
| start_date      | Tanggal mulai cuti                            |
| end_date        | Tanggal selesai cuti                          |
| total_days      | Total hari cuti                               |
| reason          | Alasan pengajuan cuti                         |
| attachment_path | Lokasi file attachment                        |
| status          | Status cuti: pending, approved, atau rejected |
| approved_by     | ID admin yang menyetujui cuti                 |
| approved_at     | Waktu approval                                |
| rejected_by     | ID admin yang menolak cuti                    |
| rejected_at     | Waktu rejection                               |
| admin_note      | Catatan admin saat menolak cuti               |
| created_at      | Waktu data dibuat                             |
| updated_at      | Waktu data diperbarui                         |

---

## Panduan Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/AJULUMEL/leave-management-api
cd leave-management-api
```

### 2. Install Dependency

```bash
composer install
```

### 3. Copy File Environment

```bash
cp .env.example .env
```

Untuk Windows PowerShell:

```bash
copy .env.example .env
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Buat Database PostgreSQL

Buat database PostgreSQL dengan nama:

```text
leave_management_api
```

### 6. Jalankan Migration dan Seeder

```bash
php artisan migrate:fresh --seed
```

### 7. Buat Storage Link

```bash
php artisan storage:link
```

### 8. Jalankan Server Development

```bash
php artisan serve
```

API akan berjalan di:

```text
http://127.0.0.1:8000
```

---

## Konfigurasi Environment

Contoh konfigurasi `.env`:

```env
APP_NAME="Leave Management API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=leave_management_api
DB_USERNAME=postgres
DB_PASSWORD=

GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GITHUB_REDIRECT_URI=http://127.0.0.1:8000/api/auth/github/callback
```

### Catatan Penting

File `.env` asli tidak boleh diunggah ke GitHub karena berisi konfigurasi sensitif seperti database password dan OAuth secret.

File yang boleh diunggah adalah:

```text
.env.example
```

---

## Setup GitHub OAuth

Project ini menggunakan GitHub OAuth melalui Laravel Socialite.

Buat OAuth App di GitHub melalui:

```text
GitHub
↓
Settings
↓
Developer settings
↓
OAuth Apps
↓
New OAuth App
```

Gunakan konfigurasi berikut:

```text
Application name:
Leave Management API

Homepage URL:
http://127.0.0.1:8000

Authorization callback URL:
http://127.0.0.1:8000/api/auth/github/callback
```

Setelah OAuth App dibuat, salin Client ID dan Client Secret ke file `.env`:

```env
GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret
GITHUB_REDIRECT_URI=http://127.0.0.1:8000/api/auth/github/callback
```

Setelah mengubah `.env`, jalankan:

```bash
php artisan optimize:clear
```

---

## Migration dan Seeder

Untuk reset database dan membuat akun default, jalankan:

```bash
php artisan migrate:fresh --seed
```

Seeder akan membuat dua akun default:

* Admin
* Employee

---

## Akun Default

### Admin

```text
Email    : admin@example.com
Password : admin123
Role     : admin
```

### Employee

```text
Email    : employee@example.com
Password : password123
Role     : employee
```

---

## Daftar Endpoint API

Base URL:

```text
http://127.0.0.1:8000/api
```

---

### Authentication

| Method | Endpoint    | Keterangan                           | Auth         |
| ------ | ----------- | ------------------------------------ | ------------ |
| POST   | `/register` | Register akun employee baru          | Tidak        |
| POST   | `/login`    | Login menggunakan email dan password | Tidak        |
| POST   | `/logout`   | Logout token user yang sedang login  | Bearer Token |

---

### OAuth

| Method | Endpoint                | Keterangan                        | Auth  |
| ------ | ----------------------- | --------------------------------- | ----- |
| GET    | `/auth/github/redirect` | Membuat URL redirect OAuth GitHub | Tidak |
| GET    | `/auth/github/callback` | Callback OAuth GitHub             | Tidak |

---

### Employee Leave Requests

| Method | Endpoint                  | Keterangan                                    | Auth           |
| ------ | ------------------------- | --------------------------------------------- | -------------- |
| POST   | `/leave-requests`         | Mengajukan cuti                               | Employee Token |
| GET    | `/my-leave-requests`      | Melihat daftar cuti milik employee yang login | Employee Token |
| GET    | `/my-leave-requests/{id}` | Melihat detail cuti milik employee yang login | Employee Token |

---

### Admin Leave Requests

| Method | Endpoint                             | Keterangan                    | Auth        |
| ------ | ------------------------------------ | ----------------------------- | ----------- |
| GET    | `/admin/leave-requests`              | Melihat semua pengajuan cuti  | Admin Token |
| GET    | `/admin/leave-requests/{id}`         | Melihat detail pengajuan cuti | Admin Token |
| PATCH  | `/admin/leave-requests/{id}/approve` | Menyetujui pengajuan cuti     | Admin Token |
| PATCH  | `/admin/leave-requests/{id}/reject`  | Menolak pengajuan cuti        | Admin Token |

---

## Contoh Request API

### Register

```http
POST /api/register
```

Body:

```json
{
  "name": "Employee User",
  "email": "employee@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

Response:

```json
{
  "message": "User registered successfully.",
  "data": {
    "user": {
      "id": 1,
      "name": "Employee User",
      "email": "employee@example.com",
      "role": "employee"
    },
    "token": "token",
    "token_type": "Bearer"
  }
}
```

---

### Login

```http
POST /api/login
```

Body:

```json
{
  "email": "employee@example.com",
  "password": "password123"
}
```

Response:

```json
{
  "message": "Login successful.",
  "data": {
    "user": {
      "id": 1,
      "name": "Employee User",
      "email": "employee@example.com",
      "role": "employee"
    },
    "token": "token",
    "token_type": "Bearer"
  }
}
```

---

### Membuat Pengajuan Cuti

```http
POST /api/leave-requests
```

Request menggunakan:

```text
multipart/form-data
```

Body:

```text
start_date : 2026-07-01
end_date   : 2026-07-03
reason     : Keperluan keluarga
attachment : file.pdf
```

Response:

```json
{
  "message": "Leave request submitted successfully.",
  "data": {
    "id": 1,
    "user_id": 1,
    "start_date": "2026-07-01",
    "end_date": "2026-07-03",
    "total_days": 3,
    "reason": "Keperluan keluarga",
    "attachment_path": "leave-attachments/example.pdf",
    "status": "pending"
  }
}
```

---

### Melihat Pengajuan Cuti Sendiri

```http
GET /api/my-leave-requests
```

Header:

```text
Authorization: Bearer employee_token
Accept: application/json
```

---

### Melihat Semua Pengajuan Cuti

```http
GET /api/admin/leave-requests
```

Header:

```text
Authorization: Bearer admin_token
Accept: application/json
```

---

### Approve Pengajuan Cuti

```http
PATCH /api/admin/leave-requests/1/approve
```

Header:

```text
Authorization: Bearer admin_token
Accept: application/json
```

Response:

```json
{
  "message": "Leave request approved successfully.",
  "data": {
    "id": 1,
    "status": "approved"
  }
}
```

---

### Reject Pengajuan Cuti

```http
PATCH /api/admin/leave-requests/1/reject
```

Header:

```text
Authorization: Bearer admin_token
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "admin_note": "Dokumen pendukung kurang jelas."
}
```

Response:

```json
{
  "message": "Leave request rejected successfully.",
  "data": {
    "id": 1,
    "status": "rejected",
    "admin_note": "Dokumen pendukung kurang jelas."
  }
}
```

---

## Dokumentasi Postman

Published Postman Documentation:

```text
https://documenter.getpostman.com/view/44482461/2sBXwtoUHk
```

---

## Skenario Pengujian

### 1. Register dan Login

* Register akun employee baru
* Login menggunakan email dan password
* Salin token dari response login

### 2. Employee Mengajukan Cuti

* Gunakan token employee
* Kirim pengajuan cuti dengan attachment
* Pastikan status awal adalah pending

### 3. Admin Approve Cuti

* Login sebagai admin
* Gunakan token admin
* Approve pengajuan cuti yang masih pending

### 4. Admin Reject Cuti

* Login sebagai admin
* Gunakan token admin
* Reject pengajuan cuti yang masih pending
* Tambahkan admin note jika diperlukan

### 5. Pengujian Role Protection

Employee tidak boleh mengakses endpoint admin.

Expected response:

```json
{
  "message": "Forbidden. You do not have permission to access this resource."
}
```

### 6. Pengujian Kuota Cuti Tahunan

Employee tidak boleh mengajukan cuti lebih dari 12 hari dalam satu tahun.

Expected validation error:

```json
{
  "errors": {
    "leave_quota": [
      "Leave quota exceeded. Maximum leave quota is 12 days per year."
    ]
  }
}
```

---

## Catatan Keamanan

* Password disimpan dalam bentuk hash.
* Autentikasi API menggunakan Laravel Sanctum Bearer Token.
* Login OAuth menggunakan GitHub melalui Laravel Socialite.
* User OAuth otomatis dibuat sebagai employee.
* Akun admin dibuat melalui database seeder, bukan dari public register.
* Middleware role digunakan untuk melindungi endpoint employee dan admin.
* File `.env` tidak boleh diunggah ke repository.

---

## Submission

Project ini mencakup:

* Source code dalam public repository
* Panduan instalasi
* File `.env.example`
* Database migration
* Seeder untuk akun default
* RESTful API endpoints
* Login OAuth GitHub
* Published Postman Documentation

---

## Repository

GitHub Repository:

https://github.com/AJULUMEL/leave-management-api


## Author

Dikembangkan oleh:

```text
Dandi Azrul Syahputra
```
