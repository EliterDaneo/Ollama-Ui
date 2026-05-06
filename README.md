# ChatBot UI — Laravel × Ollama

Aplikasi chat berbasis web yang menghubungkan antarmuka modern Bootstrap 5 dengan model AI lokal melalui [Ollama](https://ollama.com). Semua percakapan tersimpan di database per sesi anonim — tanpa login, tanpa akun.

---

## Fitur

- Antarmuka chat real-time dengan typing indicator
- Sesi anonim berbasis cookie (tidak perlu login)
- Avatar splash otomatis dari singkatan nama sesi dengan warna unik per pengguna
- Riwayat percakapan tersimpan di database dan tampil saat halaman dibuka kembali
- Konfigurasi model Ollama (model, temperature, system prompt) tersimpan di DB dan mudah diubah
- Hapus riwayat chat sekaligus bersihkan sesi dari DB
- Pure Bootstrap 5 + Bootstrap Icons — tanpa custom CSS framework tambahan

---

## Prasyarat

Pastikan sudah terinstal di mesin lokal:

| Tools                        | Versi minimum                      |
| ---------------------------- | ---------------------------------- |
| PHP                          | 8.2                                |
| Composer                     | 2.x                                |
| Laravel                      | 11.x                               |
| MySQL / SQLite               | —                                  |
| [Ollama](https://ollama.com) | terbaru                            |
| Model Ollama                 | `llama3.2:3b` (atau sesuai config) |

---

## Instalasi

### 1. Clone repositori

```bash
git clone https://github.com/username/chatbot-ui.git
cd chatbot-ui
```

### 2. Install dependensi PHP

```bash
composer install
```

### 3. Salin file environment

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Konfigurasi database

Edit `.env` sesuai koneksi database yang dipakai:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chatbot_ui
DB_USERNAME=root
DB_PASSWORD=
```

> Untuk penggunaan cepat tanpa setup MySQL, ganti ke `DB_CONNECTION=sqlite` dan buat file `database/database.sqlite`.

### 5. Jalankan migrasi

Migration akan membuat 3 tabel sekaligus menyeed konfigurasi Ollama default:

```bash
php artisan migrate
```

### 6. Jalankan Ollama

Pastikan Ollama berjalan di background dan model sudah diunduh:

```bash
ollama serve
ollama pull llama3.2:3b
```

### 7. Jalankan aplikasi

```bash
php artisan serve
```

Buka browser di `http://localhost:8000/chat`.

---

## Struktur file yang ditambahkan

```
app/
├── Http/Controllers/
│   └── ChatController.php      # index, store, destroy
└── Models/
    ├── OllamaConfig.php        # konfigurasi model Ollama
    ├── ChatSession.php         # sesi anonim + helper avatar
    └── ChatMessage.php         # pesan user & assistant

database/migrations/
    ├── ..._create_ollama_configs_table.php
    ├── ..._create_chat_sessions_table.php
    └── ..._create_chat_messages_table.php

resources/views/
    ├── layouts/app.blade.php   # layout utama Bootstrap 5
    └── chat.blade.php          # halaman chat

routes/web.php                  # GET /chat, POST /chat/message, DELETE /chat/session
```

---

## Skema database

```
ollama_configs          chat_sessions                  chat_messages
──────────────          ─────────────────────          ──────────────────────
id                      id                             id
model                   session_token (unique)         chat_session_id  → FK
base_url                guest_name                     role (user|assistant|system)
temperature             avatar_color                   content
top_p                   ollama_config_id  → FK         token_count
repeat_penalty          last_active_at                 response_ms
system_prompt           timestamps                     timestamps
is_active
timestamps
```

Penghapusan sesi otomatis membersihkan semua pesan terkait via `cascadeOnDelete()`.

---

## Konfigurasi Ollama

Konfigurasi model tersimpan di tabel `ollama_configs`. Nilai default di-seed saat migrasi:

| Parameter        | Default                  |
| ---------------- | ------------------------ |
| `model`          | `llama3.2:3b`            |
| `base_url`       | `http://localhost:11434` |
| `temperature`    | `0.7`                    |
| `top_p`          | `0.9`                    |
| `repeat_penalty` | `1.1`                    |

Untuk mengganti model, ubah langsung di database atau tambahkan seeder/command tersendiri.

---

## Routes

| Method   | URI             | Fungsi                                         |
| -------- | --------------- | ---------------------------------------------- |
| `GET`    | `/chat`         | Tampilkan halaman chat + riwayat sesi          |
| `POST`   | `/chat/message` | Kirim pesan, simpan ke DB, balasan dari Ollama |
| `DELETE` | `/chat/session` | Hapus sesi + semua pesan, reset cookie         |

---

## Lisensi

[MIT](https://opensource.org/licenses/MIT)
