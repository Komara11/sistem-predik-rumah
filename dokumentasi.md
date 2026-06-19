# 📖 Dokumentasi Sistem Prediksi Harga Rumah Kabupaten Majalengka

## Daftar Isi

1. [Ringkasan Sistem](#1-ringkasan-sistem)
2. [Arsitektur Sistem](#2-arsitektur-sistem)
3. [Teknologi yang Digunakan](#3-teknologi-yang-digunakan)
4. [Struktur File Proyek](#4-struktur-file-proyek)
5. [Flow Kerja Sistem](#5-flow-kerja-sistem)
6. [Cara Menjalankan Sistem](#6-cara-menjalankan-sistem)
7. [Dataset](#7-dataset)
8. [Model Machine Learning](#8-model-machine-learning)
9. [API Flask (Microservice)](#9-api-flask-microservice)
10. [Aplikasi User (Laravel)](#10-aplikasi-user-laravel)
11. [Aplikasi Admin (Laravel)](#11-aplikasi-admin-laravel)
12. [Database](#12-database)
13. [Troubleshooting](#13-troubleshooting)

---

## 1. Ringkasan Sistem

Sistem ini adalah **aplikasi prediksi harga rumah** di Kabupaten Majalengka menggunakan **algoritma Random Forest**. Sistem terdiri dari 3 komponen utama yang berjalan secara terpisah:

| Komponen | Port | Fungsi |
|----------|------|--------|
| **Flask API** | 5000 | Menjalankan model ML, menerima prediksi, retraining |
| **Laravel User App** | 8000 | Halaman publik untuk prediksi harga & kalkulator KPR |
| **Laravel Admin App** | 8001 | Dashboard admin untuk kelola dataset & training model |

---

## 2. Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────────────┐
│                        BROWSER (USER)                           │
│                                                                 │
│  ┌──────────────┐                    ┌──────────────────────┐   │
│  │ Form Prediksi│ ──── GET ────────► │  Hasil Prediksi      │   │
│  │ /prediksi    │                    │  /prediksi/hasil     │   │
│  └──────────────┘                    └──────────────────────┘   │
└────────────────────────────┬────────────────────────────────────┘
                             │
                    HTTP Request (Port 8000)
                             │
┌────────────────────────────▼────────────────────────────────────┐
│                   LARAVEL USER APP (Port 8000)                  │
│                                                                 │
│  PredictionController                                           │
│  ├── index() → Tampilkan form                                   │
│  └── result() → Kirim ke Flask → Simpan DB → Tampilkan hasil   │
│                                                                 │
│  KPR Calculator (client-side JS)                                │
└────────────────────────────┬────────────────────────────────────┘
                             │
                    HTTP POST /predict (Port 5000)
                             │
┌────────────────────────────▼────────────────────────────────────┐
│                    FLASK ML API (Port 5000)                      │
│                                                                 │
│  Endpoints:                                                     │
│  ├── GET  /health      → Status model                           │
│  ├── GET  /model-info  → Metadata & feature importances         │
│  ├── POST /predict     → Prediksi harga rumah                   │
│  └── POST /retrain     → Latih ulang model dari admin           │
│                                                                 │
│  Artifacts: model.joblib, columns.joblib, encoders.joblib       │
└────────────────────────────┬────────────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────────────┐
│                   LARAVEL ADMIN APP (Port 8001)                 │
│                                                                 │
│  DashboardController → Statistik dari DB + Flask /model-info    │
│  DatasetController   → CRUD properti + Upload Excel             │
│  SettingsController  → Retrain model via Flask /retrain         │
└────────────────────────────┬────────────────────────────────────┘
                             │
                   Shared SQLite Database
                             │
┌────────────────────────────▼────────────────────────────────────┐
│              SQLite: database.sqlite (Shared)                   │
│                                                                 │
│  Tables:                                                        │
│  ├── users       → Admin login (email/password)                 │
│  ├── properties  → 150 data rumah dari Excel dataset            │
│  └── predictions → Log setiap prediksi yang dilakukan user      │
└─────────────────────────────────────────────────────────────────┘
```

### Mengapa Arsitektur Ini?

1. **Pemisahan Domain** — Admin dan User memiliki domain/port berbeda sehingga user tidak bisa mengakses halaman admin.
2. **Microservice ML** — Model ML dijalankan terpisah di Flask sehingga Laravel tidak perlu dependency Python. Jika model perlu di-update, cukup restart Flask.
3. **Shared Database** — Kedua app Laravel menggunakan file SQLite yang sama sehingga data selalu sinkron.

---

## 3. Teknologi yang Digunakan

### Backend
| Teknologi | Versi | Fungsi |
|-----------|-------|--------|
| PHP | 8.x | Runtime Laravel |
| Laravel | 11 | Framework web (User + Admin) |
| Python | 3.12 | Runtime ML model |
| Flask | 3.x | API microservice |
| scikit-learn | 1.9 | Random Forest algorithm |
| pandas | 3.x | Data processing |
| PhpSpreadsheet | 5.x | Baca/tulis file Excel |

### Frontend
| Teknologi | Fungsi |
|-----------|--------|
| Tailwind CSS v4 | Utility-first CSS framework |
| Vite | Asset bundler |
| Material Symbols | Google icon set |
| Vanilla JS | Kalkulasi KPR & interaktivitas |

### Database
| Teknologi | Fungsi |
|-----------|--------|
| SQLite | Database file-based (ringan, tanpa server) |

---

## 4. Struktur File Proyek

```
tugas-akhir-deka/
│
├── UI-peedik-rumah/                    # ← LARAVEL USER APP
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   └── PredictionController.php  # Logika prediksi
│   │   └── Models/
│   │       ├── Property.php              # Model properti
│   │       └── Prediction.php            # Model log prediksi
│   ├── database/
│   │   ├── database.sqlite               # ← DATABASE UTAMA (shared)
│   │   ├── migrations/
│   │   │   ├── ...create_properties_table.php
│   │   │   └── ...create_predictions_table.php
│   │   └── seeders/
│   │       └── PropertySeeder.php        # Import Excel ke DB
│   ├── ml/                               # ← MODEL MACHINE LEARNING
│   │   ├── train_model.py                # Script training
│   │   ├── api.py                        # Flask API server
│   │   ├── model.joblib                  # Model tersimpan
│   │   ├── columns.joblib                # Urutan fitur
│   │   ├── encoders.joblib               # Label encoders
│   │   ├── metrics.json                  # Metrik evaluasi (R², MAPE, dll)
│   │   └── requirements.txt             # Python dependencies
│   ├── resources/views/
│   │   ├── layouts/app.blade.php         # Layout utama
│   │   ├── prediction/
│   │   │   ├── index.blade.php           # Form input prediksi
│   │   │   └── result.blade.php          # Hasil prediksi
│   │   └── kpr.blade.php                # Kalkulator KPR
│   ├── routes/web.php
│   └── Dataset Rumah di Kabupaten Majalengka.xlsx  # Dataset asli
│
├── admin-predik-rumah/                 # ← LARAVEL ADMIN APP
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   ├── DashboardController.php   # Statistik & overview
│   │   │   ├── DatasetController.php     # CRUD dataset + upload
│   │   │   └── SettingsController.php    # Retrain model
│   │   └── Models/
│   │       ├── Property.php
│   │       └── Prediction.php
│   ├── resources/views/admin/
│   │   ├── dashboard.blade.php           # Halaman overview
│   │   ├── dataset.blade.php             # Manajemen dataset
│   │   └── settings.blade.php            # Pengaturan & retrain
│   ├── routes/web.php
│   └── .env                              # DB_DATABASE → shared SQLite
```

---

## 5. Flow Kerja Sistem

### 5.1 Flow Prediksi Harga (User)

```
1. User buka http://localhost:8000/prediksi
2. User isi form (luas tanah, bangunan, lokasi, tipe, kondisi, dll)
3. User klik "Hitung Estimasi Harga"
4. Laravel PredictionController menerima data
5. Controller kirim HTTP POST ke Flask API (localhost:5000/predict)
6. Flask load model → encode fitur → predict dengan 100 pohon
7. Flask hitung rata-rata, std dev, confidence interval
8. Flask return JSON {price, min_price, max_price, confidence, category}
9. Controller simpan hasil ke tabel `predictions`
10. Controller render halaman result dengan data prediksi
11. User lihat harga + grafik feature importance + tabel parameter
```

### 5.2 Flow Upload Dataset (Admin)

```
1. Admin buka http://localhost:8001/dataset
2. Admin klik area upload → pilih file Excel (.xlsx)
3. Admin klik "Proses & Import"
4. DatasetController baca file Excel dengan PhpSpreadsheet
5. Setiap baris di-insert ke tabel `properties`
6. Admin melihat data baru di tabel (paginated)
7. Admin bisa hapus data satu per satu
```

### 5.3 Flow Retrain Model (Admin)

```
1. Admin buka http://localhost:8001/settings
2. Admin atur parameter (n_estimators, max_depth, test_size)
3. Admin klik "Latih Ulang Model"
4. JavaScript fetch POST ke /settings/retrain
5. SettingsController forward request ke Flask /retrain
6. Flask baca ulang dataset Excel
7. Flask train model baru dengan parameter yang dikirim
8. Flask evaluate (R², MAE, RMSE, Cross-Validation)
9. Flask simpan model baru ke file .joblib (hot-swap)
10. Flask return metrics ke admin
11. Admin lihat hasil di konsol output & kartu metrik
```

---

## 6. Cara Menjalankan Sistem

### Prasyarat

- PHP 8.x + extensi SQLite
- Node.js + npm
- Python 3.12+
- Library Python: `pandas`, `scikit-learn`, `flask`, `flask-cors`, `openpyxl`, `joblib`

### Langkah Menjalankan

```bash
# ═══════════════════════════════════════════════════
# LANGKAH 1: Training Model (satu kali atau saat update dataset)
# ═══════════════════════════════════════════════════
cd UI-peedik-rumah
python3 ml/train_model.py

# ═══════════════════════════════════════════════════
# LANGKAH 2: Migrasi & Seed Database (satu kali)
# ═══════════════════════════════════════════════════
php artisan migrate:fresh --seed
# → Akan membuat tabel properties & predictions
# → Akan import 150 data dari file Excel

# ═══════════════════════════════════════════════════
# LANGKAH 3: Jalankan Flask API (harus jalan di background)
# ═══════════════════════════════════════════════════
python3 ml/api.py &
# → Berjalan di http://127.0.0.1:5000

# ═══════════════════════════════════════════════════
# LANGKAH 4: Jalankan Laravel User App
# ═══════════════════════════════════════════════════
php artisan serve --port=8000
# → Berjalan di http://127.0.0.1:8000

# ═══════════════════════════════════════════════════
# LANGKAH 5: Jalankan Laravel Admin App (terminal baru)
# ═══════════════════════════════════════════════════
cd admin-predik-rumah
php artisan serve --port=8001
# → Berjalan di http://127.0.0.1:8001
```

### Urutan Startup yang Benar

> **PENTING**: Flask API (langkah 3) HARUS berjalan sebelum User atau Admin App digunakan, karena prediksi dan model info bergantung pada Flask.

---

## 7. Dataset

### Spesifikasi

| Item | Detail |
|------|--------|
| File | `Dataset Rumah di Kabupaten Majalengka.xlsx` |
| Jumlah Baris | 150 |
| Jumlah Kolom | 11 |
| Target | `harga` (Rp) |
| Null Values | 0 (clean data) |

### Kolom Dataset

| Kolom | Tipe | Deskripsi | Range |
|-------|------|-----------|-------|
| `tahun` | int | Tahun data | 2022–2024 |
| `luas_tanah` | int | Luas tanah (m²) | 60–197 |
| `luas_bangunan` | int | Luas bangunan (m²) | 40–180 |
| `kmr_tidur` | int | Jumlah kamar tidur | 2–5 |
| `kmr_mandi` | int | Jumlah kamar mandi | 1–3 |
| `usia` | int | Usia bangunan (tahun) | 0–20 |
| `lokasi` | string | Kecamatan | 6 lokasi |
| `tipe_properti` | string | Tipe properti | Subsidi, Minimalis, Mewah |
| `kondisi` | string | Kondisi bangunan | Baru, Bekas |
| `ada_garasi` | int | Ada garasi? | 0 atau 1 |
| `harga` | int | Harga rumah (Rp) | 89.9M–714.6M |

### 6 Kecamatan dalam Dataset

1. Majalengka
2. Jatiwangi
3. Kertajati
4. Sumberjaya
5. Ligung
6. Argapura

---

## 8. Model Machine Learning

### Algoritma: Random Forest Regressor

Random Forest adalah algoritma ensemble yang membangun **100 pohon keputusan (decision tree)** secara paralel. Setiap pohon dilatih pada subset data yang berbeda (bagging), lalu hasil prediksi semua pohon dirata-ratakan.

### Cara Kerja

```
Input fitur (10 fitur)
       │
       ▼
┌──────────────────────────┐
│    Random Forest          │
│    (100 Decision Trees)   │
│                           │
│  Tree 1: Rp 310M         │
│  Tree 2: Rp 320M         │
│  Tree 3: Rp 315M         │
│  ...                      │
│  Tree 100: Rp 305M       │
│                           │
│  Rata-rata = Rp 312M     │
│  Std Dev = Rp 25M        │
└──────────────────────────┘
       │
       ▼
Confidence Interval:
  Min = 312M - 1.96×25M = Rp 263M
  Max = 312M + 1.96×25M = Rp 361M
```

### Feature Engineering

Kolom kategorikal di-encode menggunakan **LabelEncoder**:

| Fitur | Encoding |
|-------|----------|
| `lokasi` | Argapura=0, Jatiwangi=1, Kertajati=2, Ligung=3, Majalengka=4, Sumberjaya=5 |
| `tipe_properti` | Mewah=0, Minimalis=1, Subsidi=2 |
| `kondisi` | Baru=0, Bekas=1 |

### Performa Model

| Metrik | Nilai |
|--------|-------|
| R² (train) | 0.9771 |
| R² (test) | 0.8139 |
| MAE (test) | Rp 38.640.515 |
| RMSE (test) | Rp 48.313.586 |
| **MAPE (test)** | **15.37%** |
| **🎯 Akurasi Prediksi** | **84.63%** |
| 5-Fold CV R² | 0.8117 ± 0.0543 |

> **Cara Menghitung Akurasi:**
> - MAPE (Mean Absolute Percentage Error) mengukur rata-rata error dalam bentuk persentase
> - Rumus: MAPE = (1/n) × Σ |actual - predicted| / |actual| × 100%
> - Akurasi = 100% - MAPE = 100% - 15.37% = **84.63%**
> - Artinya: rata-rata, prediksi harga meleset sebesar 15.37% dari harga sebenarnya

### Feature Importances

| Fitur | Importance | Keterangan |
|-------|-----------|------------|
| Luas Bangunan | 85.19% | **Faktor paling dominan** |
| Lokasi | 7.14% | Kecamatan berpengaruh |
| Luas Tanah | 2.10% | Pengaruh sedang |
| Usia Bangunan | 1.67% | Semakin tua, harga turun |
| Tipe Properti | 1.18% | Mewah > Minimalis > Subsidi |
| Lainnya | 3.63% | Kamar, garasi, kondisi |

---

## 9. API Flask (Microservice)

### Base URL: `http://127.0.0.1:5000`

### Endpoints

#### GET `/health`
Cek status API.

**Response:**
```json
{
  "status": "ok",
  "model": "RandomForestRegressor",
  "features": 10,
  "accuracy_pct": 84.63,
  "r2_test": 0.8139
}
```

#### GET `/model-info`
Metadata model untuk dashboard admin.

**Response:**
```json
{
  "n_estimators": 100,
  "n_features": 10,
  "accuracy_pct": 84.63,
  "r2_train": 0.9771,
  "r2_test": 0.8139,
  "mae": 38640515,
  "rmse": 48313586,
  "mape": 15.37,
  "cv_mean": 0.8117,
  "cv_std": 0.0543,
  "trained_at": "2026-06-16T12:10:13",
  "feature_importances": {"luas_bangunan": 0.8531, ...},
  "encoders": {"lokasi": ["Argapura", "Jatiwangi", ...], ...}
}
```

#### POST `/predict`
Prediksi harga rumah.

**Request Body:**
```json
{
  "tahun": 2024,
  "luas_tanah": 120,
  "luas_bangunan": 80,
  "kmr_tidur": 3,
  "kmr_mandi": 2,
  "usia": 5,
  "ada_garasi": 1,
  "lokasi": "Majalengka",
  "tipe_properti": "Minimalis",
  "kondisi": "Baru"
}
```

**Response:**
```json
{
  "price": 328938412,
  "min_price": 225907858,
  "max_price": 431968966,
  "confidence": 84,
  "category": "Menengah",
  "feature_importances": {"luas_bangunan": 0.8531, ...},
  "accuracy_pct": 84.63,
  "r2_test": 0.8139
}
```

#### POST `/retrain`
Latih ulang model (dipanggil dari admin).

**Request Body:**
```json
{
  "n_estimators": 100,
  "max_depth": 50,
  "test_size": 20
}
```

**Response:**
```json
{
  "status": "success",
  "r2_train": 0.9771,
  "r2_test": 0.8139,
  "mae": 38640515,
  "rmse": 48313586,
  "cv_mean": 0.8117,
  "cv_std": 0.0543,
  "mape": 15.37,
  "accuracy_pct": 84.63,
  "feature_importances": {...}
}
```

---

## 10. Aplikasi User (Laravel)

### URL: `http://127.0.0.1:8000`

### Halaman

| Route | Fungsi |
|-------|--------|
| `/` | Landing page |
| `/prediksi` | Form input prediksi harga |
| `/prediksi/hasil` | Hasil prediksi + feature importance |
| `/kalkulator-kpr` | Kalkulator angsuran KPR |

### Form Prediksi — Field yang Harus Diisi

| Field | Tipe Input | Validasi |
|-------|-----------|----------|
| Luas Tanah (m²) | number | required, min:1 |
| Luas Bangunan (m²) | number | required, min:1 |
| Kamar Tidur | number | required, min:1, max:10 |
| Kamar Mandi | number | required, min:1, max:5 |
| Kecamatan | select | 6 pilihan |
| Tipe Properti | select | Subsidi/Minimalis/Mewah |
| Kondisi | select | Baru/Bekas |
| Usia Bangunan | number | required, min:0, max:50 |
| Garasi | checkbox | opsional |

---

## 11. Aplikasi Admin (Laravel)

### URL: `http://127.0.0.1:8001`

### Login Admin

| Item | Keterangan |
|------|------------|
| **Email** | `admin@clovercode.com` |
| **Password** | `admin123` |

> Semua halaman admin dilindungi oleh autentikasi. Admin yang belum login akan otomatis diarahkan ke halaman `/login`.
>
> *Catatan Keamanan & Sesi:*
> * Sesi admin diset bertahan selama 30 hari (`43200` menit) di berkas `.env` dengan opsi *Ingat Saya (Remember Me)* tercentang secara default untuk mencegah kewajiban login berulang kali.
> * Tombol logout dilengkapi dengan konfirmasi peringatan sebelum keluar untuk menghindari ketidaksengajaan.

### Halaman

| Route | Fungsi |
|-------|--------|
| `/login` | Halaman login admin |
| `/dashboard` | Overview statistik, akurasi model, & log prediksi |
| `/dataset` | Kelola dataset + upload Excel |
| `/settings` | Pengaturan model & retrain |
| `/guide` | Panduan penggunaan & dokumentasi teori/metrik ML |

### Fitur Admin

1. **Dashboard** — Statistik real-time:
   - Total dataset (baris data)
   - **Akurasi model (MAPE %)** — persentase ketepatan prediksi
   - MAE (rata-rata error dalam Rupiah)
   - Total prediksi yang dilakukan user publik
   - Panel performa model (R² train/test, CV, MAPE, jumlah pohon)
   - Feature importance bar chart
   - Distribusi lokasi
   - Log prediksi terbaru dengan badge confidence
2. **Dataset Management** — Tabel properti dengan pagination & search, upload file Excel baru, hapus data individual
3. **Model Settings** — Atur hyperparameter (n_estimators, max_depth, test_size), retrain model langsung dari browser, konsol output real-time, perbandingan R² sebelum/sesudah retrain
4. **Panduan Penggunaan & Dokumentasi** — Halaman interaktif (berbasis tab) yang mendokumentasikan ikhtisar dashboard (metrik evaluasi akademis), alur pelatihan ulang model, landasan teori algoritma Random Forest Regressor, serta panduan fitur publik.

---

## 12. Database

### Tabel `properties`

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | INTEGER (auto) | Primary key |
| tahun | INTEGER | Tahun data |
| luas_tanah | INTEGER | Luas tanah (m²) |
| luas_bangunan | INTEGER | Luas bangunan (m²) |
| kmr_tidur | INTEGER | Jumlah kamar tidur |
| kmr_mandi | INTEGER | Jumlah kamar mandi |
| usia | INTEGER | Usia bangunan |
| lokasi | VARCHAR | Nama kecamatan |
| tipe_properti | VARCHAR | Subsidi/Minimalis/Mewah |
| kondisi | VARCHAR | Baru/Bekas |
| ada_garasi | BOOLEAN | Ada garasi (0/1) |
| harga | BIGINT | Harga rumah (Rp) |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

### Tabel `predictions`

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | INTEGER (auto) | Primary key |
| input_data | JSON | Semua input user (fitur) |
| predicted_price | BIGINT | Harga prediksi |
| min_price | BIGINT | Batas bawah interval |
| max_price | BIGINT | Batas atas interval |
| confidence | INTEGER | Skor kepercayaan (%) |
| category | VARCHAR | Subsidi/Menengah/Mewah |
| feature_importances | JSON | Bobot fitur saat prediksi |
| created_at | TIMESTAMP | Waktu prediksi |

---

## 13. Troubleshooting

### "Gagal menghubungi model prediksi"
→ **Flask API tidak berjalan**. Jalankan `python3 ml/api.py &` di folder `UI-peedik-rumah`

### "Address already in use" saat serve
→ Port sudah terpakai. Matikan proses lama:
```bash
fuser -k 5000/tcp 8000/tcp 8001/tcp
```

### "ModuleNotFoundError" di Python
→ Install dependency Python:
```bash
python3 -m pip install pandas scikit-learn flask flask-cors openpyxl joblib --break-system-packages
```

### Model accuracy rendah setelah retrain
→ Pastikan dataset cukup besar (minimal 100 baris). Coba naikkan `n_estimators` (200-300) di halaman Settings.

### Admin tidak bisa lihat data
→ Pastikan `DB_DATABASE` di `.env` admin app mengarah ke file SQLite yang benar (path absolut, harus di-quote jika ada spasi).

### Upload dataset gagal
→ Pastikan file Excel menggunakan format `.xlsx` dan memiliki header kolom yang sesuai (tahun, luas_tanah, luas_bangunan, kmr_tidur, kmr_mandi, usia, lokasi, tipe_properti, kondisi, ada_garasi, harga).

---

> **Catatan**: Sistem ini dibuat untuk Tugas Akhir Universitas Muhammadiyah Cirebon (UMC). Untuk deployment production, disarankan menggunakan Gunicorn (Flask) + Nginx (reverse proxy) + MySQL/PostgreSQL (database).
