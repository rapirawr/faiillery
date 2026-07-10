Saya ingin membuat fitur CMS (Content Management System) di project Laravel saya. 
Berikut spesifikasinya:

FITUR CMS YANG DIBUTUHKAN
Buatkan modul CMS untuk mengelola konten berikut:
1. Halaman/Page (judul, slug, konten (rich text), meta title, meta description, status publish/draft, thumbnail, color pallete, logo, title, navbar item)
2. Kategori (nama, slug)
3. Artikel/Post (judul, slug, konten, kategori, penulis, thumbnail, status, tanggal publish)
4. [tambahkan entitas lain jika perlu, misal: Banner, FAQ, Testimoni]

REQUIREMENT TEKNIS
- Buatkan migration untuk masing-masing tabel di atas, lengkap dengan kolom timestamps dan soft deletes.
- Buatkan Eloquent Model beserta relasi antar tabel (misal Post belongsTo Category).
- Buatkan Controller (resource controller) untuk operasi CRUD (Create, Read, Update, Delete) tiap entitas.
- Buatkan Form Request untuk validasi input (judul wajib, slug unik, dll).
- Slug dibuat otomatis dari judul (gunakan Str::slug atau package seperti spatie/laravel-sluggable).
- Buatkan seeder/factory untuk data dummy sebagai contoh.
- Gunakan Repository Pattern / Service Class untuk memisahkan logic dari Controller (opsional, sebutkan jika saya mau).
- Buatkan routing (web.php) dengan prefix /admin dan middleware auth untuk area admin.
- Tampilkan data di view menggunakan Blade (atau beri saya opsi jika saya pakai Livewire/Inertia+Vue/React).
- Sertakan fitur upload gambar/thumbnail (simpan ke storage/public, generate URL yang benar).
- Sertakan pagination dan fitur pencarian/filter data di halaman list admin.

OUTPUT YANG DIHARAPKAN
- Struktur folder & file yang perlu dibuat/diedit.
- Isi lengkap tiap file (migration, model, controller, request, route, view).
- Perintah artisan yang perlu saya jalankan (migrate, make:model, dsb).
- Penjelasan singkat alur datanya dari input form sampai tersimpan di database.

Tolong buatkan step-by-step dan kode lengkapnya, mulai dari migration sampai tampilan admin panel-nya.