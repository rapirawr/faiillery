@extends('layouts.admin')

@section('page-title', 'CMS Settings')
@section('page-subtitle', 'Kelola konten dan konfigurasi platform')

@section('content')

<form action="{{ route('admin.cms.update') }}" method="POST" x-data="{ activeTab: 'general' }">
    @csrf
    @method('PUT')

    {{-- Tab Nav --}}
    <div class="flex items-center gap-1 mb-6 p-1 rounded-xl w-fit" style="background:#F5E6CE;">
        @foreach([
            ['general',      'Umum',        'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
            ['homepage',     'Homepage',    'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['upload',       'Upload',      'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h14a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['social',       'Sosial',      'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ['registration', 'Registrasi',  'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'],
        ] as [$tab, $label, $icon])
        <button type="button" @click="activeTab = '{{ $tab }}'"
            :class="activeTab === '{{ $tab }}' ? 'bg-white shadow-sm text-espresso font-semibold' : 'text-caramel hover:text-espresso'"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm transition-all duration-200"
            style="font-weight:500;">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
            </svg>
            {{ $label }}
        </button>
        @endforeach
    </div>


    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Main Panel --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- TAB: General --}}
            <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="admin-card rounded-2xl p-6 space-y-5">
                    <h3 class="font-bold text-sm border-b pb-3" style="color:#3B2417;border-color:#F5E6CE;">Informasi Situs</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="cms-label">Nama Situs</label>
                            <input type="text" name="site_name" value="{{ $settings['site_name'] }}" class="admin-input" placeholder="Faiillery">
                        </div>
                        <div>
                            <label class="cms-label">Tagline</label>
                            <input type="text" name="site_tagline" value="{{ $settings['site_tagline'] }}" class="admin-input" placeholder="Abadikan Momenmu">
                        </div>
                    </div>

                    <div>
                        <label class="cms-label">Deskripsi Singkat</label>
                        <textarea name="site_description" rows="3" class="admin-input" style="resize:none;" placeholder="Deskripsi platform...">{{ $settings['site_description'] }}</textarea>
                    </div>

                    <div>
                        <label class="cms-label">Email Kontak</label>
                        <input type="email" name="contact_email" value="{{ $settings['contact_email'] }}" class="admin-input" placeholder="hello@Faiillery.com">
                    </div>

                    <div>
                        <label class="cms-label">Teks Footer</label>
                        <input type="text" name="footer_text" value="{{ $settings['footer_text'] }}" class="admin-input" placeholder="© {year} Faiillery. All rights reserved.">
                        <p class="text-xs mt-1" style="color:#C69C6D;">Gunakan <code class="px-1 rounded" style="background:#F5E6CE;">{year}</code> untuk tahun otomatis.</p>
                    </div>
                </div>

                <div class="admin-card rounded-2xl p-6 space-y-4 mt-6">
                    <h3 class="font-bold text-sm border-b pb-3" style="color:#3B2417;border-color:#F5E6CE;">Mode Maintenance</h3>
                    <x-cms-toggle name="maintenance_mode" :checked="$settings['maintenance_mode'] === '1'" label="Aktifkan Maintenance Mode" description="Situs hanya bisa diakses admin saat aktif." danger />
                    <div>
                        <label class="cms-label">Pesan Maintenance</label>
                        <textarea name="maintenance_message" rows="2" class="admin-input" style="resize:none;">{{ $settings['maintenance_message'] }}</textarea>
                    </div>
                </div>
            </div>


            {{-- TAB: Homepage --}}
            <div x-show="activeTab === 'homepage'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="admin-card rounded-2xl p-6 space-y-5">
                    <div class="flex items-center justify-between border-b pb-3" style="border-color:#F5E6CE;">
                        <h3 class="font-bold text-sm" style="color:#3B2417;">Hero Banner</h3>
                        <x-cms-toggle name="show_hero_banner" :checked="$settings['show_hero_banner'] === '1'" label="Tampilkan hero" inline />
                    </div>

                    <div>
                        <label class="cms-label">Judul Hero</label>
                        <input type="text" name="hero_title" value="{{ $settings['hero_title'] }}" class="admin-input" placeholder="Abadikan Setiap Momen">
                    </div>
                    <div>
                        <label class="cms-label">Subjudul Hero</label>
                        <textarea name="hero_subtitle" rows="2" class="admin-input" style="resize:none;" placeholder="Deskripsi singkat...">{{ $settings['hero_subtitle'] }}</textarea>
                    </div>
                    <div>
                        <label class="cms-label">Teks Tombol CTA</label>
                        <input type="text" name="hero_cta_text" value="{{ $settings['hero_cta_text'] }}" class="admin-input" placeholder="Mulai Sekarang">
                    </div>
                </div>
            </div>

            {{-- TAB: Upload --}}
            <div x-show="activeTab === 'upload'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="admin-card rounded-2xl p-6 space-y-5">
                    <h3 class="font-bold text-sm border-b pb-3" style="color:#3B2417;border-color:#F5E6CE;">Batasan Upload</h3>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="cms-label">Ukuran Maksimal (MB)</label>
                            <input type="number" name="max_upload_size_mb" value="{{ $settings['max_upload_size_mb'] }}" min="1" max="100" class="admin-input">
                        </div>
                        <div>
                            <label class="cms-label">Maks Foto per User</label>
                            <input type="number" name="max_photos_per_user" value="{{ $settings['max_photos_per_user'] }}" min="1" class="admin-input">
                        </div>
                    </div>

                    <div>
                        <label class="cms-label">Tipe File yang Diizinkan</label>
                        <input type="text" name="allowed_file_types" value="{{ $settings['allowed_file_types'] }}" class="admin-input" placeholder="jpg,jpeg,png,webp,gif">
                        <p class="text-xs mt-1" style="color:#C69C6D;">Pisahkan dengan koma. Contoh: jpg,png,webp</p>
                    </div>
                </div>

                <div class="admin-card rounded-2xl p-6 space-y-4 mt-6">
                    <h3 class="font-bold text-sm border-b pb-3" style="color:#3B2417;border-color:#F5E6CE;">Watermark</h3>
                    <x-cms-toggle name="watermark_enabled" :checked="$settings['watermark_enabled'] === '1'" label="Tambahkan watermark otomatis" description="Watermark diterapkan pada semua foto yang diupload." />
                    <div>
                        <label class="cms-label">Teks Watermark</label>
                        <input type="text" name="watermark_text" value="{{ $settings['watermark_text'] }}" class="admin-input" placeholder="Faiillery">
                    </div>
                </div>
            </div>


            {{-- TAB: Sosial --}}
            <div x-show="activeTab === 'social'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="admin-card rounded-2xl p-6 space-y-4">
                    <h3 class="font-bold text-sm border-b pb-3" style="color:#3B2417;border-color:#F5E6CE;">Fitur Sosial</h3>
                    <x-cms-toggle name="allow_comments"   :checked="$settings['allow_comments'] === '1'"   label="Komentar"  description="Izinkan user berkomentar pada foto." />
                    <x-cms-toggle name="allow_likes"      :checked="$settings['allow_likes'] === '1'"      label="Likes"     description="Izinkan user menyukai foto." />
                    <x-cms-toggle name="allow_follows"    :checked="$settings['allow_follows'] === '1'"    label="Follow"    description="Izinkan user mengikuti pengguna lain." />
                    <x-cms-toggle name="allow_messages"   :checked="$settings['allow_messages'] === '1'"   label="Pesan"     description="Izinkan fitur pesan langsung antar user." />
                    <x-cms-toggle name="allow_guest_view" :checked="$settings['allow_guest_view'] === '1'" label="Akses Tamu" description="Izinkan pengunjung tanpa akun melihat konten." />
                </div>
            </div>

            {{-- TAB: Registrasi --}}
            <div x-show="activeTab === 'registration'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="admin-card rounded-2xl p-6 space-y-5">
                    <h3 class="font-bold text-sm border-b pb-3" style="color:#3B2417;border-color:#F5E6CE;">Pengaturan Akun</h3>
                    <x-cms-toggle name="registration_open"  :checked="$settings['registration_open'] === '1'"  label="Registrasi Terbuka" description="Nonaktifkan untuk menutup pendaftaran akun baru." />
                    <x-cms-toggle name="email_verification" :checked="$settings['email_verification'] === '1'" label="Verifikasi Email"   description="Wajibkan verifikasi email sebelum akun aktif." />
                    <div>
                        <label class="cms-label">Pesan Selamat Datang</label>
                        <textarea name="welcome_message" rows="3" class="admin-input" style="resize:none;" placeholder="Pesan untuk user baru...">{{ $settings['welcome_message'] }}</textarea>
                    </div>
                </div>
            </div>

        </div>


        {{-- Sidebar --}}
        <div class="space-y-5">

            {{-- Save Card --}}
            <div class="admin-card rounded-2xl p-5 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-widest" style="color:#8B5E3C;">Aksi</h4>
                <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Perubahan
                </button>
                <button type="button" class="btn-ghost w-full flex items-center justify-center gap-2"
                    @click.prevent="window.appConfirm('Reset Semua', 'Reset semua pengaturan CMS ke nilai default?', () => { document.getElementById('reset-form').submit(); }, 'Reset', 'danger')">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Reset ke Default
                </button>
            </div>

            {{-- Status Overview --}}
            <div class="admin-card rounded-2xl p-5">
                <h4 class="text-xs font-bold uppercase tracking-widest mb-4" style="color:#8B5E3C;">Status Aktif</h4>
                <div class="space-y-3">
                    @foreach([
                        ['maintenance_mode', 'Maintenance Mode', true],
                        ['registration_open', 'Registrasi', false],
                        ['allow_comments', 'Komentar', false],
                        ['allow_messages', 'Pesan', false],
                        ['watermark_enabled', 'Watermark', false],
                        ['allow_guest_view', 'Akses Tamu', false],
                    ] as [$key, $label, $invertDanger])
                    @php
                        $active = $settings[$key] === '1';
                        $isDanger = $invertDanger ? $active : false;
                        $isGood   = $invertDanger ? !$active : $active;
                    @endphp
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium" style="color:#8B5E3C;">{{ $label }}</span>
                        <span class="badge {{ $active ? ($isDanger ? 'badge-pending' : 'badge-live') : 'badge-ended' }}">
                            {{ $active ? 'ON' : 'OFF' }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Info --}}
            <div class="glass-panel p-5">
                <h4 class="text-xs font-bold uppercase tracking-widest mb-3" style="color:#8B5E3C;">Info</h4>
                <ul class="space-y-2">
                    <li class="flex items-start gap-2 text-xs" style="color:#8B5E3C;">
                        <span class="w-1.5 h-1.5 rounded-full mt-1.5 flex-shrink-0" style="background:#8B5E3C;"></span>
                        Perubahan langsung aktif setelah disimpan.
                    </li>
                    <li class="flex items-start gap-2 text-xs" style="color:#8B5E3C;">
                        <span class="w-1.5 h-1.5 rounded-full mt-1.5 flex-shrink-0" style="background:#8B5E3C;"></span>
                        Maintenance mode hanya memblokir user biasa.
                    </li>
                    <li class="flex items-start gap-2 text-xs" style="color:#8B5E3C;">
                        <span class="w-1.5 h-1.5 rounded-full mt-1.5 flex-shrink-0" style="background:#8B5E3C;"></span>
                        Cache otomatis direset saat menyimpan.
                    </li>
                </ul>
            </div>

        </div>
    </div>

</form>

{{-- Reset form --}}
<form id="reset-form" action="{{ route('admin.cms.reset') }}" method="POST" class="hidden">
    @csrf
</form>

<style>
    .cms-label {
        display: block;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #8B5E3C;
        margin-bottom: 6px;
    }
    [x-cloak] { display: none !important; }
</style>

@endsection
