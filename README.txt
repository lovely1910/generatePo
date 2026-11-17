GENERATOR PURCHASE ORDER - PAKET XAMPP TANPA COMPOSER

CARA MENJALANKAN:
1. Ekstrak folder generator_po_php ke C:\xampp\htdocs\
2. Pastikan file ini ada: C:\xampp\htdocs\generator_po_php\public\index.php
3. Buka XAMPP Control Panel, lalu klik Start pada Apache.
4. Buka browser: http://localhost/generator_po_php/public/
5. Isi data PO, lalu klik Generate Word + PDF.

TIDAK PERLU COMPOSER / PHPWORD.

SYARAT:
- PHP bawaan XAMPP dengan ekstensi zip aktif.
  Jika muncul pesan ZipArchive belum aktif: buka C:\xampp\php\php.ini,
  cari ;extension=zip, hapus tanda titik koma sehingga menjadi extension=zip,
  simpan, lalu restart Apache.
- Untuk membuat PDF otomatis, install LibreOffice di Windows.
  Paket ini mencari LibreOffice pada lokasi instalasi Windows yang umum.
  Jika belum ada, install LibreOffice lalu restart Apache dan coba lagi.

CATATAN:
- File Word asli menjadi template. Tata letak, tabel, catatan, dan tanda tangan
  dipertahankan; sistem hanya mengganti bagian data yang disediakan.
- Hasil Word dan PDF akan diunduh bersama dalam satu file ZIP.
- Jika PDF gagal dibuat, Word masih bisa dibuka dengan Microsoft Word dan
  disimpan sebagai PDF secara manual.
