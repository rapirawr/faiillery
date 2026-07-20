@extends('layouts.app')

@section('title', $photo->title . ' - Faiillery')
@section('meta_description', str()->limit($photo->description, 160) ?: 'Temukan momen menarik ini di Faiillery.')
@section('meta_image', $photo->image_url)
@section('meta_image_width', $photo->width ?? '1200')
@section('meta_image_height', $photo->height ?? '630')

@push('head')
<style>
    /* Heart burst animation */
    @keyframes heart-burst {
        0%   { opacity: 0; transform: translate(-50%, -50%) scale(0.2); }
        30%  { opacity: 1; transform: translate(-50%, -50%) scale(1.3); }
        60%  { opacity: 1; transform: translate(-50%, -50%) scale(1.0); }
        100% { opacity: 0; transform: translate(-50%, -50%) scale(1.0); }
    }
    .heart-burst {
        animation: heart-burst 0.9s cubic-bezier(0.17, 0.89, 0.32, 1.28) forwards;
    }
    /* Like button pulse */
    @keyframes like-pop {
        0%   { transform: scale(1); }
        40%  { transform: scale(1.25); }
        70%  { transform: scale(0.92); }
        100% { transform: scale(1); }
    }
    .like-pop {
        animation: like-pop 0.35s ease forwards;
    }
    /* Hide native video controls on custom player */
    .custom-video-player video::-webkit-media-controls { display: none !important; }
    .custom-video-player video::-webkit-media-controls-enclosure { display: none !important; }
</style>
@endpush

@section('content')
{{-- ROOT ALPINE STATE --}}
<div x-data="{
    liked: {{ auth()->check() && auth()->user()->hasLiked($photo) ? 'true' : 'false' }},
    likesCount: {{ $photo->likes_count }},
    likeAnimating: false,
    showHeartBurst: false,

    toggleLike() {
        const prev = { liked: this.liked, count: this.likesCount };
        this.liked = !this.liked;
        this.likesCount = this.liked ? this.likesCount + 1 : Math.max(0, this.likesCount - 1);
        this.likeAnimating = true;
        setTimeout(() => this.likeAnimating = false, 350);

        axios.post('{{ route('photos.like', $photo) }}')
            .then(res => {
                this.liked = res.data.liked;
                this.likesCount = res.data.likes_count;
            })
            .catch(() => {
                this.liked = prev.liked;
                this.likesCount = prev.count;
                window.showToast('Gagal menyukai', 'error');
            });
    },

    doubleTapLike() {
        if (!this.liked) {
            @auth this.toggleLike(); @endauth
            @guest window.location = '{{ route('login') }}'; return; @endguest
        }
        this.showHeartBurst = true;
        setTimeout(() => this.showHeartBurst = false, 950);
    },

    formatLikes(n) {
        if (n >= 1000000) return (n / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
        if (n >= 1000)    return (n / 1000).toFixed(1).replace(/\.0$/, '') + 'K';
        return n.toString();
    },

    isFollowing: {{ auth()->check() && $photo->user && auth()->user()->isFollowing($photo->user) ? 'true' : 'false' }},
    isFollowingLoading: false,
    toggleFollow() {
        this.isFollowingLoading = true;
        axios.post('{{ $photo->user ? route('user.follow', $photo->user->username) : '#' }}')
            .then(res => this.isFollowing = res.data.following)
            .finally(() => this.isFollowingLoading = false);
    },

    shareUrl: window.location.href,
    isShareModalOpen: false,
    isReportModalOpen: false,
    isCollectionModalOpen: false,
    isOptionsOpen: false,
    userCollections: [],

    init() {
        @auth this.fetchCollections(); @endauth
    },
    fetchCollections() {
        axios.get('{{ route('collections.index') }}?photo_id={{ $photo->id }}')
            .then(res => this.userCollections = res.data);
    },
    copyLink() {
        navigator.clipboard.writeText(this.shareUrl).then(() => {
            window.showToast('Tautan disalin!');
            this.isShareModalOpen = false;
        });
    },
    toggleInCollection(collection) {
        axios.post('{{ url('/collections') }}/' + collection.id + '/toggle-photo', { photo_id: {{ $photo->id }} })
            .then(res => {
                window.showToast(res.data.message);
                collection.is_attached = res.data.attached;
                collection.photos_count = res.data.attached
                    ? collection.photos_count + 1
                    : Math.max(0, collection.photos_count - 1);
            });
    },

    pinned: {{ auth()->check() && auth()->user()->hasPinned($photo) ? 'true' : 'false' }},
    boards: {{ $userBoards ? $userBoards->toJson() : '[]' }},
    showBoards: false,
    saving: false,
    saveToBoard(boardId) {
        this.saving = true;
        axios.post('{{ route('pins.store') }}', { photo_id: {{ $photo->id }}, board_id: boardId })
            .then(res => {
                window.showToast(res.data.message);
                this.showBoards = false;
                this.pinned = true;
            })
            .catch(err => window.showToast(err.response?.data?.message || 'Gagal menyimpan', 'error'))
            .finally(() => this.saving = false);
    }
}">

    {{-- Mobile Back Button --}}
    <div class="fixed top-24 left-4 z-40 lg:hidden">
        <button onclick="history.back()"
            class="w-10 h-10 bg-white/80 dark:bg-black/60 backdrop-blur-md rounded-full flex items-center justify-center shadow-md border border-sand/40 text-cocoa dark:text-white active:scale-95 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
    </div>

    {{-- MAIN FLEX CONTAINER (2 PANELS) --}}
    <div class="max-w-6xl mx-auto px-2 sm:px-4 py-3 md:py-6">
        <div class="flex flex-col lg:flex-row items-center lg:items-start justify-center gap-6 relative">

            {{-- ── PANEL KIRI: Photo Card dengan Floating Header Profil Saja ── --}}
            <div class="relative w-full max-w-[540px] shrink-0 rounded-[32px] overflow-hidden shadow-2xl bg-[#0e0906] border border-sand/40 select-none">

                {{-- Media area --}}
                <div class="relative w-full flex items-center justify-center min-h-[380px] max-h-[75vh] bg-[#0e0906]"
                     x-data="{
                         loaded: false,
                         error: false,
                         checkLoad() {
                             @if($photo->isVideo())
                                 this.loaded = true;
                             @else
                                 if (this.$refs.mainImage && this.$refs.mainImage.complete) this.loaded = true;
                             @endif
                         }
                     }"
                     x-init="checkLoad()">

                    <div class="absolute inset-0 transition-opacity duration-700"
                         :class="loaded ? 'opacity-0 pointer-events-none' : 'opacity-100'"
                         style="background-color: {{ $photo->dominant_color ?? '#0e0906' }};"></div>

                    @if($photo->isVideo())
                        <div class="relative w-full h-full flex items-center justify-center group/vp custom-video-player"
                             :class="fullscreen ? 'fixed inset-0 z-[9999] bg-black w-screen h-screen' : 'w-full h-full'"
                             x-data="{
                                 playing: true,
                                 muted: true,
                                 duration: 0,
                                 currentTime: 0,
                                 progress: 0,
                                 fullscreen: false,
                                 togglePlay() {
                                     this.$refs.vid.paused
                                         ? (this.$refs.vid.play(), this.playing = true)
                                         : (this.$refs.vid.pause(), this.playing = false);
                                 },
                                 toggleMute() {
                                     this.$refs.vid.muted = !this.$refs.vid.muted;
                                     this.muted = this.$refs.vid.muted;
                                 },
                                 toggleFullscreen() {
                                     if (!document.fullscreenElement) {
                                         this.$el.requestFullscreen().catch(() => {});
                                     } else {
                                         document.exitFullscreen().catch(() => {});
                                     }
                                 },
                                 formatTime(s) {
                                     let m = Math.floor(s / 60);
                                     let sec = Math.floor(s % 60);
                                     return m + ':' + (sec < 10 ? '0' : '') + sec;
                                 },
                                 seek(e) {
                                     let rect = this.$refs.pgBar.getBoundingClientRect();
                                     this.$refs.vid.currentTime = ((e.clientX - rect.left) / rect.width) * this.duration;
                                 }
                             }"
                             x-init="document.addEventListener('fullscreenchange', () => { fullscreen = !!document.fullscreenElement; });"
                             @dblclick="$dispatch('double-tap-like')">

                            <video x-ref="vid"
                                   src="{{ $photo->image_url }}"
                                   class="w-full h-auto max-h-[75vh] object-contain cursor-pointer"
                                   autoplay muted loop playsinline
                                   @click="togglePlay"
                                   @loadedmetadata="duration = $el.duration"
                                   @timeupdate="currentTime = $el.currentTime; progress = duration ? (currentTime / duration) * 100 : 0"
                                   x-on:loadeddata="loaded = true; $dispatch('video-loaded')"
                                   x-on:error="error = true; loaded = false">
                            </video>

                            <div class="absolute bottom-0 left-0 right-0 px-4 pb-4 pt-10 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover/vp:opacity-100 transition-opacity duration-300 z-20"
                                 :class="fullscreen ? 'opacity-100' : ''">
                                <div x-ref="pgBar" @click="seek" class="relative w-full py-2 cursor-pointer group/pg mb-3">
                                    <div class="w-full h-[3px] bg-white/30 rounded-full">
                                        <div class="h-full rounded-full bg-white transition-all" :style="`width: ${progress}%`"></div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-white text-xs select-none">
                                    <div class="flex items-center gap-3">
                                        <button @click="togglePlay" class="hover:opacity-70 transition-opacity">
                                            <svg x-show="!playing" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                            <svg x-show="playing"  class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                        </button>
                                        <span class="font-mono text-[11px] text-white/80">
                                            <span x-text="formatTime(currentTime)">0:00</span> / <span x-text="formatTime(duration)">0:00</span>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button @click="toggleMute" class="hover:opacity-70 transition-opacity">
                                            <svg x-show="muted" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.21.05-.42.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"/></svg>
                                            <svg x-show="!muted" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>
                                        </button>
                                        <button @click="toggleFullscreen" class="hover:opacity-70 transition-opacity">
                                            <svg x-show="!fullscreen" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/></svg>
                                            <svg x-show="fullscreen"  class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Image Display with double-tap --}}
                        <div class="relative w-full h-full flex items-center justify-center" @dblclick="$dispatch('double-tap-like')">
                            <img x-ref="mainImage"
                                 src="{{ $photo->image_url }}"
                                 crossorigin="anonymous"
                                 alt="{{ $photo->title }}"
                                 class="w-full h-auto max-h-[75vh] object-contain opacity-0 transition-opacity duration-500"
                                 :class="{ 'opacity-100': loaded }"
                                 x-on:load="loaded = true"
                                 x-on:error="error = true; loaded = false">
                        </div>
                    @endif

                    {{-- Double-tap heart burst overlay --}}
                    <div @double-tap-like.window="
                            showHeartBurst = true;
                            if (!liked) {
                                @auth toggleLike(); @endauth
                                @guest window.location = '{{ route('login') }}'; @endguest
                            }
                            setTimeout(() => showHeartBurst = false, 950);
                         "
                         x-show="showHeartBurst"
                         class="absolute inset-0 flex items-center justify-center pointer-events-none z-50">
                        <svg class="heart-burst w-24 h-24 text-white drop-shadow-2xl" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                    </div>
                </div>

                {{-- ── FLOATING GLASS OVERLAY TOP: Header Profil Saja ── --}}
                <div class="absolute top-0 inset-x-0 h-24 bg-gradient-to-b from-black/35 via-black/10 to-transparent pointer-events-none rounded-t-[32px] z-20"></div>
                <div class="absolute top-3.5 inset-x-3.5 z-30 pointer-events-auto">
                    @include('photos.partials.photo-header')
                </div>

            </div>{{-- /Panel Kiri --}}

            {{-- ── PANEL KANAN: Caption, Action Bar, Komentar & Input ── --}}
            <div class="w-full lg:w-[380px] shrink-0 bg-white/85 dark:bg-[#1c1611]/90 backdrop-blur-xl rounded-[32px] border border-sand/40 shadow-xl p-4 flex flex-col h-auto lg:h-[620px] lg:max-h-[75vh]">
                
                {{-- Middle Scrollable Content: Caption, Action Bar & Comments --}}
                <div class="flex-1 overflow-y-auto space-y-4 pr-1 scrollbar-thin scrollbar-thumb-sand">
                    {{-- Caption, Title, Hashtags & Views --}}
                    @include('photos.partials.photo-caption')

                    {{-- Action Bar (Like, Share, Save) --}}
                    @include('photos.partials.photo-actions')

                    <div class="h-px bg-sand/30 my-2"></div>

                    {{-- Comments List --}}
                    @include('photos.partials.photo-comments')
                </div>

                {{-- Bottom Sticky Comment Input Bar --}}
                <div class="pt-3 border-t border-sand/30 shrink-0">
                    @include('photos.partials.photo-comment-input')
                </div>

            </div>{{-- /Panel Kanan --}}

        </div>
    </div>

    {{-- RELATED PHOTOS SECTION --}}
    @if($relatedPhotos->count() > 0)
    <div class="mt-12 max-w-6xl mx-auto px-4 text-center">
        <h2 class="text-lg font-bold text-cocoa dark:text-white mb-6 tracking-tight">Eksplorasi Lainnya</h2>
        <div class="w-full mx-auto" x-data="{ msnry: null }" x-init="$nextTick(() => {
            const grid = $el.querySelector('.related-grid');
            msnry = new window.Masonry(grid, { 
                itemSelector: '.grid-item', 
                columnWidth: '.grid-sizer', 
                percentPosition: true 
            });
            window.imagesLoaded(grid).on('progress', () => {
                msnry.layout();
            });
        })">
            <div class="related-grid w-full -ml-2 sm:-ml-4 text-left">
                <div class="grid-sizer w-1/2 sm:w-1/3 md:w-1/4 lg:w-1/5 xl:w-[16.666%] h-0"></div>
                @foreach($relatedPhotos as $relPhoto)
                    <div class="grid-item w-1/2 sm:w-1/3 md:w-1/4 lg:w-1/5 xl:w-[16.666%] pl-2 sm:pl-4 mb-2 sm:mb-4">
                        @include('components.photo-card', ['photo' => $relPhoto])
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- MODALS --}}

    {{-- Share Modal --}}
    <div x-show="isShareModalOpen" style="display:none;"
         class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div x-show="isShareModalOpen"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click="isShareModalOpen = false"
             class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>

        <div x-show="isShareModalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative w-full sm:max-w-md bg-white dark:bg-[#1c1c1e] rounded-t-3xl sm:rounded-3xl shadow-2xl z-10 overflow-hidden">
            <div class="w-10 h-1 bg-sand rounded-full mx-auto mt-3 mb-1 sm:hidden"></div>
            <div class="flex items-center justify-between px-5 py-4 border-b border-sand/30">
                <h3 class="text-base font-bold text-cocoa dark:text-white">Bagikan</h3>
                <button @click="isShareModalOpen = false" class="w-7 h-7 flex items-center justify-center rounded-full bg-sand/20 text-cocoa hover:bg-sand/40 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-4 gap-3 mb-5">
                    <a :href="'https://wa.me/?text=' + encodeURIComponent('Lihat ini di Faiillery: ' + shareUrl)" target="_blank" class="flex flex-col items-center gap-1.5 group">
                        <div class="w-14 h-14 rounded-2xl bg-[#25D366] text-white flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                        </div>
                        <span class="text-xs text-caramel dark:text-gray-400">WhatsApp</span>
                    </a>
                    <a :href="'https://twitter.com/intent/tweet?text=' + encodeURIComponent('Lihat ini di Faiillery: ') + '&url=' + encodeURIComponent(shareUrl)" target="_blank" class="flex flex-col items-center gap-1.5 group">
                        <div class="w-14 h-14 rounded-2xl bg-black text-white flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </div>
                        <span class="text-xs text-caramel dark:text-gray-400">X</span>
                    </a>
                    <a :href="'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(shareUrl)" target="_blank" class="flex flex-col items-center gap-1.5 group">
                        <div class="w-14 h-14 rounded-2xl bg-[#1877F2] text-white flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </div>
                        <span class="text-xs text-caramel dark:text-gray-400">Facebook</span>
                    </a>
                    <a :href="'mailto:?subject=' + encodeURIComponent('Lihat ini di Faiillery') + '&body=' + encodeURIComponent('Lihat foto menarik ini: ' + shareUrl)" class="flex flex-col items-center gap-1.5 group">
                        <div class="w-14 h-14 rounded-2xl bg-[#8B5E3C] text-white flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="text-xs text-caramel dark:text-gray-400">Email</span>
                    </a>
                </div>

                {{-- Copy Link --}}
                <div class="flex items-center gap-2 bg-sand/20 dark:bg-white/5 rounded-2xl px-4 py-3 mb-4">
                    <span class="flex-1 text-xs text-cocoa dark:text-gray-300 truncate" x-text="shareUrl"></span>
                    <button @click="copyLink()" class="text-xs font-bold text-brown hover:underline whitespace-nowrap">Salin</button>
                </div>

                {{-- Embed --}}
                <div x-data="{ embedCode: '<iframe src=\'{{ route('photos.embed', $photo->uid) }}\' width=\'100%\' height=\'500\' frameborder=\'0\' allowfullscreen></iframe>' }" class="mb-4">
                    <p class="text-xs font-semibold text-caramel dark:text-gray-400 mb-2 uppercase tracking-wider">Embed</p>
                    <div class="flex items-center gap-2 bg-sand/20 dark:bg-white/5 rounded-2xl px-4 py-3">
                        <code class="flex-1 text-[10px] text-cocoa dark:text-gray-300 font-mono truncate" x-text="embedCode"></code>
                        <button @click="navigator.clipboard.writeText(embedCode); window.showToast('Kode embed disalin!');" class="text-xs font-bold text-brown hover:underline whitespace-nowrap">Salin</button>
                    </div>
                </div>

                {{-- In-App DM Share Section --}}
                @auth
                <div class="mt-4 pt-4 border-t border-sand/30"
                     x-data="{
                         chats: [],
                         loading: false,
                         loaded: false,
                         loadChats() {
                             if (this.loaded) return;
                             this.loading = true;
                             axios.get('/api/conversations-list')
                                 .then(res => {
                                     this.chats = res.data;
                                     this.loaded = true;
                                 })
                                 .finally(() => this.loading = false);
                         },
                         sendPhoto(chat) {
                             axios.post('{{ route('messages.share') }}', {
                                 username: chat.username,
                                 photo_url: '{{ route('photos.show', $photo->uid) }}',
                                 photo_title: '{{ addslashes($photo->title ?? "Karya Tanpa Judul") }}'
                             })
                             .then(res => {
                                 window.showToast(res.data.message);
                                 isShareModalOpen = false;
                             })
                             .catch(err => {
                                 window.showToast(err.response?.data?.message || 'Gagal mengirim', 'error');
                             });
                         }
                     }"
                     x-init="$watch('isShareModalOpen', value => { if (value) loadChats(); })">
                    <p class="text-xs font-semibold text-caramel dark:text-gray-400 mb-2.5 uppercase tracking-wider">Kirim ke Obrolan</p>
                    
                    <!-- Loading state -->
                    <div x-show="loading" class="flex justify-center py-4">
                        <svg class="animate-spin h-5 w-5 text-brown" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    <!-- Chats List -->
                    <div x-show="!loading" class="max-h-48 overflow-y-auto space-y-2 pr-1">
                        <template x-for="chat in chats" :key="chat.username">
                            <div class="flex items-center justify-between p-2 rounded-xl bg-sand/10 hover:bg-sand/20 dark:bg-white/5 dark:hover:bg-white/10 transition-colors">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <img :src="chat.avatar" class="w-8 h-8 rounded-full object-cover border border-sand/40">
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-cocoa dark:text-white truncate" x-text="chat.name"></p>
                                        <p class="text-[10px] text-caramel truncate" x-text="'@' + chat.username"></p>
                                    </div>
                                </div>
                                <button @click="sendPhoto(chat)"
                                        class="px-3 py-1 rounded-full bg-brown hover:bg-espresso text-white text-[11px] font-bold shadow-sm transition-all active:scale-95">
                                    Kirim
                                </button>
                            </div>
                        </template>
                        <template x-if="chats.length === 0">
                            <div class="text-center py-4 text-xs text-caramel font-medium">Belum ada obrolan aktif.</div>
                        </template>
                    </div>
                </div>
                @endauth
            </div>
        </div>
    </div>

    {{-- Report Modal --}}
    <div x-show="isReportModalOpen" style="display:none;"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="isReportModalOpen = false"
             class="w-full max-w-md bg-white dark:bg-[#1c1c1e] rounded-3xl shadow-2xl overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-bold text-cocoa dark:text-white text-center mb-1">Laporkan Postingan</h3>
                <p class="text-xs text-caramel text-center mb-6 uppercase tracking-widest">Bantu kami menjaga Faiillery tetap aman</p>
                <form action="{{ route('photos.report', $photo) }}" method="POST" class="space-y-3">
                    @csrf
                    <select name="reason" required class="w-full bg-sand/20 dark:bg-white/10 border-none rounded-2xl px-4 py-3 text-sm text-cocoa dark:text-white focus:ring-2 focus:ring-red-400 transition-all">
                        <option value="">Pilih alasan...</option>
                        <option value="spam">Spam atau Penipuan</option>
                        <option value="inappropriate">Konten Tidak Pantas</option>
                        <option value="violence">Kekerasan atau Ancaman</option>
                        <option value="copyright">Pelanggaran Hak Cipta</option>
                        <option value="harassment">Pelecehan atau Bullying</option>
                        <option value="other">Alasan Lainnya</option>
                    </select>
                    <textarea name="description" placeholder="Ceritakan lebih lanjut..." class="w-full bg-sand/20 dark:bg-white/10 border-none rounded-2xl px-4 py-3 text-sm text-cocoa dark:text-white focus:ring-2 focus:ring-red-400 min-h-[80px] resize-none"></textarea>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="isReportModalOpen = false" class="flex-1 py-3 rounded-2xl font-bold text-sm text-cocoa/60 hover:bg-sand/20 transition-all">Batal</button>
                        <button type="submit" class="flex-1 py-3 rounded-2xl font-bold text-sm bg-red-500 text-white hover:bg-red-600 transition-all">Kirim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Board Modal --}}
    <div x-show="showBoards" style="display:none;"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showBoards = false"></div>
        <div x-show="showBoards"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative w-full sm:max-w-sm bg-white dark:bg-[#1c1c1e] rounded-t-3xl sm:rounded-3xl shadow-2xl z-10 overflow-hidden">
            <div class="w-10 h-1 bg-sand rounded-full mx-auto mt-3 mb-1 sm:hidden"></div>
            <div class="px-5 py-4 border-b border-sand/30 flex items-center justify-between">
                <span class="font-bold text-cocoa dark:text-white">Simpan ke Papan</span>
                <button @click="showBoards = false" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-sand/30 text-caramel transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="max-h-72 overflow-y-auto p-2">
                <template x-for="board in boards" :key="board.id">
                    <button @click="saveToBoard(board.id)"
                        class="w-full text-left px-4 py-3 hover:bg-sand/20 dark:hover:bg-white/5 flex items-center gap-3 transition-colors rounded-2xl">
                        <div class="w-10 h-10 rounded-xl bg-sand/30 shrink-0 overflow-hidden">
                            <template x-if="board.cover_image">
                                <img :src="'/storage/' + board.cover_image" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!board.cover_image">
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-caramel" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                </div>
                            </template>
                        </div>
                        <span x-text="board.title" class="font-semibold text-sm text-cocoa dark:text-white truncate"></span>
                    </button>
                </template>
                <template x-if="boards.length === 0">
                    <div class="text-center py-8 text-sm text-caramel">Belum ada papan. Buat papan dulu.</div>
                </template>
            </div>
        </div>
    </div>

</div>
@endsection