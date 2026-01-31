# Setup Email untuk SIKOPMA

## Opsi 1: Menggunakan Sendmail Lokal (Recommended untuk Virtualmin)

Ini adalah opsi paling mudah karena server Anda sudah punya mail server lokal.

### Konfigurasi di `.env`:

```
MAIL_MAILER=sendmail
MAIL_FROM_ADDRESS=noreply@wirus.stis.ac.id
MAIL_FROM_NAME="SIKOPMA"
```

Hanya itu! Tidak perlu username/password karena menggunakan sendmail lokal.

---

## Opsi 2: Menggunakan SMTP Lokal (Jika Sendmail tidak bekerja)

### Konfigurasi di `.env`:

```
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=25
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=
MAIL_FROM_ADDRESS=noreply@wirus.stis.ac.id
MAIL_FROM_NAME="SIKOPMA"
```

---

## Opsi 3: Menggunakan Gmail (Jika ingin email eksternal)

### Langkah-langkah:

1. Buka https://myaccount.google.com/apppasswords
2. Pilih "Mail" dan "Windows Computer"
3. Google akan generate password 16 karakter
4. Copy password tersebut

### Konfigurasi di `.env`:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="SIKOPMA"
```

---

## Testing Email

Setelah konfigurasi, test dengan command:

```bash
php artisan tinker
Mail::raw('Test email', function($message) {
    $message->to('test@example.com');
});
```

Atau gunakan fitur "Send Test Email" di aplikasi jika ada.

---

## Rekomendasi

Gunakan **Opsi 1 (Sendmail)** karena:
- Tidak perlu konfigurasi username/password
- Email langsung terkirim dari server Anda
- Lebih aman dan reliable
- Sudah tersedia di Virtualmin
