# 📚 Sistem Perpustakaan Digital

Aplikasi perpustakaan digital berbasis web yang menyediakan fitur peminjaman dan pengembalian buku dengan dukungan REST API dan GraphQL API. Sistem ini dilengkapi dengan autentikasi JWT, role-based access control (admin & member), manajemen denda, dan integrasi payment gateway Midtrans.

---

## 🚀 Fitur Utama

### Member
- ✅ Registrasi dan login member
- ✅ Melihat katalog buku dan mencari buku
- ✅ Meminjam buku (dengan validasi stok)
- ✅ Mengembalikan buku
- ✅ Melihat riwayat transaksi peminjaman
- ✅ Cek denda keterlambatan
- ✅ Pembayaran denda (manual & Midtrans)

### Admin
- ✅ Login admin
- ✅ Manajemen buku (Create, Read, Update, Delete)
- ✅ Monitoring semua transaksi

### API
- ✅ REST API dengan 16 endpoints
- ✅ GraphQL API (Query & Mutation)
- ✅ JWT Authentication & Authorization
- ✅ Consistent JSON Response Format

---

## 🛠️ Teknologi yang Digunakan

### Backend
- **Framework**: Laravel 10
- **Database**: MySQL 8.0
- **Authentication**: Laravel Sanctum (JWT)
- **GraphQL**: Lighthouse PHP
- **Payment Gateway**: Midtrans

### Development Tools
- **Server**: XAMPP
- **API Testing**: Postman
- **GraphQL Testing**: Altair GraphQL Client
- **Version Control**: Git & GitHub
- **Package Manager**: Composer

### Dependencies
```json
{
    "php": "^8.1",
    "laravel/framework": "^10.0",
    "laravel/sanctum": "^3.2",
    "nuwave/lighthouse": "^6.0",
    "midtrans/midtrans-php": "^2.5"
}
