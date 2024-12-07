# Satpam WP - Plugin WordPress Untuk Keamanan

Plugin ini adalah plugin sederhana untuk melindungi situs WordPress Anda dengan autentikasi dua faktor (2FA).

## Fitur Utama
- Menghasilkan kode QR untuk aplikasi autentikasi.
- Mendukung penyimpanan rahasia Google Authenticator untuk pengguna.
- Aktivasi dan deaktivasi Two-Factor Authentication (2FA) di pengaturan pengguna.

## Instalasi
1. **Unduh** dan **upload** plugin ke direktori /wp-content/plugins/ di situs WordPress Anda.
2. Aktifkan plugin melalui menu 'Plugin' di admin WordPress.
3. Plugin ini akan menambahkan pengaturan Two-Factor Authentication pada halaman pengaturan WordPress.

## Penggunaan
1. **Aktifkan Two-Factor Authentication**:
   - Masuk ke halaman pengaturan Two-Factor Authentication di dashboard WordPress Anda.
   - Jika belum ada rahasia Google Authenticator, klik 'Aktivasi 2FA' untuk menghasilkan rahasia dan kode QR.
   - Arahkan aplikasi autentikasi 2FA Anda untuk memindai kode QR atau masukkan rahasia secara manual.
   - Masukkan kode OTP yang dihasilkan aplikasi autentikasi untuk mengaktifkan 2FA.

2. **Nonaktifkan Two-Factor Authentication**:
   - Klik tombol 'Nonaktifkan 2FA' di pengaturan.
   - Konfirmasi dengan tombol 'Nonaktifkan 2FA' untuk menghapus rahasia dan mematikan 2FA.

## Lisensi
Plugin ini dilisensikan di bawah GPLv2.
