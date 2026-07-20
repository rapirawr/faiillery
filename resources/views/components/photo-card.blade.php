@props(['photo'])

<div class="relative group mb-4 break-inside-avoid rounded-2xl overflow-hidden"
 style="padding-bottom: {{ ($photo->height / $photo->width) * 100 }}%; background:{{ $photo->dominant_color ?? '#F5E6CE' }};">

 <!-- Media (Image or Video) -->
 <a href="{{ route('photos.show', $photo->uid) }}"
 class="absolute inset-0 w-full h-full"
 x-data="{ loaded: false, checkLoad() { if ({{ $photo->isVideo() ? 'false' : 'this.$refs.img.complete' }}) this.loaded = true; } }"
 x-init="checkLoad()">
 @if($photo->isVideo())
 <video x-ref="video"
        src="{{ $photo->image_url }}"
        class="w-full h-full object-cover transition-all duration-500 group-hover:scale-[1.03] opacity-0"
        :class="{ 'opacity-100': loaded }"
        autoplay
        muted
        loop
        playsinline
        x-on:loadeddata="loaded = true">
 </video>
 <div class="absolute top-3 left-3 w-7 h-7 rounded-full bg-black/45 text-white flex items-center justify-center z-10 backdrop-blur-md">
     <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
 </div>
 @else
 <img x-ref="img"
 src="{{ $photo->thumbnail_url }}"
 alt="{{ $photo->title }}"
 class="w-full h-full object-cover transition-all duration-500 group-hover:scale-[1.03] opacity-0"
 :class="{ 'opacity-100': loaded }"
 x-on:load="loaded = true" />
 @endif
 </a>

 <!-- Hover overlay -->
 <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-250 pointer-events-none flex flex-col justify-between p-3"
 style="background:rgba(59,36,23,0.45);">

 <!-- Top: Save button -->
 <div class="flex justify-end w-max self-end pointer-events-auto">
 @auth
 <div x-data="{ pinned: {{ ($photo->is_pinned ?? false) ? 'true' : 'false' }}, saving: false, showBoards: false }" class="relative">
 <button @click="showBoards = !showBoards"
 class="font-bold px-5 py-2 rounded-full text-xs transition-all active:scale-95"
 style="background:rgba(255,248,237,0.2);color:#FFF8ED;border:1px solid rgba(255,248,237,0.35);backdrop-filter:blur(6px);">
 Simpan
 </button>
 <div x-show="showBoards" @click.away="showBoards = false"
 class="absolute top-full right-0 mt-2 w-48 rounded-2xl shadow-warm overflow-hidden z-20"
 style="background:#FFF8ED;border:1px solid #E3C79A;display:none;">
 <div class="max-h-48 overflow-y-auto">
 <template x-for="board in window.failerryBoards || []" :key="board.id">
 <button @click="
 saving = true;
 axios.post('{{ route('pins.store') }}', { photo_id: {{ $photo->id }}, board_id: board.id })
 .then(res => { window.showToast(res.data.message); showBoards = false; pinned = true; })
 .catch(err => {
 if (err.response?.status === 409) {
 // Already pinned — unpin instead
 axios.delete('{{ route('pins.destroy') }}', { data: { photo_id: {{ $photo->id }}, board_id: board.id } })
 .then(res => { window.showToast('Foto dihapus dari board.'); showBoards = false; pinned = false; })
 .catch(() => window.showToast('Gagal menghapus simpanan', 'error'));
 } else {
 window.showToast(err.response?.data?.message || 'Gagal menyimpan', 'error');
 }
 })
 .finally(() => saving = false);"
 class="w-full text-left px-4 py-3 text-sm font-semibold transition-colors"
 style="color:#5C3A21;"
 onmouseover="this.style.background='#F5E6CE'" onmouseout="this.style.background=''">
 <span x-text="board.title" class="truncate block"></span>
 </button>
 </template>
 <div x-show="(window.failerryBoards || []).length === 0" class="px-4 py-3 text-sm text-center" style="color:#C69C6D;">
 Belum ada board.
 </div>
 </div>
 </div>
 </div>
 @endauth
 @guest
 <a href="{{ route('login') }}"
 class="font-bold px-5 py-2 rounded-full text-xs transition-all active:scale-95 pointer-events-auto"
 style="background:rgba(255,248,237,0.2);color:#FFF8ED;border:1px solid rgba(255,248,237,0.35);backdrop-filter:blur(6px);">
 Simpan
 </a>
 @endguest
 </div>

 <!-- Bottom: Title + owner + options -->
 <div class="flex items-end justify-between pointer-events-auto">
 <div class="flex flex-col gap-1 max-w-[70%]">
 <a href="{{ route('photos.show', $photo) }}" class="font-bold truncate text-sm hover:underline drop-shadow" style="color:#FFF8ED;">
 {{ $photo->title }}
 </a>
 <a href="{{ route('profile.show', $photo->user) }}" class="flex items-center gap-1.5 hover:opacity-80 transition-opacity">
 <span class="text-[10px] font-bold truncate" style="color:rgba(255,248,237,0.8);">@ {{ $photo->user->username }}</span>
 @if($photo->user->is_verified)
 <x-verified-badge size="w-3.5 h-3.5" checkSize="w-2 h-2" />
 @endif
 </a>
 </div>

 <!-- More options -->
 <div x-data="{ openOptions: false }" class="relative shrink-0">
 <button @click="openOptions = !openOptions"
 class="w-8 h-8 rounded-full flex items-center justify-center transition-colors"
 style="background:rgba(255,248,237,0.9);color:#5C3A21;">
 <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
 </button>
 <div x-show="openOptions" @click.away="openOptions = false"
 class="absolute bottom-full right-0 mb-2 w-48 rounded-xl shadow-warm py-1 z-50 overflow-hidden"
 style="background:#FFF8ED;border:1px solid #E3C79A;display:none;">
 <button @click="navigator.clipboard.writeText('{{ route('photos.show', $photo) }}'); window.showToast('Tautan disalin!'); openOptions = false;"
 class="w-full text-left px-4 py-2.5 flex items-center gap-2 text-sm transition-colors"
 style="color:#5C3A21;" onmouseover="this.style.background='#F5E6CE'" onmouseout="this.style.background=''">
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
 Salin Tautan
 </button>
 <a href="{{ route('photos.download', $photo) }}"
 class="w-full text-left px-4 py-2.5 flex items-center gap-2 text-sm transition-colors"
 style="color:#5C3A21;" onmouseover="this.style.background='#F5E6CE'" onmouseout="this.style.background=''">
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
 Unduh
 </a>
 </div>
 </div>
 </div>
 </div>
</div>
