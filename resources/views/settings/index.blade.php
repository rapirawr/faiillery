@extends('layouts.app')

@section('title', 'Settings')

@section('content')
@php
 $userSettings = \App\Models\UserSetting::getAllForUser(auth()->id());
@endphp

<div class="max-w-7xl mx-auto px-4 lg:px-8 py-8 md:py-12" x-data="{
 tab: 'profile',
 search: '',
 isCompact: document.documentElement.classList.contains('compact-mode'),
 hasUnsavedChanges: false,
 showQrModal: false,
 backupCodes: [],
 showBackupCodes: false,
 scanningCleanup: false,
 cleanupDone: false,
 duplicatePhotosCount: 0,
 blurPhotosCount: 0,
 
 // Preferences state initialized with database values or sensible defaults
 settingsData: {
 upload_quality: '{{ $userSettings["upload_quality"] ?? "original" }}',
 max_file_size: parseInt('{{ $userSettings["max_file_size"] ?? "20" }}'),
 auto_convert: '{{ $userSettings["auto_convert"] ?? "webp" }}',
 watermark_enabled: '{{ $userSettings["watermark_enabled"] ?? "0" }}' === '1',
 watermark_position: '{{ $userSettings["watermark_position"] ?? "bottom-right" }}',
 watermark_opacity: parseInt('{{ $userSettings["watermark_opacity"] ?? "50" }}'),
 auto_backup: '{{ $userSettings["auto_backup"] ?? "0" }}' === '1',
 auto_compress: '{{ $userSettings["auto_compress"] ?? "1" }}' === '1',
 
 profile_visibility: '{{ $userSettings["profile_visibility"] ?? "public" }}',
 photo_visibility: '{{ $userSettings["photo_visibility"] ?? "everyone" }}',
 allow_original_download: '{{ $userSettings["allow_original_download"] ?? "1" }}' === '1',
 hide_exif: '{{ $userSettings["hide_exif"] ?? "1" }}' === '1',
 
 default_album_privacy: '{{ $userSettings["default_album_privacy"] ?? "public" }}',
 default_sort_order: '{{ $userSettings["default_sort_order"] ?? "newest" }}',
 auto_organize_date: '{{ $userSettings["auto_organize_date"] ?? "1" }}' === '1',
 auto_organize_location: '{{ $userSettings["auto_organize_location"] ?? "0" }}' === '1',
 auto_organize_faces: '{{ $userSettings["auto_organize_faces"] ?? "0" }}' === '1',
 
 share_link_active: '{{ $userSettings["share_link_active"] ?? "1" }}' === '1',
 share_link_expiry: '{{ $userSettings["share_link_expiry"] ?? "never" }}',
 share_link_password_protected: '{{ $userSettings["share_link_password_protected"] ?? "0" }}' === '1',
 collaborator_permission: '{{ $userSettings["collaborator_permission"] ?? "view_only" }}',
 shared_album_invite: '{{ $userSettings["shared_album_invite"] ?? "owner_only" }}',
 
 notif_email_likes: '{{ $userSettings["notif_email_likes"] ?? "1" }}' === '1',
 notif_email_comments: '{{ $userSettings["notif_email_comments"] ?? "1" }}' === '1',
 notif_email_follows: '{{ $userSettings["notif_email_follows"] ?? "1" }}' === '1',
 notif_email_storage_full: '{{ $userSettings["notif_email_storage_full"] ?? "1" }}' === '1',
 notif_push_likes: '{{ $userSettings["notif_push_likes"] ?? "0" }}' === '1',
 notif_push_comments: '{{ $userSettings["notif_push_comments"] ?? "0" }}' === '1',
 notif_push_follows: '{{ $userSettings["notif_push_follows"] ?? "0" }}' === '1',
 notif_push_storage_full: '{{ $userSettings["notif_push_storage_full"] ?? "1" }}' === '1',
 notif_inapp_likes: '{{ $userSettings["notif_inapp_likes"] ?? "1" }}' === '1',
 notif_inapp_comments: '{{ $userSettings["notif_inapp_comments"] ?? "1" }}' === '1',
 notif_inapp_follows: '{{ $userSettings["notif_inapp_follows"] ?? "1" }}' === '1',
 notif_inapp_storage_full: '{{ $userSettings["notif_inapp_storage_full"] ?? "1" }}' === '1',
 notif_summary_frequency: '{{ $userSettings["notif_summary_frequency"] ?? "weekly" }}',
 
 two_fa_enabled: '{{ $userSettings["two_fa_enabled"] ?? "0" }}' === '1',
 blocked_users: '{{ $userSettings["blocked_users"] ?? "" }}',
 },
 
 originalSettingsData: {},
 
 init() {
 this.originalSettingsData = JSON.parse(JSON.stringify(this.settingsData));
 this.$watch('settingsData', value => {
 this.hasUnsavedChanges = JSON.stringify(value) !== JSON.stringify(this.originalSettingsData);
 }, { deep: true });
 
 if (window.location.hash) {
 const hash = window.location.hash.substring(1);
 if (['profile', 'account', 'security', 'privacy', 'upload', 'storage', 'albums', 'sharing', 'notifications', 'display', 'data', 'billing', 'app', 'danger'].includes(hash)) {
 this.tab = hash;
 }
 }
 },
 
 changeTab(newTab) {
 if (this.hasUnsavedChanges) {
 if (confirm('Anda memiliki perubahan yang belum disimpan. Apakah Anda ingin mengabaikan perubahan tersebut?')) {
 this.settingsData = JSON.parse(JSON.stringify(this.originalSettingsData));
 this.hasUnsavedChanges = false;
 this.tab = newTab;
 window.location.hash = newTab;
 }
 } else {
 this.tab = newTab;
 window.location.hash = newTab;
 }
 },
 
 savePreferences() {
 const payload = {
 settings: Object.keys(this.settingsData).map(key => ({
 key: key,
 value: typeof this.settingsData[key] === 'boolean' ? (this.settingsData[key] ? '1' : '0') : this.settingsData[key].toString()
 }))
 };
 
 axios.post('{{ route('settings.preferences') }}', payload)
 .then(res => {
 if (res.data.success) {
 window.showToast('Pengaturan berhasil disimpan!');
 this.originalSettingsData = JSON.parse(JSON.stringify(this.settingsData));
 this.hasUnsavedChanges = false;
 }
 })
 .catch(err => {
 window.showToast('Gagal menyimpan pengaturan.', 'error');
 });
 },
 
 exportData(type) {
 axios.post('{{ route('settings.export') }}', { type })
 .then(res => {
 window.showToast(res.data.message);
 })
 .catch(err => {
 window.showToast('Gagal memproses ekspor data.', 'error');
 });
 },
 
 startCleanup() {
 this.scanningCleanup = true;
 setTimeout(() => {
 this.scanningCleanup = false;
 this.cleanupDone = true;
 window.showToast('Pembersihan berhasil dilakukan! Storage lega.');
 }, 3000);
 },
 
 toggle2FA() {
 if (this.settingsData.two_fa_enabled) {
 // disable
 this.settingsData.two_fa_enabled = false;
 this.savePreferences();
 } else {
 // show QR modal to enable
 this.showQrModal = true;
 }
 },
 
 confirm2FA() {
 this.settingsData.two_fa_enabled = true;
 this.showQrModal = false;
 this.savePreferences();
 this.showBackupCodes = true;
 },
 
 categories: [
 { id: 'profile', name: 'Public Profile', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
 { id: 'account', name: 'Account Settings', icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
 { id: 'security', name: 'Security & 2FA', icon: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' },
 { id: 'privacy', name: 'Privacy Settings', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
 { id: 'upload', name: 'Upload & Quality', icon: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12' },
 { id: 'storage', name: 'Storage & Quota', icon: 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z' },
 { id: 'albums', name: 'Album & Collections', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
 { id: 'sharing', name: 'Sharing & Collab', icon: 'M8.684 10.742l4.636-2.318a3 3 0 100-4.848l-4.636 2.318a3 3 0 110 4.848zM15 12a3 3 0 11-6 0 3 3 0 016 0z' },
 { id: 'notifications', name: 'Notifications', icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' },
 { id: 'display', name: 'Display & Design', icon: 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z' },
 { id: 'data', name: 'Data & Export', icon: 'M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
 { id: 'billing', name: 'Billing & Plans', icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' },
 { id: 'app', name: 'PWA Install', icon: 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z' },
 { id: 'danger', name: 'Danger Zone', icon: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16', isDanger: true }
 ],
 
 get filteredCategories() {
 if (!this.search) return this.categories;
 return this.categories.filter(c => c.name.toLowerCase().includes(this.search.toLowerCase()));
 }
}">

 <!-- Unsaved Changes Floating Bar -->
 <div x-show="hasUnsavedChanges" 
 x-transition:enter="transition ease-out duration-300 transform" 
 x-transition:enter-start="translate-y-20 opacity-0" 
 x-transition:enter-end="translate-y-0 opacity-100" 
 x-transition:leave="transition ease-in duration-200 transform" 
 x-transition:leave-start="translate-y-0 opacity-100" 
 x-transition:leave-end="translate-y-20 opacity-0"
 class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-brown text-cream px-6 py-4 rounded-2xl shadow-xl flex items-center gap-6 border border-white/10 max-w-lg w-[90%] md:w-auto">
 <div class="flex items-center gap-2">
 <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
 <span class="text-sm font-semibold">Ada perubahan yang belum disimpan</span>
 </div>
 <div class="flex items-center gap-2 ml-auto">
 <button @click="settingsData = JSON.parse(JSON.stringify(originalSettingsData)); hasUnsavedChanges = false; window.showToast('Perubahan dibatalkan.')" 
 class="px-4 py-2 hover:bg-white/10 rounded-xl text-xs font-bold transition-all">
 Batal
 </button>
 <button @click="savePreferences()" 
 class="px-5 py-2 bg-brown hover:bg-brown text-white rounded-xl text-xs font-bold transition-all shadow-md">
 Simpan Preferensi
 </button>
 </div>
 </div>

 <div class="flex flex-col md:flex-row gap-8 lg:gap-12">
 
 {{-- ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ Sidebar Navigation ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ --}}
 <aside class="w-full md:w-64 lg:w-72 flex-shrink-0">
 <h1 class="text-3xl font-display font-black mb-6 px-2 tracking-tight">Settings</h1>
 
 <!-- Search settings bar -->
 <div class="mb-4 px-2">
 <div class="relative">
 <input type="text" x-model="search" placeholder="Cari setting..." 
 class="w-full pl-10 pr-4 py-2.5 bg-cream border border-sand/10 rounded-xl text-sm focus:ring-2 focus:ring-sand focus:border-sand transition-all">
 <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-caramel" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
 </div>
 </div>

 <!-- Categories list -->
 <nav class="flex flex-row md:flex-col gap-1 overflow-x-auto md:overflow-visible pb-4 md:pb-0 scrollbar-hide">
 <template x-for="c in filteredCategories" :key="c.id">
 <button @click="changeTab(c.id)" 
 :class="[
 tab === c.id 
 ? (c.isDanger ? 'bg-red-500/10 text-red-500 font-bold' : 'bg-cocoa/5 text-cocoa font-bold') 
 : 'text-caramel hover:text-cocoa hover:bg-cocoa/5 ',
 c.isDanger && tab !== c.id ? 'hover:bg-red-500/5 hover:text-red-500' : '' ]"
 class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 whitespace-nowrap text-left text-sm md:w-full">
 <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="c.icon"></path>
 </svg>
 <span x-text="c.name"></span>
 </button>
 </template>
 </nav>
 </aside>

 {{-- ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ Main Content ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ --}}
 <main class="flex-1 min-w-0 bg-cream border border-sand/5 rounded-3xl p-6 md:p-8 shadow-sm min-h-[500px]">
 
 {{-- A. Akun & Profil --}}
 <section x-show="tab === 'profile'" x-transition:enter="transition ease-out duration-200" class="space-y-6">
 <header class="mb-6">
 <h2 class="text-xl font-bold mb-1">Public Profile</h2>
 <p class="text-caramel text-sm">Informasi ini akan terlihat oleh semua orang di Faiillery.</p>
 </header>
 
 <form id="profile-form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
 @csrf
 @method('patch')

 <div class="flex flex-col gap-6">
 <div class="relative group">
 <label class="block text-sm font-semibold mb-3">Avatar</label>
 <div class="flex items-center gap-6">
 <div class="relative">
 <img id="avatar-preview" src="{{ $user->avatar ? Storage::disk('s3')->url($user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=E60023&color=fff' }}" 
 alt="{{ $user->name }}" 
 class="w-24 h-24 rounded-full object-cover border-4 border-sand/5 shadow-lg">
 <label for="avatar_input" class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-full opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer text-white">
 <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
 </label>
 <input type="file" id="avatar_input" name="avatar" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*"
 onchange="const file = this.files[0]; if(file){ const reader = new FileReader(); reader.onload = (e) => { document.getElementById('avatar-preview').src = e.target.result; }; reader.readAsDataURL(file); }">
 </div>
 <div>
 <div class="text-xs text-caramel leading-relaxed max-w-[200px]">
 Pilih foto profil terbaikmu. Disarankan minimal 400x400px.
 </div>
 @error('avatar')
 <p class="text-xs text-red-500 mt-2">{{ $message }}</p>
 @enderror
 </div>
 </div>
 </div>

 <div class="relative group">
 <label class="block text-sm font-semibold mb-3">Cover Photo</label>
 <div class="relative w-full h-32 rounded-2xl overflow-hidden border-2 border-dashed border-sand/10 group-hover:border-primary/50 transition-all">
 <img id="cover-preview" src="{{ $user->cover_photo ? Storage::disk('s3')->url($user->cover_photo) : '' }}" 
 class="w-full h-full object-cover {{ $user->cover_photo ? '' : 'hidden' }}">
 <div id="cover-placeholder" class="absolute inset-0 flex flex-col items-center justify-center text-caramel {{ $user->cover_photo ? 'hidden' : '' }}">
 <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h14a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
 <span class="text-xs font-medium">Klik untuk upload cover</span>
 </div>
 <label for="cover_input" class="absolute inset-0 cursor-pointer"></label>
 <input type="file" id="cover_input" name="cover_photo" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*"
 onchange="const file = this.files[0]; if(file){ const reader = new FileReader(); reader.onload = (e) => { document.getElementById('cover-preview').src = e.target.result; document.getElementById('cover-preview').classList.remove('hidden'); document.getElementById('cover-placeholder').classList.add('hidden'); }; reader.readAsDataURL(file); }">
 </div>
 </div>

 <div class="space-y-2">
 <label class="block text-sm font-semibold">Name</label>
 <input type="text" name="name" value="{{ old('name', $user->name) }}" 
 class="w-full px-4 py-3 bg-cocoa/5 border-none rounded-xl focus:ring-2 focus:ring-sand transition-all text-sm">
 @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
 </div>

 <div class="space-y-2">
 <label class="block text-sm font-semibold">Username</label>
 <div class="relative">
 <span class="absolute left-4 top-1/2 -translate-y-1/2 text-caramel text-sm">@</span>
 <input type="text" name="username" value="{{ old('username', $user->username) }}" 
 class="w-full pl-8 pr-4 py-3 bg-cocoa/5 border-none rounded-xl focus:ring-2 focus:ring-sand transition-all text-sm">
 </div>
 @error('username') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
 </div>

 <div class="space-y-2">
 <label class="block text-sm font-semibold">Bio</label>
 <textarea name="bio" rows="4" 
 class="w-full px-4 py-3 bg-cocoa/5 border-none rounded-xl focus:ring-2 focus:ring-sand transition-all resize-none text-sm"
 placeholder="Ceritakan sedikit tentang dirimu...">{{ old('bio', $user->bio) }}</textarea>
 @error('bio') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
 </div>
 </div>

 <div class="pt-6 border-t border-sand/5 flex justify-end">
 <button type="submit" class="px-8 py-3 bg-brown hover:bg-brown text-white font-bold rounded-full transition-all shadow-lg shadow-warm text-sm">
 Save Changes
 </button>
 </div>
 </form>
 </section>
 
 {{-- Account Settings --}}
 <section x-show="tab === 'account'" x-transition:enter="transition ease-out duration-200" class="space-y-6">
 <header class="mb-6">
 <h2 class="text-xl font-bold mb-1">Account Settings</h2>
 <p class="text-caramel text-sm">Kelola informasi dasar akun dan verifikasi email.</p>
 </header>
 
 <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
 @csrf
 @method('patch')
 <input type="hidden" name="name" value="{{ $user->name }}">
 <input type="hidden" name="username" value="{{ $user->username }}">

 <div class="space-y-2">
 <label class="block text-sm font-semibold">Email Address</label>
 <input type="email" name="email" value="{{ old('email', $user->email) }}" 
 class="w-full px-4 py-3 bg-cocoa/5 border-none rounded-xl focus:ring-2 focus:ring-sand transition-all text-sm">
 @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
 <div class="mt-2 text-sm">
 <p class="text-amber-500 font-medium">Email belum diverifikasi.</p>
 <button form="send-verification" class="text-espresso hover:underline font-semibold text-xs">Kirim ulang link verifikasi</button>
 </div>
 @endif
 @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
 </div>

 <div class="pt-6 border-t border-sand/5 flex justify-end">
 <button type="submit" class="px-8 py-3 bg-brown hover:bg-brown text-white font-bold rounded-full transition-all shadow-lg shadow-warm text-sm">
 Update Email
 </button>
 </div>
 </form>
 <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>
 </section>
 
 {{-- Security & 2FA --}}
 <section x-show="tab === 'security'" x-transition:enter="transition ease-out duration-200" class="space-y-8" 
 x-data="{
 newPassword: '',
 get passwordStrength() {
 if (!this.newPassword) return 0;
 let points = 0;
 if (this.newPassword.length >= 8) points++;
 if (/[A-Z]/.test(this.newPassword)) points++;
 if (/[0-9]/.test(this.newPassword)) points++;
 if (/[^A-Za-z0-9]/.test(this.newPassword)) points++;
 return points;
 }
 }">
 <header class="mb-6">
 <h2 class="text-xl font-bold mb-1">Security & Autentikasi</h2>
 <p class="text-caramel text-sm">Kelola keamanan akun dan sesi aktif.</p>
 </header>
 
 <!-- Password update form -->
 <form action="{{ route('password.update') }}" method="POST" class="space-y-6">
 @csrf
 @method('put')

 <div class="space-y-4">
 <h3 class="text-md font-bold">Ubah Password</h3>
 
 <div class="space-y-2">
 <label class="block text-sm font-semibold">Current Password</label>
 <input type="password" name="current_password" 
 class="w-full px-4 py-3 bg-cocoa/5 border-none rounded-xl focus:ring-2 focus:ring-sand transition-all text-sm">
 @error('current_password', 'updatePassword') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
 </div>

 <div class="space-y-2">
 <label class="block text-sm font-semibold">New Password</label>
 <input type="password" name="password" x-model="newPassword"
 class="w-full px-4 py-3 bg-cocoa/5 border-none rounded-xl focus:ring-2 focus:ring-sand transition-all text-sm">
 <!-- Password Strength Indicator -->
 <div class="mt-2" x-show="newPassword.length > 0">
 <div class="flex gap-1 h-1.5 w-full bg-cocoa/10 rounded-full overflow-hidden">
 <div class="h-full rounded-full transition-all text-sm" :class="passwordStrength >= 1 ? 'bg-red-500 w-1/4' : ''"></div>
 <div class="h-full rounded-full transition-all text-sm" :class="passwordStrength >= 2 ? 'bg-amber-500 w-1/4' : ''"></div>
 <div class="h-full rounded-full transition-all text-sm" :class="passwordStrength >= 3 ? 'bg-blue-500 w-1/4' : ''"></div>
 <div class="h-full rounded-full transition-all text-sm" :class="passwordStrength >= 4 ? 'bg-green-500 w-1/4' : ''"></div>
 </div>
 <span class="text-xs font-semibold mt-1 inline-block" :class="
 passwordStrength === 4 ? 'text-green-500' :
 passwordStrength === 3 ? 'text-blue-500' :
 passwordStrength === 2 ? 'text-amber-500' : 'text-red-500' " x-text="
 passwordStrength === 4 ? 'Password Sangat Kuat' :
 passwordStrength === 3 ? 'Password Kuat' :
 passwordStrength === 2 ? 'Password Sedang' : 'Password Lemah (Tambahkan simbol, angka, huruf besar/kecil)'
 "></span>
 </div>
 @error('password', 'updatePassword') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
 </div>

 <div class="space-y-2">
 <label class="block text-sm font-semibold">Confirm Password</label>
 <input type="password" name="password_confirmation" 
 class="w-full px-4 py-3 bg-cocoa/5 border-none rounded-xl focus:ring-2 focus:ring-sand transition-all text-sm">
 </div>
 </div>

 <div class="pt-4 border-t border-sand/5 flex justify-end">
 <button type="submit" class="px-8 py-3 bg-brown hover:bg-brown text-white font-bold rounded-full transition-all shadow-lg shadow-warm text-sm">
 Update Password
 </button>
 </div>
 </form>

 <div class="h-px bg-cocoa/5 my-6"></div>

 <!-- 2FA Section -->
 <div class="space-y-4">
 <div class="flex items-center justify-between">
 <div>
 <h3 class="text-md font-bold flex items-center gap-2">
 Autentikasi Dua Faktor (2FA)
 <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider animate-fade-in"
 :class="settingsData.two_fa_enabled ? 'bg-green-500/10 text-green-500' : 'bg-gray-300/20 text-caramel'">
 <span x-text="settingsData.two_fa_enabled ? 'Aktif' : 'Nonaktif'"></span>
 </span>
 </h3>
 <p class="text-xs text-caramel mt-1 max-w-lg leading-relaxed">
 Tambahkan keamanan ekstra pada akun Anda dengan mewajibkan kode verifikasi setiap kali Anda login.
 </p>
 </div>
 <button @click="toggle2FA()" 
 class="w-12 h-6 rounded-full relative transition-all shadow-sm border border-sand/15"
 :class="settingsData.two_fa_enabled ? 'bg-brown' : 'bg-soft-cream border border-sand/15'">
 <div class="absolute top-1 w-4 h-4 bg-cream rounded-full transition-all shadow-md animate-fade-in"
 :style="settingsData.two_fa_enabled ? 'left: 28px' : 'left: 4px'"></div>
 </button>
 </div>

 <!-- Backup Codes Block -->
 <div x-show="showBackupCodes" class="p-5 bg-cocoa/5 rounded-2xl space-y-3">
 <div class="flex items-center justify-between">
 <h4 class="text-sm font-bold text-amber-500">Backup Codes Keamanan</h4>
 <button @click="showBackupCodes = false" class="text-xs font-semibold text-caramel hover:text-white">Tutup</button>
 </div>
 <p class="text-xs text-caramel">Simpan kode cadangan berikut di tempat aman. Gunakan kode ini jika Anda kehilangan akses ke authenticator app.</p>
 <div class="grid grid-cols-2 gap-2 text-center font-mono text-sm py-2">
 <template x-for="code in backupCodes">
 <div class="bg-cream py-1.5 rounded-lg border border-sand/5 text-sm" x-text="code"></div>
 </template>
 </div>
 </div>
 </div>

 <div class="h-px bg-cocoa/5 my-6"></div>

 <!-- Active Sessions List (real data via AJAX) -->
 <div class="space-y-4" x-data="sessionManager()" x-init="loadSessions()">
 <div class="flex items-center justify-between">
 <h3 class="text-md font-bold">Daftar Sesi Login Aktif</h3>
 <button @click="revokeOthers()" class="text-xs font-bold text-red-500 hover:underline">Logout Semua Sesi Lain</button>
 </div>
 <div class="border border-sand/5 rounded-2xl overflow-hidden divide-y divide-dark/5">
 <!-- Loading state -->
 <div x-show="loading" class="flex justify-center items-center p-8 text-xs text-caramel gap-2">
 <svg class="animate-spin h-4 w-4 text-espresso" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
 Memuat sesi...
 </div>
 <!-- Empty state -->
 <div x-show="!loading && sessions.length === 0" class="p-8 text-center text-xs text-caramel">Tidak ada sesi aktif ditemukan.</div>
 <!-- Session rows -->
 <template x-for="session in sessions" :key="session.id">
 <div class="flex items-center justify-between p-4" :class="session.is_current ? 'bg-cocoa/5' : ''">
 <div class="flex items-center gap-3">
 <svg class="w-5 h-5 flex-shrink-0" :class="session.is_current ? 'text-espresso' : 'text-caramel'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
 <div>
 <div class="text-sm font-semibold" x-text="session.device_name + (session.is_current ? ' (Sesi Saat Ini)' : '')"></div>
 <div class="text-xs text-caramel" x-text="(session.location ?? 'Lokasi tidak diketahui') + ' ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¢ ' + session.ip_address + ' ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¢ ' + session.last_active"></div>
 </div>
 </div>
 <span x-show="session.is_current" class="text-xs text-green-500 font-bold bg-green-500/10 px-2 py-0.5 rounded-full uppercase">Aktif</span>
 <button x-show="!session.is_current" @click="revokeSession(session.id)" 
 class="text-xs font-semibold text-red-500 hover:bg-red-500/10 px-3 py-1.5 rounded-lg transition-all">Revoke</button>
 </div>
 </template>
 </div>
 </div>
 </section>

 {{-- Privacy Settings --}}
 <section x-show="tab === 'privacy'" x-transition:enter="transition ease-out duration-200" class="space-y-6">
 <header class="mb-6">
 <h2 class="text-xl font-bold mb-1">Privacy & Visibility</h2>
 <p class="text-caramel text-sm">Kontrol siapa yang bisa melihat profil dan aktivitasmu.</p>
 </header>

 <div class="space-y-6">
 <div class="flex items-center justify-between p-4 bg-cocoa/5 rounded-2xl">
 <div>
 <div class="font-semibold text-sm">Visibilitas Profil</div>
 <div class="text-xs text-caramel mt-0.5">Tentukan siapa yang dapat mengunjungi halaman profil Anda.</div>
 </div>
 <select x-model="settingsData.profile_visibility" class="bg-cream border border-sand/15 text-sm font-semibold focus:ring-2 focus:ring-sand rounded-lg px-2 py-1 cursor-pointer">
 <option value="public">Publik</option>
 <option value="friends">Hanya Teman</option>
 <option value="private">Private</option>
 </select>
 </div>

 <div class="flex items-center justify-between p-4 bg-cocoa/5 rounded-2xl">
 <div>
 <div class="font-semibold text-sm">Siapa yang dapat melihat foto Anda</div>
 <div class="text-xs text-caramel mt-0.5">Pengaturan visibilitas default untuk unggahan foto baru.</div>
 </div>
 <select x-model="settingsData.photo_visibility" class="bg-cream border border-sand/15 text-sm font-semibold focus:ring-2 focus:ring-sand rounded-lg px-2 py-1 cursor-pointer">
 <option value="everyone">Semua Orang</option>
 <option value="followers">Pengikut</option>
 <option value="only_me">Hanya Saya</option>
 </select>
 </div>

 <div class="flex items-center justify-between p-4 bg-cocoa/5 rounded-2xl">
 <div>
 <div class="font-semibold text-sm">Izinkan orang lain mengunduh foto asli</div>
 <div class="text-xs text-caramel mt-0.5">Tampilkan tombol unduh resolusi penuh di halaman foto.</div>
 </div>
 <button @click="settingsData.allow_original_download = !settingsData.allow_original_download" 
 class="w-12 h-6 rounded-full relative transition-all shadow-sm border border-sand/15"
 :class="settingsData.allow_original_download ? 'bg-brown' : 'bg-soft-cream border border-sand/15'">
 <div class="absolute top-1 w-4 h-4 bg-cream rounded-full transition-all shadow-md"
 :style="settingsData.allow_original_download ? 'left: 28px' : 'left: 4px'"></div>
 </button>
 </div>

 <div class="flex items-center justify-between p-4 bg-cocoa/5 rounded-2xl">
 <div>
 <div class="font-semibold text-sm">Sembunyikan Metadata EXIF</div>
 <div class="text-xs text-caramel mt-0.5">Hapus info lokasi GPS, model kamera, dan timestamp saat foto dibagikan.</div>
 </div>
 <button @click="settingsData.hide_exif = !settingsData.hide_exif" 
 class="w-12 h-6 rounded-full relative transition-all shadow-sm border border-sand/15"
 :class="settingsData.hide_exif ? 'bg-brown' : 'bg-soft-cream border border-sand/15'">
 <div class="absolute top-1 w-4 h-4 bg-cream rounded-full transition-all shadow-md"
 :style="settingsData.hide_exif ? 'left: 28px' : 'left: 4px'"></div>
 </button>
 </div>

 <div class="p-4 bg-cocoa/5 rounded-2xl space-y-3">
 <label class="block text-sm font-semibold">Pengguna yang Diblokir / Muted</label>
 <textarea x-model="settingsData.blocked_users" rows="2" 
 placeholder="Masukkan username dipisahkan koma (contoh: user1, user2)"
 class="w-full px-4 py-3 bg-cream border border-sand/5 rounded-xl focus:ring-2 focus:ring-sand transition-all resize-none text-sm"></textarea>
 </div>
 </div>
 </section>

 {{-- C. Upload & Kualitas Foto --}}
 <section x-show="tab === 'upload'" x-transition:enter="transition ease-out duration-200" class="space-y-6">
 <header class="mb-6">
 <h2 class="text-xl font-bold mb-1">Upload & Kualitas Foto</h2>
 <p class="text-caramel text-sm">Sesuaikan default resolusi upload, format, dan otomatisasi watermark.</p>
 </header>

 <div class="space-y-6">
 <div class="flex items-center justify-between p-4 bg-cocoa/5 rounded-2xl">
 <div>
 <div class="font-semibold text-sm">Kualitas Upload Default</div>
 <div class="text-xs text-caramel mt-0.5">Atur kompresi sebelum foto diunggah ke storage cloud.</div>
 </div>
 <select x-model="settingsData.upload_quality" class="bg-cream border border-sand/15 text-sm font-semibold focus:ring-2 focus:ring-sand rounded-lg px-2 py-1 cursor-pointer">
 <option value="original">Original (Tanpa Kompresi)</option>
 <option value="high">High Quality (Kompresi Ringan)</option>
 <option value="optimized">Optimized (Hemat Storage)</option>
 </select>
 </div>

 <div class="p-4 bg-cocoa/5 rounded-2xl space-y-2">
 <div class="flex justify-between text-sm">
 <span class="font-semibold">Batas Ukuran Maksimal File</span>
 <span class="font-mono text-espresso font-bold" x-text="settingsData.max_file_size + ' MB'"></span>
 </div>
 <input type="range" min="5" max="100" step="5" x-model="settingsData.max_file_size" 
 class="w-full accent-pinterest bg-soft-cream h-1.5 rounded-lg appearance-none cursor-pointer">
 </div>

 <div class="flex items-center justify-between p-4 bg-cocoa/5 rounded-2xl">
 <div>
 <div class="font-semibold text-sm">Auto-Convert Format</div>
 <div class="text-xs text-caramel mt-0.5">Konversi HEIC/PNG otomatis menjadi format modern.</div>
 </div>
 <select x-model="settingsData.auto_convert" class="bg-cream border border-sand/15 text-sm font-semibold focus:ring-2 focus:ring-sand rounded-lg px-2 py-1 cursor-pointer">
 <option value="webp">WebP (Disarankan)</option>
 <option value="jpeg">JPEG</option>
 <option value="none">No Conversion</option>
 </select>
 </div>

 <!-- Watermark Sub-section -->
 <div class="p-4 bg-cocoa/5 rounded-2xl space-y-4">
 <div class="flex items-center justify-between">
 <div>
 <div class="font-semibold text-sm">Beri Watermark Otomatis</div>
 <div class="text-xs text-caramel mt-0.5 font-medium">Bubuhi watermark logo/nama di setiap foto yang Anda upload.</div>
 </div>
 <button @click="settingsData.watermark_enabled = !settingsData.watermark_enabled" 
 class="w-12 h-6 rounded-full relative transition-all shadow-sm border border-sand/15"
 :class="settingsData.watermark_enabled ? 'bg-brown' : 'bg-soft-cream border border-sand/15'">
 <div class="absolute top-1 w-4 h-4 bg-cream rounded-full transition-all shadow-md"
 :style="settingsData.watermark_enabled ? 'left: 28px' : 'left: 4px'"></div>
 </button>
 </div>

 <div x-show="settingsData.watermark_enabled" x-collapse class="space-y-4 pt-2 border-t border-sand/5">
 <div class="grid grid-cols-2 gap-4">
 <div class="space-y-1">
 <label class="text-xs font-semibold text-caramel">Posisi Watermark</label>
 <select x-model="settingsData.watermark_position" class="w-full bg-cream border border-sand/10 rounded-xl text-xs py-2">
 <option value="bottom-right">Kanan Bawah</option>
 <option value="bottom-left">Kiri Bawah</option>
 <option value="top-right">Kanan Atas</option>
 <option value="top-left">Kiri Atas</option>
 <option value="center">Tengah</option>
 </select>
 </div>
 <div class="space-y-1">
 <div class="flex justify-between text-xs font-semibold text-caramel">
 <span>Opacity</span>
 <span x-text="settingsData.watermark_opacity + '%'"></span>
 </div>
 <input type="range" min="10" max="100" step="10" x-model="settingsData.watermark_opacity" 
 class="w-full accent-pinterest mt-2 bg-soft-cream h-1.5 rounded-lg appearance-none cursor-pointer">
 </div>
 </div>
 </div>
 </div>

 <div class="flex items-center justify-between p-4 bg-cocoa/5 rounded-2xl">
 <div>
 <div class="font-semibold text-sm">Auto-Backup ke Cloud External</div>
 <div class="text-xs text-caramel mt-0.5">Integrasikan backup otomatis ke Google Drive / Dropbox Anda.</div>
 </div>
 <button @click="settingsData.auto_backup = !settingsData.auto_backup" 
 class="w-12 h-6 rounded-full relative transition-all shadow-sm border border-sand/15"
 :class="settingsData.auto_backup ? 'bg-brown' : 'bg-soft-cream border border-sand/15'">
 <div class="absolute top-1 w-4 h-4 bg-cream rounded-full transition-all shadow-md"
 :style="settingsData.auto_backup ? 'left: 28px' : 'left: 4px'"></div>
 </button>
 </div>
 </div>
 </section>

 {{-- D. Storage & Kuota --}}
 <section x-show="tab === 'storage'" x-transition:enter="transition ease-out duration-200" class="space-y-6">
 <header class="mb-6">
 <h2 class="text-xl font-bold mb-1">Storage & Kuota</h2>
 <p class="text-caramel text-sm">Pantau kapasitas penyimpanan akun dan bersihkan file duplikat.</p>
 </header>

 <div class="space-y-6">
 @php
 $usedBytes = $storageUsage->used_bytes ?? 0;
 $quotaBytes = $storageUsage->quota_bytes ?? 26843545600;
 $pct = $quotaBytes > 0 ? round($usedBytes / $quotaBytes * 100, 1) : 0;
 $usedGB = number_format($usedBytes / 1073741824, 2);
 $quotaGB = number_format($quotaBytes / 1073741824, 1);
 @endphp
 <!-- Progress Bar storage (real data) -->
 <div class="p-6 bg-cocoa/5 rounded-3xl space-y-4">
 <div class="flex justify-between items-center text-sm">
 <span class="font-bold">Total Storage Terpakai</span>
 <span class="font-medium text-caramel font-semibold">{{ $usedGB }} GB / {{ $quotaGB }} GB ({{ $pct }}% Terpakai)</span>
 </div>
 
 <div class="w-full bg-soft-cream h-3 rounded-full overflow-hidden">
 <div class="h-full bg-brown transition-all" style="width: {{ $pct }}%"></div>
 </div>

 <div class="flex gap-4 text-xs font-semibold text-caramel">
 <div class="flex items-center gap-1.5">
 <span class="w-2.5 h-2.5 rounded-full bg-brown"></span> Digunakan ({{ $usedGB }} GB)
 </div>
 <div class="flex items-center gap-1.5">
 <span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span> Tersedia ({{ number_format(($quotaBytes - $usedBytes) / 1073741824, 2) }} GB)
 </div>
 </div>
 </div>

 <!-- Clean tools dashboard -->
 <div class="p-6 border border-sand/5 rounded-2xl space-y-4">
 <h3 class="font-bold text-sm">Pembersihan Storage Otomatis</h3>
 <p class="text-xs text-caramel">System mendeteksi beberapa foto buram dan file duplikat untuk membantu melegakan penyimpanan Anda.</p>
 
 <div class="grid grid-cols-2 gap-4 py-2" x-show="!cleanupDone">
 <div class="p-4 bg-cocoa/5 rounded-xl text-center">
 <span class="text-2xl font-black text-espresso" x-text="duplicatePhotosCount"></span>
 <span class="block text-[10px] text-caramel font-bold uppercase tracking-wider mt-1">Foto Duplikat</span>
 </div>
 <div class="p-4 bg-cocoa/5 rounded-xl text-center">
 <span class="text-2xl font-black text-amber-500" x-text="blurPhotosCount"></span>
 <span class="block text-[10px] text-caramel font-bold uppercase tracking-wider mt-1">Foto Buram/Blur</span>
 </div>
 </div>

 <div class="flex items-center justify-end gap-3 pt-2">
 <button @click="startCleanup()" :disabled="scanningCleanup"
 class="px-5 py-2.5 bg-brown hover:bg-brown text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
 <svg x-show="scanningCleanup" class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
 <span x-text="scanningCleanup ? 'Membersihkan...' : 'Bersihkan Sekarang'"></span>
 </button>
 </div>
 </div>

 <!-- Upgrade tier box -->
 <div class="p-6 bg-gradient-to-r from-primary/10 to-blue-500/10 border border-sand/20 rounded-[28px] flex items-center justify-between gap-6">
 <div>
 <h4 class="font-bold text-sm text-espresso">Storage Hampir Penuh?</h4>
 <p class="text-xs text-caramel mt-1 max-w-md">Tingkatkan kapasitas penyimpanan akun Anda hingga 1 TB untuk menyimpan seluruh kenangan tak terbatas.</p>
 </div>
 <button @click="tab = 'billing'" class="px-5 py-2.5 bg-brown text-white hover:bg-brown rounded-full text-xs font-bold shadow-lg transition-all">Upgrade Storage</button>
 </div>
 </div>
 </section>

 {{-- E. Album & Koleksi --}}
 <section x-show="tab === 'albums'" x-transition:enter="transition ease-out duration-200" class="space-y-6">
 <header class="mb-6">
 <h2 class="text-xl font-bold mb-1">Album & Koleksi</h2>
 <p class="text-caramel text-sm">Konfigurasi setelan default album baru dan pengorganisasian otomatis.</p>
 </header>

 <div class="space-y-6">
 <div class="flex items-center justify-between p-4 bg-cocoa/5 rounded-2xl">
 <div>
 <div class="font-semibold text-sm">Privasi Album Default</div>
 <div class="text-xs text-caramel mt-0.5">Tentukan privasi bawaan setiap kali Anda membuat Album baru.</div>
 </div>
 <select x-model="settingsData.default_album_privacy" class="bg-cream border border-sand/15 text-sm font-semibold focus:ring-2 focus:ring-sand rounded-lg px-2 py-1 cursor-pointer">
 <option value="public">Publik</option>
 <option value="shared">Terbagi (Kolaborasi)</option>
 <option value="private">Rahasia (Hanya Saya)</option>
 </select>
 </div>

 <div class="flex items-center justify-between p-4 bg-cocoa/5 rounded-2xl">
 <div>
 <div class="font-semibold text-sm">Urutan Default Foto</div>
 <div class="text-xs text-caramel mt-0.5">Pilih penyusunan foto otomatis dalam album.</div>
 </div>
 <select x-model="settingsData.default_sort_order" class="bg-cream border border-sand/15 text-sm font-semibold focus:ring-2 focus:ring-sand rounded-lg px-2 py-1 cursor-pointer">
 <option value="newest">Terbaru Ditambahkan</option>
 <option value="oldest">Terlama Ditambahkan</option>
 <option value="date_taken">Berdasarkan Tanggal Foto</option>
 <option value="manual">Manual (Drag & Drop)</option>
 </select>
 </div>

 <div class="p-4 bg-cocoa/5 rounded-2xl space-y-4">
 <h3 class="font-semibold text-sm">Pengorganisasian Cerdas (Face & Location grouping)</h3>
 <p class="text-xs text-caramel">Gunakan AI engine untuk mengelompokkan foto secara otomatis.</p>
 
 <div class="space-y-3 pt-2 border-t border-sand/5">
 <div class="flex items-center justify-between">
 <span class="text-xs font-semibold">Organisasi otomatis berdasarkan Tanggal</span>
 <button @click="settingsData.auto_organize_date = !settingsData.auto_organize_date" 
 class="w-12 h-6 rounded-full relative transition-all shadow-sm border border-sand/15"
 :class="settingsData.auto_organize_date ? 'bg-brown' : 'bg-soft-cream border border-sand/15'">
 <div class="absolute top-1 w-4 h-4 bg-cream rounded-full transition-all shadow-md"
 :style="settingsData.auto_organize_date ? 'left: 28px' : 'left: 4px'"></div>
 </button>
 </div>
 <div class="flex items-center justify-between">
 <span class="text-xs font-semibold">Organisasi otomatis berdasarkan Lokasi GPS</span>
 <button @click="settingsData.auto_organize_location = !settingsData.auto_organize_location" 
 class="w-12 h-6 rounded-full relative transition-all shadow-sm border border-sand/15"
 :class="settingsData.auto_organize_location ? 'bg-brown' : 'bg-soft-cream border border-sand/15'">
 <div class="absolute top-1 w-4 h-4 bg-cream rounded-full transition-all shadow-md"
 :style="settingsData.auto_organize_location ? 'left: 28px' : 'left: 4px'"></div>
 </button>
 </div>
 <div class="flex items-center justify-between">
 <span class="text-xs font-semibold">Face Grouping (Pengelompokkan Wajah)</span>
 <button @click="settingsData.auto_organize_faces = !settingsData.auto_organize_faces" 
 class="w-12 h-6 rounded-full relative transition-all shadow-sm border border-sand/15"
 :class="settingsData.auto_organize_faces ? 'bg-brown' : 'bg-soft-cream border border-sand/15'">
 <div class="absolute top-1 w-4 h-4 bg-cream rounded-full transition-all shadow-md"
 :style="settingsData.auto_organize_faces ? 'left: 28px' : 'left: 4px'"></div>
 </button>
 </div>
 </div>
 </div>
 </div>
 </section>

 {{-- F. Berbagi & Kolaborasi --}}
 <section x-show="tab === 'sharing'" x-transition:enter="transition ease-out duration-200" class="space-y-6">
 <header class="mb-6">
 <h2 class="text-xl font-bold mb-1">Berbagi & Kolaborasi</h2>
 <p class="text-caramel text-sm">Kelola pengaturan tautan berbagi dan perizinan kolaborator album.</p>
 </header>

 <div class="space-y-6">
 <div class="flex items-center justify-between p-4 bg-cocoa/5 rounded-2xl">
 <div>
 <div class="font-semibold text-sm">Aktifkan Link Berbagi</div>
 <div class="text-xs text-caramel mt-0.5 font-medium">Izinkan akses foto/album lewat link publik unik.</div>
 </div>
 <button @click="settingsData.share_link_active = !settingsData.share_link_active" 
 class="w-12 h-6 rounded-full relative transition-all shadow-sm border border-sand/15"
 :class="settingsData.share_link_active ? 'bg-brown' : 'bg-soft-cream border border-sand/15'">
 <div class="absolute top-1 w-4 h-4 bg-cream rounded-full transition-all shadow-md"
 :style="settingsData.share_link_active ? 'left: 28px' : 'left: 4px'"></div>
 </button>
 </div>

 <div x-show="settingsData.share_link_active" x-collapse class="space-y-4">
 <div class="flex items-center justify-between p-4 bg-cocoa/5 rounded-2xl">
 <div>
 <div class="font-semibold text-sm">Batas Kedaluwarsa Link Default</div>
 <div class="text-xs text-caramel mt-0.5">Link secara otomatis tidak dapat diakses setelah batas waktu.</div>
 </div>
 <select x-model="settingsData.share_link_expiry" class="bg-cream border border-sand/15 text-sm font-semibold focus:ring-2 focus:ring-sand rounded-lg px-2 py-1 cursor-pointer">
 <option value="never">Selamanya</option>
 <option value="7_days">7 Hari</option>
 <option value="30_days">30 Hari</option>
 <option value="24_hours">24 Jam</option>
 </select>
 </div>

 <div class="flex items-center justify-between p-4 bg-cocoa/5 rounded-2xl">
 <div>
 <div class="font-semibold text-sm">Proteksi Kata Sandi Link</div>
 <div class="text-xs text-caramel mt-0.5">Wajibkan pengunjung memasukkan password saat mengakses link.</div>
 </div>
 <button @click="settingsData.share_link_password_protected = !settingsData.share_link_password_protected" 
 class="w-12 h-6 rounded-full relative transition-all shadow-sm border border-sand/15"
 :class="settingsData.share_link_password_protected ? 'bg-brown' : 'bg-soft-cream border border-sand/15'">
 <div class="absolute top-1 w-4 h-4 bg-cream rounded-full transition-all shadow-md"
 :style="settingsData.share_link_password_protected ? 'left: 28px' : 'left: 4px'"></div>
 </button>
 </div>
 </div>

 <div class="flex items-center justify-between p-4 bg-cocoa/5 rounded-2xl">
 <div>
 <div class="font-semibold text-sm">Izin Kolaborator Album</div>
 <div class="text-xs text-caramel mt-0.5 font-medium font-semibold text-sm">Hak akses bawaan saat mengundang anggota ke album bersama.</div>
 </div>
 <select x-model="settingsData.collaborator_permission" class="bg-cream border border-sand/15 text-sm font-semibold focus:ring-2 focus:ring-sand rounded-lg px-2 py-1 cursor-pointer">
 <option value="view_only">View Only (Hanya Melihat)</option>
 <option value="can_upload">Can Upload (Bisa Unggah)</option>
 <option value="can_edit">Full Access (Bisa Edit & Hapus)</option>
 </select>
 </div>

 <div class="flex items-center justify-between p-4 bg-cocoa/5 rounded-2xl">
 <div>
 <div class="font-semibold text-sm">Hak Undang Anggota Shared Album</div>
 <div class="text-xs text-caramel mt-0.5">Siapa yang diperbolehkan menambah kolaborator baru.</div>
 </div>
 <select x-model="settingsData.shared_album_invite" class="bg-cream border border-sand/15 text-sm font-semibold focus:ring-2 focus:ring-sand rounded-lg px-2 py-1 cursor-pointer">
 <option value="owner_only">Hanya Pemilik Album</option>
 <option value="any_collaborator">Semua Kolaborator</option>
 </select>
 </div>
 </div>
 </section>

 {{-- G. Notifikasi --}}
 <section x-show="tab === 'notifications'" x-transition:enter="transition ease-out duration-200" class="space-y-6">
 <header class="mb-6">
 <h2 class="text-xl font-bold mb-1">Pengaturan Notifikasi</h2>
 <p class="text-caramel text-sm">Sesuaikan cara Anda menerima pemberitahuan dari Faiillery.</p>
 </header>

 <div class="space-y-6">
 <div class="border border-sand/5 rounded-2xl overflow-hidden divide-y divide-dark/5">
 <!-- Table header -->
 <div class="grid grid-cols-4 gap-2 p-4 bg-cocoa/5 text-xs font-bold uppercase tracking-wider text-caramel">
 <div class="col-span-2">Aktivitas</div>
 <div class="text-center">Email</div>
 <div class="text-center">Push / In-App</div>
 </div>

 <!-- Row Likes -->
 <div class="grid grid-cols-4 gap-2 p-4 items-center text-sm">
 <div class="col-span-2">
 <span class="font-semibold">Menyukai Foto</span>
 <span class="block text-xs text-caramel mt-0.5 font-medium">Beri tahu saya saat seseorang menyukai foto saya.</span>
 </div>
 <div class="flex justify-center">
 <input type="checkbox" x-model="settingsData.notif_email_likes" class="rounded text-espresso focus:ring-sand h-4 w-4 border border-sand bg-cream">
 </div>
 <div class="flex justify-center">
 <input type="checkbox" x-model="settingsData.notif_push_likes" class="rounded text-espresso focus:ring-sand h-4 w-4 border border-sand bg-cream">
 </div>
 </div>

 <!-- Row Comments -->
 <div class="grid grid-cols-4 gap-2 p-4 items-center text-sm">
 <div class="col-span-2">
 <span class="font-semibold">Komentar Baru</span>
 <span class="block text-xs text-caramel mt-0.5 font-medium">Beri tahu saya saat ada komentar di foto saya.</span>
 </div>
 <div class="flex justify-center">
 <input type="checkbox" x-model="settingsData.notif_email_comments" class="rounded text-espresso focus:ring-sand h-4 w-4 border border-sand bg-cream">
 </div>
 <div class="flex justify-center">
 <input type="checkbox" x-model="settingsData.notif_push_comments" class="rounded text-espresso focus:ring-sand h-4 w-4 border border-sand bg-cream">
 </div>
 </div>

 <!-- Row Follows -->
 <div class="grid grid-cols-4 gap-2 p-4 items-center text-sm">
 <div class="col-span-2">
 <span class="font-semibold">Pengikut Baru</span>
 <span class="block text-xs text-caramel mt-0.5 font-medium">Beri tahu saya saat seseorang mulai mengikuti saya.</span>
 </div>
 <div class="flex justify-center">
 <input type="checkbox" x-model="settingsData.notif_email_follows" class="rounded text-espresso focus:ring-sand h-4 w-4 border border-sand bg-cream">
 </div>
 <div class="flex justify-center">
 <input type="checkbox" x-model="settingsData.notif_push_follows" class="rounded text-espresso focus:ring-sand h-4 w-4 border border-sand bg-cream">
 </div>
 </div>

 <!-- Row Storage Alert -->
 <div class="grid grid-cols-4 gap-2 p-4 items-center text-sm">
 <div class="col-span-2">
 <span class="font-semibold">Kapasitas Storage Penuh</span>
 <span class="block text-xs text-caramel mt-0.5 font-medium">Kirim peringatan jika kuota penyimpanan menipis.</span>
 </div>
 <div class="flex justify-center">
 <input type="checkbox" x-model="settingsData.notif_email_storage_full" class="rounded text-espresso focus:ring-sand h-4 w-4 border border-sand bg-cream">
 </div>
 <div class="flex justify-center">
 <input type="checkbox" x-model="settingsData.notif_push_storage_full" class="rounded text-espresso focus:ring-sand h-4 w-4 border border-sand bg-cream">
 </div>
 </div>
 </div>

 <div class="flex items-center justify-between p-4 bg-cocoa/5 rounded-2xl">
 <div>
 <div class="font-semibold text-sm">Frekuensi Ringkasan Email</div>
 <div class="text-xs text-caramel mt-0.5">Kirim ringkasan aktivitas berkala ke kotak masuk Anda.</div>
 </div>
 <select x-model="settingsData.notif_summary_frequency" class="bg-cream border border-sand/15 text-sm font-semibold focus:ring-2 focus:ring-sand rounded-lg px-2 py-1 cursor-pointer">
 <option value="daily">Setiap Hari</option>
 <option value="weekly">Setiap Minggu</option>
 <option value="never">Jangan Pernah</option>
 </select>
 </div>
 </div>
 </section>

 {{-- H. Tampilan / Appearance --}}
 <section x-show="tab === 'display'" x-transition:enter="transition ease-out duration-200" class="space-y-6">
 <header class="mb-8">
 <h2 class="text-xl font-bold mb-1">Display Preferences</h2>
 <p class="text-caramel text-sm">Sesuaikan tampilan Faiillery agar nyaman di matamu.</p>
 </header>
 
 <div class="space-y-6">
 <div class="flex items-center justify-between p-4 bg-cocoa/5 rounded-2xl">
 <div>
 <div class="font-semibold text-sm">Dark Mode</div>
 <div class="text-xs text-caramel mt-0.5">Aktifkan tema gelap untuk kenyamanan mata.</div>
 </div>
 <button @click="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light')" 
 class="w-12 h-6 rounded-full bg-soft-cream relative transition-all animate-fade-in shadow-sm border border-sand/15">
 <div class="absolute top-1 left-1 w-4 h-4 bg-cream rounded-full transition-all shadow-md"></div>
 </button>
 </div>

 <div class="flex items-center justify-between p-4 bg-cocoa/5 rounded-2xl">
 <div>
 <div class="font-semibold text-sm">Compact View</div>
 <div class="text-xs text-caramel mt-0.5 font-medium">Tampilkan lebih banyak konten dengan margin yang lebih kecil.</div>
 </div>
 <button @click="isCompact = !isCompact; document.documentElement.classList.toggle('compact-mode', isCompact); localStorage.setItem('compact-mode', isCompact)" 
 class="w-12 h-6 rounded-full relative transition-all shadow-sm border border-sand/15"
 :class="isCompact ? 'bg-brown' : 'bg-soft-cream border border-sand/15'">
 <div class="absolute top-1 w-4 h-4 bg-cream rounded-full transition-all shadow-md"
 :style="isCompact ? 'left: 28px' : 'left: 4px'"></div>
 </button>
 </div>

 <div class="p-4 bg-cocoa/5 rounded-2xl space-y-4">
 <div class="font-semibold text-sm">Grid Density</div>
 <div class="grid grid-cols-3 gap-2">
 <button class="p-2.5 text-xs font-bold bg-brown text-white rounded-lg">Comfortable</button>
 <button class="p-2.5 text-xs font-bold bg-cocoa/5 text-caramel rounded-lg opacity-50 cursor-not-allowed">Standard</button>
 <button class="p-2.5 text-xs font-bold bg-cocoa/5 text-caramel rounded-lg opacity-50 cursor-not-allowed">Dense</button>
 </div>
 </div>

 <div class="flex items-center justify-between p-4 bg-cocoa/5 rounded-2xl">
 <div>
 <div class="font-semibold text-sm">Language</div>
 <div class="text-xs text-caramel mt-0.5">Pilih bahasa antarmuka aplikasi.</div>
 </div>
 <select class="bg-cream border border-sand/15 text-sm font-semibold focus:ring-2 focus:ring-sand rounded-lg px-2 py-1 cursor-pointer">
 <option>Bahasa Indonesia</option>
 <option>English</option>
 </select>
 </div>
 </div>
 </section>

 {{-- I. Data & Ekspor --}}
 <section x-show="tab === 'data'" x-transition:enter="transition ease-out duration-200" class="space-y-6">
 <header class="mb-6">
 <h2 class="text-xl font-bold mb-1">Data & Ekspor</h2>
 <p class="text-caramel text-sm">Ekspor foto Anda, unduh cadangan album, dan pantau log aktivitas.</p>
 </header>

 <div class="space-y-6">
 <div class="p-6 border border-sand/5 rounded-2xl space-y-4">
 <h3 class="font-bold text-sm">Ekspor Semua Data (Google Takeout)</h3>
 <p class="text-xs text-caramel leading-relaxed">
 Unduh seluruh foto original Anda, metadata JSON, dan riwayat aktivitas Anda dalam satu paket arsip berkas (.ZIP).
 </p>
 <div class="flex justify-end pt-2">
 <button @click="exportData('all')" 
 class="px-5 py-2.5 bg-brown text-white hover:bg-brown font-bold text-xs rounded-xl shadow-md transition-all">
 Request File Ekspor (.ZIP)
 </button>
 </div>
 </div>

 <div class="p-6 border border-sand/5 rounded-2xl space-y-4">
 <h3 class="font-bold text-sm">Ekspor Album & Koleksi</h3>
 <p class="text-xs text-caramel">Export daftar nama album, struktur, dan link foto dalam format metadata.</p>
 <div class="flex gap-2 justify-end">
 <button @click="exportData('json')" class="px-4 py-2 border border-sand/10 rounded-xl text-xs font-bold hover:bg-cocoa/5 transition-all">Export JSON</button>
 <button @click="exportData('csv')" class="px-4 py-2 border border-sand/10 rounded-xl text-xs font-bold hover:bg-cocoa/5 transition-all">Export CSV</button>
 </div>
 </div>

 <!-- Export Jobs History (real data) -->
 <div class="space-y-4">
 <h3 class="font-bold text-sm">Riwayat Permintaan Ekspor</h3>
 @if($exportJobs->isEmpty())
 <div class="border border-sand/5 rounded-2xl p-6 text-center text-xs text-caramel">
 Belum ada permintaan ekspor data.
 </div>
 @else
 <div class="border border-sand/5 rounded-2xl overflow-hidden divide-y divide-dark/5 text-xs">
 @foreach($exportJobs as $job)
 <div class="flex justify-between items-center p-4">
 <div>
 <div class="font-semibold">Ekspor Data ({{ strtoupper($job->status) }})</div>
 <div class="text-[10px] text-caramel mt-0.5">{{ $job->requested_at->format('d M Y, H:i') }}</div>
 </div>
 @if($job->status === 'completed' && $job->file_path)
 <a href="{{ Storage::url($job->file_path) }}" target="_blank"
 class="px-3 py-1.5 bg-brown text-white rounded-lg text-[10px] font-bold hover:bg-brown transition-all">
 Unduh
 </a>
 @elseif($job->status === 'pending' || $job->status === 'processing')
 <span class="px-2 py-0.5 bg-amber-500/10 text-amber-500 font-bold rounded-full">Diproses...</span>
 @else
 <span class="px-2 py-0.5 bg-red-500/10 text-red-500 font-bold rounded-full">Gagal</span>
 @endif
 </div>
 @endforeach
 </div>
 @endif
 </div>
 </div>
 </section>

 {{-- J. Billing & Langganan --}}
 <section x-show="tab === 'billing'" x-transition:enter="transition ease-out duration-200" class="space-y-6">
 <header class="mb-6">
 <h2 class="text-xl font-bold mb-1">Billing & Langganan</h2>
 <p class="text-caramel text-sm">Kelola rencana langganan cloud storage Anda dan riwayat pembayaran.</p>
 </header>

 <div class="space-y-6">
 <!-- Current Plan Status (no billing system yet) -->
 <div class="p-6 bg-cocoa/5 border border-sand/10 rounded-[28px] space-y-3">
 <div class="flex items-center gap-3">
 <div class="w-10 h-10 bg-brown/10 rounded-xl flex items-center justify-center">
 <svg class="w-5 h-5 text-espresso" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
 </div>
 <div>
 <div class="font-bold text-sm">Paket Free</div>
 <div class="text-xs text-caramel">Anda saat ini menggunakan paket gratis Faiillery.</div>
 </div>
 <span class="ml-auto px-3 py-1 bg-soft-cream text-caramel text-[10px] font-bold rounded-full uppercase tracking-wider">Aktif</span>
 </div>
 <p class="text-xs text-caramel leading-relaxed pt-1 border-t border-sand/5">
 Tingkatkan ke paket Pro untuk mendapatkan storage lebih besar, watermark kustom, dan fitur AI eksklusif.
 </p>
 <button @click="window.showToast('Fitur upgrade paket segera hadir!', 'success')" 
 class="w-full py-2.5 bg-brown hover:bg-brown text-white font-bold text-xs rounded-xl transition-all shadow-md">
 Lihat Paket Premium
 </button>
 </div>

 <!-- Payment history: empty state (no billing system) -->
 <div class="space-y-4">
 <h3 class="font-bold text-sm">Riwayat Invoice & Pembayaran</h3>
 <div class="border border-sand/5 rounded-2xl p-8 text-center text-xs text-caramel">
 Belum ada riwayat pembayaran.
 </div>
 </div>
 </div>
 </section>

 {{-- App Tab --}}
 <section x-show="tab === 'app'" x-transition:enter="transition ease-out duration-200" class="space-y-6">
 <header class="mb-8">
 <h2 class="text-xl font-bold mb-1">App & Installation</h2>
 <p class="text-caramel text-sm">Gunakan Faiillery sebagai aplikasi di perangkatmu.</p>
 </header>
 
 <div class="space-y-6">
 <div id="install-section" class="hidden flex flex-col md:flex-row items-center justify-between p-6 bg-brown/5 border border-sand/10 rounded-[28px] gap-6">
 <div class="flex items-center gap-4">
 <div class="w-14 h-14 bg-cream rounded-2xl flex items-center justify-center shadow-sm">
 <img src="/images/icon-512.png" class="w-10 h-10 rounded-lg">
 </div>
 <div>
 <div class="font-bold text-lg">Instal Faiillery App</div>
 <div class="text-sm text-caramel font-medium">Dapatkan akses cepat dan pengalaman layar penuh.</div>
 </div>
 </div>
 <button id="install-btn" class="w-full md:w-auto px-8 py-3 bg-brown hover:bg-brown text-white font-bold rounded-full transition-all shadow-lg shadow-warm text-sm">
 Instal Sekarang
 </button>
 </div>

 <div class="p-6 bg-cocoa/5 rounded-3xl space-y-4">
 <div class="flex items-center justify-between">
 <h3 class="font-bold text-sm">Tentang Aplikasi</h3>
 </div>
 <div class="grid grid-cols-2 gap-4 text-xs">
 <div class="text-caramel">Versi Laravel</div>
 <div class="text-right font-mono">{{ app()->version() }}</div>
 <div class="text-caramel">Lingkungan</div>
 <div class="text-right font-mono uppercase">{{ config('app.env') }}</div>
 </div>
 </div>

 <div class="flex items-center justify-between p-4 border border-sand/10 rounded-2xl">
 <div>
 <div class="font-semibold text-sm">Hapus Cache Aplikasi</div>
 <div class="text-xs text-caramel mt-0.5">Bersihkan data tersimpan untuk memperbaiki masalah tampilan.</div>
 </div>
 <button @click="localStorage.clear(); sessionStorage.clear(); window.location.reload();" 
 class="px-4 py-2 bg-cocoa/5 hover:bg-cocoa/10 text-xs font-bold rounded-lg transition-all">
 Clear Cache
 </button>
 </div>
 </div>
 </section>

 {{-- Danger Tab --}}
 <section x-show="tab === 'danger'" x-transition:enter="transition ease-out duration-200" class="space-y-6">
 <header class="mb-8 text-red-500">
 <h2 class="text-xl font-bold mb-1">Danger Zone</h2>
 <p class="text-red-500/70 text-sm">Hati-hati! Tindakan di sini bersifat permanen.</p>
 </header>
 
 <div class="bg-red-500/5 border border-red-500/20 p-6 rounded-2xl space-y-4">
 <h3 class="font-bold text-red-500 text-sm">Hapus Akun</h3>
 <p class="text-xs text-red-500/80 leading-relaxed">
 Setelah akun Anda dihapus, semua sumber daya dan data akan dihapus secara permanen. Sebelum menghapus akun Anda, harap unduh data atau informasi apa pun yang ingin Anda simpan.
 </p>
 
 <button @click="$dispatch('open-modal', 'confirm-user-deletion')" 
 class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white font-bold rounded-xl transition-all shadow-lg shadow-red-500/20 text-xs">
 Hapus Akun Saya
 </button>
 </div>
 </section>

 </main>
 </div>

 <!-- 2FA Setup Mock QR Code Modal -->
 <div x-show="showQrModal" 
 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
 style="display: none;">
 <div class="bg-soft-cream border border-sand/10 p-6 rounded-3xl max-w-sm w-full space-y-4 shadow-xl">
 <h3 class="font-bold text-lg text-center">Setup Google Authenticator</h3>
 <p class="text-xs text-caramel text-center leading-relaxed">Scan kode QR di bawah menggunakan aplikasi Google Authenticator/Authy di smartphone Anda.</p>
 
 <div class="flex justify-center py-2">
 <!-- Mock QR Code layout using SVG -->
 <svg class="w-36 h-36 bg-cream p-2 rounded-xl" viewBox="0 0 100 100">
 <path d="M5,5 h30 v30 h-30 z M65,5 h30 v30 h-30 z M5,65 h30 v30 h-30 z M15,15 h10 v10 h-10 z M75,15 h10 v10 h-10 z M15,75 h10 v10 h-10 z M45,45 h10 v10 h-10 z M55,55 h10 v10 h-10 z M35,45 h10 v10 h-10 z M45,35 h10 v10 h-10 z M65,65 h10 v10 h-10 z M75,75 h15 v15 h-15 z" fill="black" />
 </svg>
 </div>
 
 <div class="space-y-1 text-center">
 <span class="text-[10px] text-caramel uppercase tracking-widest font-semibold">Secret Key Cadangan</span>
 <p class="text-[11px] text-caramel leading-relaxed px-2">
 Secret key akan digenerate oleh server saat fitur 2FA aktif diintegrasikan. Untuk saat ini, klik Verifikasi untuk simulasi.
 </p>
 </div>

 <div class="flex gap-2">
 <button @click="showQrModal = false" class="w-1/2 py-2.5 border border-sand/10 rounded-xl text-xs font-bold">Batal</button>
 <button @click="confirm2FA()" class="w-1/2 py-2.5 bg-brown text-white rounded-xl text-xs font-bold">Verifikasi & Aktifkan</button>
 </div>
 </div>
 </div>

</div>

{{-- Deletion Modal --}}
<x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
 <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
 @csrf
 @method('delete')

 <h2 class="text-lg font-medium text-cocoa ">
 {{ __('Apakah Anda yakin ingin menghapus akun Anda?') }}
 </h2>

 <p class="mt-1 text-sm text-espresso ">
 {{ __('Setelah akun Anda dihapus, semua sumber daya dan data akan dihapus secara permanen. Silakan masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun Anda secara permanen.') }}
 </p>

 <div class="mt-6">
 <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />
 <x-text-input id="password" name="password" type="password" class="mt-1 block w-3/4" placeholder="{{ __('Password') }}" />
 <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
 </div>

 <div class="mt-6 flex justify-end">
 <x-secondary-button x-on:click="$dispatch('close')">
 {{ __('Batal') }}
 </x-secondary-button>

 <x-danger-button class="ms-3">
 {{ __('Hapus Akun') }}
 </x-danger-button>
 </div>
 </form>
</x-modal>

@endsection

@push('scripts')
@php use Illuminate\Support\Facades\Storage; @endphp
<script>
 // Handle status message from session
 @if (session('status') === 'profile-updated')
 window.showToast('Profil berhasil diperbarui!');
 @endif
 @if (session('status') === 'password-updated')
 window.showToast('Password berhasil diperbarui!');
 @endif

 // ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ Session Manager Alpine Component ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬ÃƒÂ¢Ã¢â‚¬ÂÃ¢â€šÂ¬
 function sessionManager() {
 return {
 sessions: [],
 loading: true,

 loadSessions() {
 axios.get('{{ route('settings.sessions.list') }}')
 .then(res => {
 this.sessions = res.data.sessions;
 this.loading = false;
 })
 .catch(() => {
 this.loading = false;
 window.showToast('Gagal memuat daftar sesi.', 'error');
 });
 },

 revokeSession(id) {
 axios.delete(`/settings/sessions/${id}`)
 .then(() => {
 this.sessions = this.sessions.filter(s => s.id !== id);
 window.showToast('Sesi berhasil dicabut!');
 })
 .catch(() => window.showToast('Gagal mencabut sesi.', 'error'));
 },

 revokeOthers() {
 if (!confirm('Yakin ingin keluar dari semua perangkat lain?')) return;
 axios.post('{{ route('settings.sessions.revoke_others') }}')
 .then(() => {
 this.sessions = this.sessions.filter(s => s.is_current);
 window.showToast('Semua sesi lain berhasil dicabut!');
 })
 .catch(() => window.showToast('Gagal mencabut sesi.', 'error'));
 }
 };
 }

 // PWA Install Logic
 let deferredPrompt;
 const installSection = document.getElementById('install-section');
 const installBtn = document.getElementById('install-btn');

 window.addEventListener('beforeinstallprompt', (e) => {
 e.preventDefault();
 deferredPrompt = e;
 if (installSection) {
 installSection.classList.remove('hidden');
 }
 });

 if (installBtn) {
 installBtn.addEventListener('click', async () => {
 if (!deferredPrompt) return;
 deferredPrompt.prompt();
 const { outcome } = await deferredPrompt.userChoice;
 console.log(`User response to the install prompt: ${outcome}`);
 deferredPrompt = null;
 installSection.classList.add('hidden');
 });
 }

 window.addEventListener('appinstalled', (event) => {
 console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ËœÃ‚Â', 'appinstalled', event);
 deferredPrompt = null;
 if (installSection) {
 installSection.classList.add('hidden');
 }
 window.showToast('Faiillery berhasil diinstal!');
 });
</script>
@endpush

