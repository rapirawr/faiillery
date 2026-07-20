# Prompt: Restyle `photos.show` (Failerry) ala Instagram iOS

## Prompt siap pakai (copy-paste ke Claude Code / dev lain)

```
Restyle halaman detail foto Failerry (resources/views/photos/show.blade.php)
supaya tampilan dan interaksinya terasa seperti halaman detail post di
Instagram versi iOS.

Stack: Laravel Blade, Tailwind CSS, Alpine.js. Gambar disajikan lewat
PhotoController (proxy S3/Supabase). Pertahankan style frosted glass
iOS yang sudah diterapkan sebelumnya di file ini, jangan ditimpa dari nol
— lanjutkan/rapikan supaya konsisten dengan pola IG.

Requirement tampilan:
1. Header sticky: avatar bulat + username (bold, ukuran kecil) + lokasi
   opsional di bawah username (teks abu-abu kecil) + tombol "..." (opsi)
   di kanan. Frosted glass background (backdrop-blur), border-bottom tipis.
2. Media area: foto full-width, rasio dipertahankan (object-contain jika
   perlu), swipe/carousel kalau board berisi banyak foto (pakai Alpine
   x-data untuk index aktif + dot indicator di bawah foto seperti IG).
3. Action bar di bawah foto: ikon like (heart, toggle filled saat like,
   animasi scale saat tap), komentar (bubble), share/kirim, dan bookmark
   di ujung kanan. Semua ikon outline style ala SF Symbols (pakai
   heroicons outline, bukan solid, kecuali saat active state).
4. Like count: bold, format singkat (contoh 12.3K), di bawah action bar.
5. Caption: username bold diikuti teks caption, collapsible kalau > 2
   baris ("more" seperti IG) pakai Alpine x-show/x-collapse.
6. Preview komentar: 1-2 komentar teratas + link "Lihat semua N komentar".
7. Timestamp: huruf kecil, abu-abu, format relatif (2h, 3d) di bagian
   paling bawah caption/komentar.
8. Input komentar sticky di bawah viewport (mobile) dengan avatar kecil +
   input rounded-full + tombol "Kirim" yang disabled sampai ada teks.
9. Double-tap pada foto untuk like (Alpine @dblclick / touch handler) +
   munculkan animasi heart besar di tengah foto yang fade out (mirip IG).
10. Transisi: gunakan Alpine x-transition untuk modal komentar/opsi,
    durasi singkat (150-200ms), easing ease-out, konsisten dengan
    frosted glass yang sudah ada.

Requirement teknis:
- Semua interaksi (like, bookmark, buka komentar) tetap manggil endpoint
  Laravel yang sudah ada; jangan ubah kontrak API kecuali diminta.
- Optimistic UI update di Alpine (update angka like duluan, rollback
  kalau request gagal).
- Pastikan responsive: mobile-first, tapi tetap enak dilihat di desktop
  (foto di tengah dengan max-width, bukan full-bleed).
- Jangan pakai warna hardcoded acak — ikuti palet netral (putih/hitam/
  abu-abu) khas IG, aksen merah muda/gradient hanya untuk elemen story
  kalau relevan.
- Cek ulang dark mode kalau project ini sudah support dark mode.

Output yang diharapkan:
- File show.blade.php terupdate dengan struktur di atas.
- Kalau ada partial yang perlu dipecah (misal komentar, action bar),
  boleh dibuat partial baru di resources/views/photos/partials/.
- Tidak menyentuh PhotoController.php kecuali ada bug baru yang muncul
  akibat perubahan ini.
```

---

## Implementation Plan

### 1. Audit & Persiapan
- Review ulang `show.blade.php` versi sekarang (hasil restyle frosted
  glass sebelumnya) supaya perubahan baru ini nyambung, bukan nimpa.
- Cek apakah ada partial existing untuk header/action-bar/comment supaya
  tidak duplikasi.
- Pastikan Heroicons (atau icon set sejenis) sudah ter-install; kalau
  belum, tambahkan via CDN/npm sesuai yang dipakai project.

### 2. Struktur Komponen (pecah biar rapi)
- `partials/photo-header.blade.php` → avatar, username, lokasi, tombol opsi.
- `partials/photo-media.blade.php` → area foto + carousel + double-tap like.
- `partials/photo-actions.blade.php` → like/comment/share/bookmark + like count.
- `partials/photo-caption.blade.php` → caption collapsible + preview komentar.
- `partials/photo-comment-input.blade.php` → input komentar sticky.

### 3. State Management (Alpine.js)
- Satu `x-data` di root show.blade.php menyimpan: `liked`, `likeCount`,
  `bookmarked`, `activeSlide`, `showAllComments`, `captionExpanded`.
- Fungsi `toggleLike()`, `toggleBookmark()` → optimistic update +
  fetch ke endpoint Laravel, rollback kalau error (pakai try/catch).
- Fungsi `onDoubleTap()` → trigger like (kalau belum liked) + tampilkan
  animasi heart pakai `x-show` + `x-transition` + `setTimeout` hide.

### 4. Styling (Tailwind)
- Header: `sticky top-0 z-20 backdrop-blur-md bg-white/70 border-b
  border-gray-200/50`.
- Action icons: pakai heroicons outline 24px, gap konsisten (`gap-4`),
  active state ganti ke solid + warna (merah untuk like, kuning/hitam
  untuk bookmark).
- Caption: `text-sm`, username `font-semibold`, "more" trigger warna
  abu-abu.
- Comment input bar: `sticky bottom-0 backdrop-blur-md bg-white/80
  border-t border-gray-200/50`.

### 5. Interaksi & Animasi
- Like button tap → scale animation singkat (`transition-transform
  active:scale-90`).
- Carousel dots → update `activeSlide` on scroll/swipe (pakai Intersection
  Observer sederhana atau scroll snap + Alpine `x-on:scroll`).
- Collapsible caption pakai `x-show` + `max-height` transition atau
  Alpine Collapse plugin kalau sudah dipakai di project.

### 6. Integrasi Backend
- Pastikan route/endpoint like, bookmark, comment sudah ada; kalau
  belum, buat endpoint ringan (`POST /photos/{photo}/like`, dsb.) yang
  return JSON `{ liked, like_count }` untuk mendukung optimistic UI.
- Tidak mengubah `PhotoController.php` (image proxy) kecuali diperlukan.

### 7. Testing
- Test manual di mobile viewport (Chrome DevTools iPhone size) dan desktop.
- Test like/unlike, bookmark toggle, double-tap like, expand caption,
  buka semua komentar, kirim komentar baru.
- Cek regresi: pastikan nav bar & frosted glass style yang sudah dibuat
  sebelumnya tidak rusak (mengingat sempat ada isu alignment nav.blade.php
  akibat view cache — jalankan `php artisan view:clear` setelah setiap
  perubahan blade untuk menghindari hal serupa).

### 8. Rilis
- `php artisan view:clear` & `php artisan cache:clear` sebelum QA.
- Review responsive di beberapa breakpoint (375px, 768px, 1280px).
- Minta review desain sebelum merge kalau ada tim/reviewer lain.