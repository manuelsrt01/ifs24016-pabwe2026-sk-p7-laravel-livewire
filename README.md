# ifs24016-pabwe2026-sk-p7-laravel-livewire

Studi kasus PABWE 2026 P7 — **Stock Inventory** (Laravel 13, Livewire 4, MySQL/MariaDB, PhpSpreadsheet, OpenAI),
Feature-First + Clean Architecture.

> Folder `vendor/` dan `node_modules/` sengaja tidak disertakan (diunduh lewat Composer/npm). Kerangka Laravel
> (config, artisan, public/index.php, dst.) ditulis manual, jadi **kode belum pernah dijalankan** oleh pembuatnya.
> Jalankan langkah di bawah lalu `composer test`; kirim outputnya bila ada yang gagal.

## Menjalankan (Windows PowerShell)

```powershell
cd ifs24016-pabwe2026-sk-p7-laravel-livewire
copy .env.example .env                 # lalu isi DB_PASSWORD bila bukan "root"
composer install
php artisan key:generate
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS pabwe_p7_stock CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan storage:link
php artisan migrate --seed             # gunakan migrate:fresh --seed hanya jika boleh menghapus data DB ini
npm install --ignore-scripts
npm run build
php artisan serve
```

Akun uji: `admin` / `admin` (→ `/admin/users`), `member` / `member` (→ `/stocks`).
Fitur AI butuh `OPENAI_API_KEY` di `.env`; tanpa itu UI menampilkan pesan konfigurasi.

## Pengujian & kualitas

```powershell
composer test      # PHPUnit + coverage-text (butuh Xdebug/PCOV). Target app/ = 100%
composer audit
```
Test memakai SQLite in-memory (lihat `phpunit.xml`); aplikasi tetap MySQL/MariaDB. Setelah `composer install`, commit `composer.lock`.

## Struktur

`app/Features/{Admin,Ai,Auth,Profile,Stock}` (Livewire, Services, Repositories, Policies, Enums, DTO, Contracts),
middleware `role` & `owns.stock`, `StockPolicy`, rate limiter `1000-per-second`, `FORCE_HTTPS`, `Jenkinsfile`.

## Keputusan desain

- Status stok dihitung satu tempat (`StockStatus::fromQuantity`) dan dipaksa event `saving` pada model `Stock`.
- Admin read-only dijaga di backend (role middleware, policy, service tanpa method tulis).
- Import Excel all-or-nothing dengan pesan per baris. Deskripsi Markdown dirender server-side (HTML di-strip);
  editor memakai textarea biasa, bukan Toast UI.
- Sesi memakai tabel `sessions`; cache `file`; queue `sync` (tidak perlu migration cache/jobs).
