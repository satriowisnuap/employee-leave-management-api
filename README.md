<div align="center">

# 🗓️ Employee Leave Management API

### *A Production-Grade RESTful API for Enterprise Leave Management*

---

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com)
[![Sanctum](https://img.shields.io/badge/Sanctum-4.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/docs/sanctum)
[![Socialite](https://img.shields.io/badge/Socialite-5.x-4285F4?style=for-the-badge&logo=google&logoColor=white)](https://laravel.com/docs/socialite)
[![Spatie](https://img.shields.io/badge/Spatie_Permission-8.x-5C2D91?style=for-the-badge&logo=spatie&logoColor=white)](https://spatie.be/docs/laravel-permission)

[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)
[![PHPUnit](https://img.shields.io/badge/Tests-PHPUnit_12-blue?style=flat-square)](https://phpunit.de)
[![Code Style](https://img.shields.io/badge/Code_Style-Laravel_Pint-orange?style=flat-square)](https://laravel.com/docs/pint)

</div>

---

> **Employee Leave Management API** adalah RESTful API berbasis Laravel yang dirancang sebagai solusi pengelolaan cuti karyawan secara terpusat. Dibangun untuk *Technical Test Backend Developer*, API ini mengimplementasikan arsitektur berlapis (*layered architecture*) dengan pola *Repository-Service*, autentikasi berbasis token via **Laravel Sanctum**, Single Sign-On via **Google OAuth (Socialite)**, serta manajemen peran dan izin menggunakan **Spatie Permission**.

---

## 📑 Table of Contents

- [Project Overview](#-project-overview)
- [Tech Stack](#-tech-stack)
- [System Architecture](#-system-architecture)
- [Features](#-features)
- [Business Rules](#-business-rules)
- [API Documentation](#-api-documentation)
- [Database Design](#-database-design)
- [Project Structure](#-project-structure)
- [Getting Started](#-getting-started)
- [Configuration](#-configuration)
- [Running Tests](#-running-tests)
- [Default Credentials](#-default-credentials)
- [Error Handling](#-error-handling)

---

## 📌 Project Overview

### Latar Belakang

Pengelolaan cuti karyawan secara manual menggunakan spreadsheet atau email kerap menimbulkan masalah seperti duplikasi data, kesulitan pelacakan status, dan tidak adanya validasi kuota secara otomatis. API ini hadir sebagai solusi backend yang terstruktur dan scalable untuk mengatasi permasalahan tersebut.

### Permasalahan yang Diselesaikan

| # | Permasalahan | Solusi |
|---|---|---|
| 1 | Tidak ada kontrol akses berbasis peran | Role-Based Access Control (RBAC) via Spatie Permission |
| 2 | Kuota cuti tidak tervalidasi otomatis | Validasi 12 hari/tahun di service layer |
| 3 | Permohonan cuti tumpang tindih | Overlap detection sebelum data tersimpan |
| 4 | Tidak ada autentikasi modern | Token-based auth (Sanctum) + Google SSO (Socialite) |
| 5 | Proses approval tidak terstruktur | Alur Pending → Approved/Rejected dengan audit trail |

### Tujuan API

- Menyediakan antarmuka backend yang **aman** dan **terstandarisasi** untuk sistem manajemen cuti
- Mendukung **dua peran pengguna** (Admin & Employee) dengan hak akses yang terpisah
- Mengotomatisasi **validasi bisnis** seperti kuota cuti, deteksi overlap, dan status approval
- Mendukung **autentikasi ganda** (email/password dan Google OAuth)

### Target Pengguna

| Peran | Deskripsi |
|---|---|
| **Employee** | Karyawan yang mengajukan permohonan cuti dan memantau statusnya |
| **Admin / HR** | Staf HR atau manajer yang meninjau, menyetujui, atau menolak pengajuan cuti |

### Scope Project

✅ Autentikasi (Register, Login, Logout, Google OAuth)  
✅ Manajemen permohonan cuti (Submit, View)  
✅ Workflow approval oleh Admin (Approve, Reject)  
✅ Validasi kuota cuti tahunan (12 hari/tahun)  
✅ Deteksi konflik jadwal cuti  
✅ Upload lampiran dokumen  
✅ Role-Based Access Control (RBAC)  
✅ Automated Feature Testing  

---

## 🛠️ Tech Stack

| Komponen | Teknologi | Versi |
|---|---|---|
| **Framework** | Laravel | 13.x |
| **Language** | PHP | 8.3+ |
| **Database** | MySQL / SQLite | 8.0 / latest |
| **Authentication** | Laravel Sanctum | 4.x |
| **Social Auth** | Laravel Socialite | 5.x |
| **Authorization** | Spatie Laravel Permission | 8.x |
| **Testing** | PHPUnit | 12.x |
| **Code Style** | Laravel Pint | 1.x |

---

## 🏗️ System Architecture

API ini dibangun menggunakan **Layered Architecture** dengan pola **Repository-Service Pattern**, memisahkan tanggung jawab setiap lapisan secara tegas:

```
┌─────────────────────────────────────────────────────────────┐
│                        HTTP Layer                           │
│   Routes (api.php)  ──►  Middleware (auth, role)            │
│                            │                                │
│   Request (Validation) ────▼─────  Controller              │
└─────────────────────────────┬───────────────────────────────┘
                              │
┌─────────────────────────────▼───────────────────────────────┐
│                      Service Layer                          │
│   LeaveService / AuthService                                │
│   (Business Logic, Validation, Domain Rules)                │
└─────────────────────────────┬───────────────────────────────┘
                              │
┌─────────────────────────────▼───────────────────────────────┐
│                   Repository Layer                          │
│   LeaveRepository / UserRepository                          │
│   (Data Access, Eloquent ORM)                               │
└─────────────────────────────┬───────────────────────────────┘
                              │
┌─────────────────────────────▼───────────────────────────────┐
│                     Data Layer                              │
│   Models (User, LeaveRequest)                               │
│   Migrations / Seeders / Factories                          │
└─────────────────────────────────────────────────────────────┘
```

### Komponen Arsitektur

| Komponen | Deskripsi |
|---|---|
| **Controller** | Menangani HTTP request/response, validasi input, delegasi ke Service |
| **Service** | Merangkum business logic dan domain rules |
| **Repository** | Abstraksi akses data via interface (Dependency Inversion) |
| **DTO (Data Transfer Object)** | Objek immutable untuk membawa data antar lapisan |
| **Enum** | Mendefinisikan nilai tetap untuk status cuti (`pending`, `approved`, `rejected`) |
| **Interface** | Kontrak repository agar mudah diganti implementasinya (testable) |
| **Helper** | `ApiResponse` untuk format respons JSON yang konsisten |
| **Resource** | Transformasi Eloquent model ke JSON representation |

---

## ✨ Features

### 🔐 Authentication & Authorization

- **Email/Password Registration & Login** – Registrasi mandiri, otomatis mendapat peran `Employee`
- **Google OAuth SSO** – Login via akun Google, akun dibuat otomatis jika belum ada
- **Token-Based Authentication** – Sanctum Personal Access Token untuk setiap sesi
- **Role-Based Access Control** – Pembatasan akses endpoint berdasarkan peran (`Admin`, `Employee`)
- **Secure Logout** – Menghapus token akses aktif

### 📋 Leave Request Management (Employee)

- **Submit Leave Request** – Pengajuan cuti dengan tanggal, alasan, dan lampiran dokumen
- **View Own Leaves** – Melihat seluruh pengajuan cuti milik sendiri, diurutkan terbaru
- **View Single Leave** – Detail satu pengajuan cuti (hanya milik sendiri)

### 🛡️ Admin Panel

- **View All Leaves** – Melihat seluruh pengajuan cuti dari semua karyawan
- **Approve Leave** – Menyetujui permohonan cuti dengan pencatatan waktu dan approver
- **Reject Leave** – Menolak permohonan cuti yang tidak memenuhi syarat

### 🧪 Quality Assurance

- **Automated Feature Tests** – Coverage lengkap untuk auth, otorisasi, business logic, file upload, Google OAuth, dan sorting
- **Test Isolation** – Setiap test menggunakan database segar (RefreshDatabase)
- **Mock & Stub** – Socialite dan Storage di-mock untuk test yang deterministik

---

## 📐 Business Rules

Seluruh aturan bisnis diimplementasikan di **Service Layer** (`LeaveService`):

### 1. Kuota Cuti Tahunan

```
Maksimal 12 hari cuti per karyawan per tahun (berdasarkan tahun dari start_date).
Perhitungan hanya dari leave request dengan status "approved".
```

> Jika pengajuan baru + total hari yang sudah disetujui melebihi 12, request **ditolak secara otomatis**.

### 2. Validasi Tanggal

```
End Date HARUS >= Start Date.
Pelanggaran akan mengembalikan error 422.
```

### 3. Deteksi Overlap

```
Satu karyawan tidak boleh memiliki dua pengajuan cuti (pending/approved) yang periodenya tumpang tindih.
```

Kondisi overlap yang dideteksi:
- `start_date` baru berada dalam range cuti yang sudah ada
- `end_date` baru berada dalam range cuti yang sudah ada
- Cuti baru sepenuhnya mencakup cuti yang sudah ada

### 4. Approval Rules

```
Hanya leave request berstatus "pending" yang dapat di-approve atau di-reject.
Jika pada saat approval kuota karyawan sudah terlampaui → otomatis di-reject (tidak error).
```

### 5. Pembatasan Akses Data

```
Employee hanya dapat melihat leave request miliknya sendiri.
Admin dapat melihat seluruh leave request dari semua karyawan.
```

### 6. Google OAuth – Auto Role Assignment

```
Jika user baru login via Google dan belum memiliki peran, otomatis mendapat peran "Employee".
```

---

## 📡 API Documentation

**Base URL:** `http://localhost:8000/api`  
**Content-Type:** `application/json`  
**Auth Header:** `Authorization: Bearer {token}`

---

### 🔓 Authentication Endpoints

#### `POST /api/auth/register`

Mendaftarkan karyawan baru. Otomatis mendapat peran `Employee`.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

**Response `201 Created`:**
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "id": 3,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2026-07-02T14:00:00.000000Z"
  }
}
```

---

#### `POST /api/auth/login`

Login menggunakan email dan password, mengembalikan Bearer Token.

**Request Body:**
```json
{
  "email": "employee@example.com",
  "password": "password"
}
```

**Response `200 OK`:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 2,
      "name": "Employee User",
      "email": "employee@example.com"
    },
    "token": "2|abcdef1234567890..."
  }
}
```

---

#### `GET /api/auth/google/redirect`

Mengembalikan URL redirect ke halaman OAuth Google.

**Response `200 OK`:**
```json
{
  "success": true,
  "data": {
    "redirect_url": "https://accounts.google.com/o/oauth2/auth?..."
  }
}
```

---

#### `GET /api/auth/google/callback`

Callback dari Google OAuth. Membuat atau menemukan user, mengembalikan token.

**Response `200 OK`:**
```json
{
  "success": true,
  "message": "Google Login successful",
  "data": {
    "user": { "id": 4, "name": "Jane Doe", "email": "jane@gmail.com" },
    "token": "5|xyz..."
  }
}
```

---

#### `POST /api/auth/logout` 🔒

Menghapus token aktif pengguna yang sedang login.

**Headers:** `Authorization: Bearer {token}`

**Response `200 OK`:**
```json
{
  "success": true,
  "message": "Logout successful",
  "data": null
}
```

---

### 👤 User Info Endpoint

#### `GET /api/user` 🔒

Mengembalikan data pengguna yang sedang login beserta perannya.

**Response `200 OK`:**
```json
{
  "id": 2,
  "name": "Employee User",
  "email": "employee@example.com",
  "roles": [{ "name": "Employee" }]
}
```

---

### 📋 Employee Leave Endpoints 🔒

> Membutuhkan autentikasi. Akses untuk peran `Employee` dan `Admin`.

#### `GET /api/leaves`

Mengambil seluruh permohonan cuti milik karyawan yang sedang login, diurutkan dari yang terbaru.

**Response `200 OK`:**
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "user_id": 2,
      "start_date": "2026-08-01",
      "end_date": "2026-08-03",
      "days": 3,
      "reason": "Liburan keluarga",
      "attachment": "attachments/document.pdf",
      "status": "pending",
      "approved_by": null,
      "approved_at": null,
      "created_at": "2026-07-02T15:00:00.000000Z"
    }
  ]
}
```

---

#### `GET /api/leaves/{id}`

Mengambil detail satu permohonan cuti berdasarkan ID. Hanya bisa mengakses data milik sendiri.

**Response `200 OK`:** *(sama dengan item di atas)*

**Response `404 Not Found`:**
```json
{
  "success": false,
  "message": "Leave request not found.",
  "data": null
}
```

---

#### `POST /api/leaves` 🔒 `Employee Only`

Mengajukan permohonan cuti baru dengan lampiran dokumen.

**Request:** `multipart/form-data`

| Field | Type | Required | Keterangan |
|---|---|---|---|
| `start_date` | `date` (Y-m-d) | ✅ | Tanggal mulai cuti |
| `end_date` | `date` (Y-m-d) | ✅ | Tanggal selesai cuti (≥ start_date) |
| `reason` | `string` | ✅ | Alasan pengajuan cuti |
| `attachment` | `file` (pdf/jpg/png/jpeg, max 2MB) | ✅ | Dokumen pendukung |

**Response `201 Created`:**
```json
{
  "success": true,
  "message": "Leave request submitted successfully",
  "data": {
    "id": 6,
    "user_id": 2,
    "start_date": "2026-08-10",
    "end_date": "2026-08-12",
    "days": 3,
    "reason": "Keperluan keluarga",
    "attachment": "attachments/abc123.pdf",
    "status": "pending",
    "created_at": "2026-07-02T16:00:00.000000Z"
  }
}
```

**Response `422 Unprocessable` (Quota Exceeded):**
```json
{
  "success": false,
  "message": "Leave quota exceeded. You have 5 days left this year.",
  "data": null
}
```

---

### 🛡️ Admin Leave Endpoints 🔒 `Admin Only`

> Membutuhkan autentikasi dengan peran `Admin`.

#### `GET /api/admin/leaves`

Mengambil seluruh permohonan cuti dari semua karyawan, diurutkan dari yang terbaru.

**Response `200 OK`:**
```json
{
  "success": true,
  "data": [
    {
      "id": 6,
      "user_id": 2,
      "start_date": "2026-08-10",
      "end_date": "2026-08-12",
      "days": 3,
      "reason": "Keperluan keluarga",
      "attachment": "attachments/abc123.pdf",
      "status": "pending",
      "approved_by": null,
      "approved_at": null
    }
  ]
}
```

---

#### `PATCH /api/admin/leaves/{id}/approve`

Menyetujui permohonan cuti. Hanya bisa approve status `pending`.

**Response `200 OK`:**
```json
{
  "success": true,
  "message": "Leave approved successfully",
  "data": {
    "id": 6,
    "status": "approved",
    "approved_by": 1,
    "approved_at": "2026-07-02T16:30:00.000000Z"
  }
}
```

---

#### `PATCH /api/admin/leaves/{id}/reject`

Menolak permohonan cuti. Hanya bisa reject status `pending`.

**Response `200 OK`:**
```json
{
  "success": true,
  "message": "Leave rejected successfully",
  "data": {
    "id": 6,
    "status": "rejected",
    "approved_by": 1,
    "approved_at": "2026-07-02T16:35:00.000000Z"
  }
}
```

---

### 📊 Endpoint Summary Table

| Method | Endpoint | Auth | Role | Deskripsi |
|---|---|---|---|---|
| `POST` | `/api/auth/register` | ❌ | - | Registrasi karyawan baru |
| `POST` | `/api/auth/login` | ❌ | - | Login email/password |
| `GET` | `/api/auth/google/redirect` | ❌ | - | Redirect ke Google OAuth |
| `GET` | `/api/auth/google/callback` | ❌ | - | Callback Google OAuth |
| `POST` | `/api/auth/logout` | ✅ | Any | Logout & hapus token |
| `GET` | `/api/user` | ✅ | Any | Info user aktif |
| `GET` | `/api/leaves` | ✅ | Employee, Admin | List cuti sendiri |
| `GET` | `/api/leaves/{id}` | ✅ | Employee, Admin | Detail cuti sendiri |
| `POST` | `/api/leaves` | ✅ | Employee | Ajukan cuti baru |
| `GET` | `/api/admin/leaves` | ✅ | Admin | List semua cuti |
| `PATCH` | `/api/admin/leaves/{id}/approve` | ✅ | Admin | Approve cuti |
| `PATCH` | `/api/admin/leaves/{id}/reject` | ✅ | Admin | Reject cuti |

---

## 🗄️ Database Design

### Entity Relationship Diagram

```
┌──────────────────────────┐          ┌────────────────────────────────┐
│          users           │          │        leave_requests          │
├──────────────────────────┤          ├────────────────────────────────┤
│ id            BIGINT PK  │◄────┐    │ id            BIGINT PK        │
│ name          VARCHAR    │    │    │ user_id       BIGINT FK ───────►│
│ email         VARCHAR UQ │    │    │ start_date    DATE              │
│ email_verified_at TSTP   │    │    │ end_date      DATE              │
│ password      VARCHAR    │    │    │ days          INT UNSIGNED      │
│ provider_name VARCHAR    │    │    │ reason        TEXT              │
│ provider_id   VARCHAR    │    │    │ attachment    VARCHAR           │
│ avatar        VARCHAR    │    │    │ status        ENUM(pending,     │
│ remember_token VARCHAR   │    │    │               approved,rejected)│
│ created_at    TIMESTAMP  │    └────│ approved_by   BIGINT FK (null)  │
│ updated_at    TIMESTAMP  │         │ approved_at   TIMESTAMP (null)  │
└──────────────────────────┘         │ created_at    TIMESTAMP         │
                                     │ updated_at    TIMESTAMP         │
                                     └────────────────────────────────┘

┌─────────────────────┐    ┌──────────────────────┐    ┌─────────────────┐
│       roles         │    │   model_has_roles     │    │   permissions   │
├─────────────────────┤    ├──────────────────────┤    ├─────────────────┤
│ id    BIGINT PK     │◄───│ role_id   BIGINT FK  │    │ id  BIGINT PK   │
│ name  VARCHAR       │    │ model_type VARCHAR    │    │ name VARCHAR    │
│ guard_name VARCHAR  │    │ model_id  BIGINT FK ──►    │ guard_name ...  │
└─────────────────────┘    └──────────────────────┘    └─────────────────┘
```

### Tabel: `users`

| Kolom | Tipe | Nullable | Keterangan |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | ❌ | Primary Key |
| `name` | VARCHAR(255) | ❌ | Nama lengkap |
| `email` | VARCHAR(255) | ❌ | Unique, untuk login |
| `email_verified_at` | TIMESTAMP | ✅ | Waktu verifikasi email |
| `password` | VARCHAR(255) | ✅ | NULL untuk user OAuth |
| `provider_name` | VARCHAR(255) | ✅ | `google` (untuk OAuth) |
| `provider_id` | VARCHAR(255) | ✅ | ID dari provider OAuth |
| `avatar` | VARCHAR(255) | ✅ | URL foto profil dari OAuth |
| `remember_token` | VARCHAR(100) | ✅ | Token remember me |
| `created_at` | TIMESTAMP | ✅ | - |
| `updated_at` | TIMESTAMP | ✅ | - |

### Tabel: `leave_requests`

| Kolom | Tipe | Nullable | Keterangan |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | ❌ | Primary Key |
| `user_id` | BIGINT UNSIGNED | ❌ | FK → `users.id` (cascade delete) |
| `start_date` | DATE | ❌ | Tanggal mulai cuti |
| `end_date` | DATE | ❌ | Tanggal selesai cuti |
| `days` | INT UNSIGNED | ❌ | Jumlah hari (dihitung otomatis) |
| `reason` | TEXT | ❌ | Alasan pengajuan cuti |
| `attachment` | VARCHAR(255) | ❌ | Path file lampiran |
| `status` | ENUM | ❌ | `pending` / `approved` / `rejected` |
| `approved_by` | BIGINT UNSIGNED | ✅ | FK → `users.id` (null on delete) |
| `approved_at` | TIMESTAMP | ✅ | Waktu approval/rejection |
| `created_at` | TIMESTAMP | ✅ | - |
| `updated_at` | TIMESTAMP | ✅ | - |

---

## 📁 Project Structure

```
employee-leave-management-api/
├── app/
│   ├── DTO/
│   │   └── LeaveDTO.php              # Data Transfer Object untuk leave request
│   ├── Enums/
│   │   └── LeaveStatus.php           # Enum: pending | approved | rejected
│   ├── Exceptions/
│   │   └── Handler.php               # Global exception handler
│   ├── Helpers/
│   │   └── ApiResponse.php           # Standarisasi format JSON response
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminLeaveController.php  # Endpoint admin: list, approve, reject
│   │   │   ├── AuthController.php        # Auth: register, login, logout, Google OAuth
│   │   │   ├── Controller.php            # Base controller
│   │   │   └── LeaveController.php       # Endpoint employee: list, show, store
│   │   ├── Middleware/               # Custom middleware
│   │   ├── Requests/
│   │   │   ├── LoginRequest.php          # Validasi input login
│   │   │   ├── RegisterRequest.php       # Validasi input registrasi
│   │   │   ├── StoreLeaveRequest.php     # Validasi input pengajuan cuti
│   │   │   └── UpdateLeaveStatusRequest.php  # Validasi update status
│   │   └── Resources/
│   │       └── LeaveResource.php         # API Resource transformer
│   ├── Interfaces/
│   │   ├── LeaveRepositoryInterface.php  # Kontrak repository cuti
│   │   └── UserRepositoryInterface.php   # Kontrak repository user
│   ├── Models/
│   │   ├── LeaveRequest.php          # Eloquent model: leave_requests
│   │   └── User.php                  # Eloquent model: users
│   ├── Providers/
│   │   └── AppServiceProvider.php    # Binding interface ke implementasi
│   ├── Repositories/
│   │   ├── LeaveRepository.php       # Implementasi akses data leave_requests
│   │   └── UserRepository.php        # Implementasi akses data users
│   ├── Service/
│   │   ├── AuthService.php           # Business logic: auth & OAuth
│   │   └── LeaveService.php          # Business logic: kuota, overlap, approval
│   └── Traits/                       # Shared traits
│
├── database/
│   ├── factories/                    # Model factories untuk testing
│   ├── migrations/
│   │   ├── ..._create_users_table.php
│   │   ├── ..._create_personal_access_tokens_table.php
│   │   ├── ..._create_permission_tables.php
│   │   └── ..._create_leave_requests_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RoleSeeder.php            # Seed roles: Admin, Employee
│       └── UserSeeder.php            # Seed default admin & employee
│
├── routes/
│   ├── api.php                       # API routes (auth, employee, admin)
│   └── auth.php                      # Default Breeze auth routes (web)
│
├── tests/
│   ├── Feature/
│   │   ├── AuthenticationTest.php    # Test: register, login, logout
│   │   ├── AuthorizationTest.php     # Test: RBAC, 401, 403
│   │   ├── GoogleOAuthCallbackTest.php   # Test: Google OAuth callback
│   │   ├── GoogleOAuthRedirectTest.php   # Test: Google OAuth redirect
│   │   ├── LeaveAccessControlTest.php    # Test: pembatasan akses data
│   │   ├── LeaveAttachmentTest.php   # Test: upload file & storage
│   │   ├── LeaveBusinessLogicTest.php    # Test: kuota, overlap, approval rules
│   │   └── LeaveSortingTest.php      # Test: urutan data (latest)
│   └── TestCase.php
│
├── .env.example                      # Template environment variables
├── composer.json                     # PHP dependencies
└── phpunit.xml                       # Konfigurasi PHPUnit
```

---

## 🚀 Getting Started

### Prerequisites

Pastikan sistem Anda memiliki:

- **PHP** >= 8.3
- **Composer** >= 2.x
- **MySQL** >= 8.0 atau **SQLite** (untuk development)
- **Node.js** >= 18.x (opsional, untuk asset build)

### Installation

**1. Clone Repository**
```bash
git clone https://github.com/yourusername/employee-leave-management-api.git
cd employee-leave-management-api
```

**2. Install Dependencies**
```bash
composer install
```

**3. Setup Environment**
```bash
cp .env.example .env
php artisan key:generate
```

**4. Konfigurasi Database**

Edit `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=employee_leave_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

**5. Jalankan Migrasi & Seeder**
```bash
php artisan migrate --seed
```

**6. Buat Storage Link**
```bash
php artisan storage:link
```

**7. Jalankan Server**
```bash
php artisan serve
```

API siap diakses di: `http://localhost:8000/api`

---

## ⚙️ Configuration

### Google OAuth Setup

**1.** Buat project di [Google Cloud Console](https://console.cloud.google.com)  
**2.** Aktifkan **Google+ API** atau **Google Identity Services**  
**3.** Buat OAuth 2.0 Client ID dengan Authorized redirect URI:
```
http://localhost:8000/api/auth/google/callback
```

**4.** Tambahkan ke `.env`:
```env
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback
```

### Environment Variables

```env
# Application
APP_NAME="Employee Leave Management API"
APP_ENV=local
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_DATABASE=employee_leave_db

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost:3000

# Google OAuth
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=

# Filesystem
FILESYSTEM_DISK=public
```

---

## 🧪 Running Tests

### Menjalankan Semua Test

```bash
php artisan test
```

atau menggunakan Composer:

```bash
composer test
```

### Menjalankan Test Spesifik

```bash
# Test autentikasi
php artisan test tests/Feature/AuthenticationTest.php

# Test otorisasi & RBAC
php artisan test tests/Feature/AuthorizationTest.php

# Test business logic (kuota, overlap, approval)
php artisan test tests/Feature/LeaveBusinessLogicTest.php

# Test Google OAuth
php artisan test tests/Feature/GoogleOAuthCallbackTest.php

# Test upload attachment
php artisan test tests/Feature/LeaveAttachmentTest.php

# Test sorting data
php artisan test tests/Feature/LeaveSortingTest.php
```

### Coverage Area

| Test File | Skenario yang Diuji |
|---|---|
| `AuthenticationTest` | Register, login sukses/gagal, logout, token revocation |
| `AuthorizationTest` | 401 unauthenticated, 403 forbidden (wrong role) |
| `GoogleOAuthCallbackTest` | User baru via Google, user existing, auto role assignment |
| `LeaveBusinessLogicTest` | Quota check, overlap detection, approve/reject workflow |
| `LeaveAttachmentTest` | File upload, storage mock, path persistence |
| `LeaveAccessControlTest` | Employee hanya akses data sendiri |
| `LeaveSortingTest` | Data diurutkan dari yang terbaru (`latest()`) |

---

## 🔑 Default Credentials

Setelah menjalankan seeder, tersedia akun default berikut:

| Peran | Email | Password |
|---|---|---|
| **Admin** | `admin@example.com` | `password` |
| **Employee** | `employee@example.com` | `password` |

> ⚠️ **Penting:** Ganti password default sebelum deploy ke production.

---

## 🚨 Error Handling

API menggunakan format respons yang konsisten via `ApiResponse` helper:

### Format Sukses

```json
{
  "success": true,
  "message": "Deskripsi pesan",
  "data": { ... }
}
```

### Format Error

```json
{
  "success": false,
  "message": "Deskripsi error",
  "data": null
}
```

### HTTP Status Codes

| Kode | Kondisi |
|---|---|
| `200 OK` | Request berhasil |
| `201 Created` | Resource berhasil dibuat |
| `401 Unauthorized` | Tidak ada / token tidak valid |
| `403 Forbidden` | Token valid tapi peran tidak memiliki akses |
| `404 Not Found` | Resource tidak ditemukan |
| `422 Unprocessable Entity` | Validasi gagal (field tidak valid / business rule violated) |

---

## 📄 License

Project ini dilisensikan di bawah [MIT License](LICENSE).

---

<div align="center">

**Made with ❤️ for Backend Developer Technical Test**

*Built with Laravel 13 · PHP 8.3 · MySQL · Sanctum · Socialite · Spatie Permission*

</div>
