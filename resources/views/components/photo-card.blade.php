@props(['photo'])

@php
    $aspectRatio = (isset($photo->height, $photo->width) && $photo->width > 0) 
        ? ($photo->height / $photo->width) * 100 
        : 125;
    $dominantColor = $photo->dominant_color ?? '#F5E6CE';
@endphp

<div x-data="{ 
        hovered: false, 
        liked: {{ ($photo->is_liked ?? false) ? 'true' : 'false' }}, 
        likesCount: {{ $photo->likes_count ?? 0 }},
        saved: false,
        openOptions: false,
        showBoardsList: false,
        saving: false,
        toggleLike() {
            this.liked = !this.liked;
            this.likesCount += this.liked ? 1 : -1;
            axios.post('{{ route('photos.like', $photo) }}').catch(() => {});
        }
     }" 
     @mouseenter="hovered = true" 
     @mouseleave="hovered = false; openOptions = false; showBoardsList = false;"
     class="relative group mb-4 break-inside-avoid rounded-[20px] overflow-hidden shadow-lg shadow-black/10 border border-white/20 transition-all duration-300 hover:shadow-2xl hover:shadow-black/20 transform hover:-translate-y-1"
     style="padding-bottom: {{ $aspectRatio }}%; background: {{ $dominantColor }};">

    <!-- Media (Image or Video) -->
    <a href="{{ route('photos.show', $photo->uid ?? $photo->id) }}"
       class="absolute inset-0 w-full h-full block overflow-hidden"
       x-data="{ loaded: false, checkLoad() { if ({{ $photo->isVideo() ? 'false' : 'this.$refs.img.complete' }}) this.loaded = true; } }"
       x-init="checkLoad()">
        @if($photo->isVideo())
            <video x-ref="video"
                   src="{{ $photo->image_url }}"
                   class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105 opacity-0"
                   :class="{ 'opacity-100': loaded }"
                   autoplay
                   muted
                   loop
                   playsinline
                   x-on:loadeddata="loaded = true">
            </video>
        @else
            <img x-ref="img"
                 src="{{ $photo->thumbnail_url }}"
                 alt="{{ $photo->title }}"
                 class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105 opacity-0"
                 :class="{ 'opacity-100': loaded }"
                 x-on:load="loaded = true" />
        @endif
    </a>

    <!-- Top Corner Floating Glass Action Pills -->
    <div class="absolute top-3 right-3 flex items-center gap-2 pointer-events-auto z-10">
        <!-- Quick Like Pill -->
        <button @click.prevent="toggleLike()" 
                class="h-8 px-3 rounded-full bg-white/15 hover:bg-white/25 backdrop-blur-xl backdrop-saturate-150 border border-white/30 text-white flex items-center gap-1.5 text-xs font-bold transition-all duration-200 active:scale-90 shadow-md">
            <svg class="w-3.5 h-3.5 transition-colors duration-200" 
                 :class="liked ? 'text-red-400 fill-current' : 'text-white fill-none'" 
                 stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <span x-text="likesCount"></span>
        </button>


    </div>

    <!-- Frosted Glass Bottom Overlay on Hover -->
    <div x-show="hovered" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2 backdrop-blur-none"
         x-transition:enter-end="opacity-100 translate-y-0 backdrop-blur-xl"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 backdrop-blur-xl"
         x-transition:leave-end="opacity-0 translate-y-2 backdrop-blur-none"
         class="absolute inset-0 bg-white/10 backdrop-blur-xl backdrop-saturate-150 border border-white/20 p-4 flex flex-col justify-end z-20 pointer-events-auto transition-all"
         style="display: none;">

        <div class="flex items-end justify-between gap-2">
            <!-- Title & User info -->
            <div class="flex flex-col gap-1 max-w-[75%]">
                <a href="{{ route('photos.show', $photo->uid ?? $photo->id) }}" 
                   class="font-bold text-white text-sm hover:underline drop-shadow truncate">
                    {{ $photo->title }}
                </a>
                <a href="{{ route('profile.show', $photo->user) }}" class="flex items-center gap-1.5 hover:opacity-90 transition-opacity">
                    <img src="{{ $photo->user->avatar_url }}" alt="{{ $photo->user->name }}" class="w-5 h-5 rounded-full object-cover border border-white/30" />
                    <span class="text-[11px] font-bold text-white/90 truncate">@ {{ $photo->user->username }}</span>
                    @if($photo->user->is_verified ?? false)
                        <x-verified-badge size="w-3.5 h-3.5" checkSize="w-2 h-2" />
                    @endif
                </a>
            </div>

            <!-- More Options Button -->
            <div class="relative shrink-0">
                <button @click.prevent="openOptions = !openOptions; showBoardsList = false;"
                        class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 border border-white/30 flex items-center justify-center text-white transition-all shadow-md">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                </button>

                <!-- Options Dropdown (Frosted Glass) -->
                <div x-show="openOptions" 
                     @click.away="openOptions = false; showBoardsList = false;"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute bottom-full right-0 mb-2 w-48 rounded-[20px] bg-white/20 backdrop-blur-xl backdrop-saturate-150 border border-white/20 shadow-2xl p-2 z-50 text-white"
                     style="display:none;">
                    
                    <!-- Main Options Menu -->
                    <div x-show="!showBoardsList">
                        @auth
                            <button @click="showBoardsList = true"
                                    class="w-full text-left px-3 py-2.5 flex items-center gap-2 text-xs font-semibold rounded-xl hover:bg-white/20 transition-colors">
                                <svg class="w-4 h-4 fill-none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                </svg>
                                <span x-text="saved ? 'Tersimpan' : 'Simpan ke Papan'"></span>
                            </button>
                        @endauth
                        @guest
                            <a href="{{ route('login') }}"
                               class="w-full text-left px-3 py-2.5 flex items-center gap-2 text-xs font-semibold rounded-xl hover:bg-white/20 transition-colors">
                                <svg class="w-4 h-4 fill-none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                </svg>
                                <span>Simpan ke Papan</span>
                            </a>
                        @endguest
                        
                        <button @click="navigator.clipboard.writeText('{{ route('photos.show', $photo->uid ?? $photo->id) }}'); if(window.showToast) window.showToast('Tautan disalin!'); openOptions = false;"
                                class="w-full text-left px-3 py-2.5 flex items-center gap-2 text-xs font-semibold rounded-xl hover:bg-white/20 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                            </svg>
                            Salin Tautan
                        </button>
                        
                        <a href="{{ route('photos.download', $photo) }}"
                           class="w-full text-left px-3 py-2.5 flex items-center gap-2 text-xs font-semibold rounded-xl hover:bg-white/20 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Unduh
                        </a>
                    </div>

                    <!-- Board Selector Sub-Menu -->
                    <div x-show="showBoardsList" style="display: none;">
                        <div class="flex items-center gap-1 px-2 py-1.5 border-b border-white/10 mb-1.5 text-white/60">
                            <button @click="showBoardsList = false" class="hover:text-white transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <span class="text-[11px] font-bold uppercase tracking-wider">Pilih Papan</span>
                        </div>
                        <div class="max-h-40 overflow-y-auto space-y-0.5">
                            <template x-for="board in window.failerryBoards || window.FaiilleryBoards || []" :key="board.id">
                                <button @click="
                                    saving = true;
                                    axios.post('{{ route('pins.store') }}', { photo_id: {{ $photo->id }}, board_id: board.id })
                                    .then(res => { window.showToast(res.data.message); openOptions = false; showBoardsList = false; saved = true; })
                                    .catch(err => {
                                        if (err.response?.status === 409) {
                                            axios.delete('{{ route('pins.destroy') }}', { data: { photo_id: {{ $photo->id }}, board_id: board.id } })
                                            .then(res => { window.showToast('Foto dihapus dari board.'); openOptions = false; showBoardsList = false; saved = false; })
                                            .catch(() => window.showToast('Gagal menghapus simpanan', 'error'));
                                        } else {
                                            window.showToast(err.response?.data?.message || 'Gagal menyimpan', 'error');
                                        }
                                    })
                                    .finally(() => saving = false);"
                                    class="w-full text-left px-3.5 py-2 rounded-xl text-xs font-semibold hover:bg-white/20 transition-colors truncate">
                                    <span x-text="board.title"></span>
                                </button>
                            </template>
                            <div x-show="(window.failerryBoards || window.FaiilleryBoards || []).length === 0" class="px-3 py-2 text-xs text-white/70 text-center">
                                Belum ada board.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @supports not ((-webkit-backdrop-filter: blur(1px)) or (backdrop-filter: blur(1px))) {
        .backdrop-blur-xl, .backdrop-blur-md {
            background-color: rgba(255, 248, 237, 0.95) !important;
            color: #3B2417 !important;
        }
    }
</style>
