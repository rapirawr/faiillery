@extends('layouts.app')

@section('title', $photo->title . ' - Bloxpin')
@section('meta_description', str()->limit($photo->description, 160) ?: 'Temukan momen menarik ini di Bloxpin.')
@section('meta_image', $photo->image_url)
@section('meta_image_width', $photo->width ?? '1200')
@section('meta_image_height', $photo->height ?? '630')

@push('head')
<style>
    /* Pinterest-style centered card layout */
    .ig-post-container {
        max-width: 100%;
        margin: 0 auto;
    }
    @media (min-width: 768px) {
        .ig-post-container {
            max-width: 860px;
            margin: 0 auto;
            padding-left: 24px;
            padding-right: 24px;
        }
        .ig-post-desktop {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 320px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 32px rgba(0,0,0,0.10);
        }
    }
    @media (min-width: 1024px) {
        .ig-post-container {
            max-width: 960px;
            padding-left: 48px;
            padding-right: 48px;
        }
        .ig-post-desktop {
            grid-template-columns: minmax(0, 1fr) 360px;
        }
    }
    @media (min-width: 1280px) {
        .ig-post-container {
            max-width: 1060px;
            padding-left: 80px;
            padding-right: 80px;
        }
        .ig-post-desktop {
            grid-template-columns: minmax(0, 1fr) 380px;
        }
    }

    /* Heart animation */
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

    /* Sticky header + comment input */
    .ig-header-sticky {
        position: sticky;
        top: 0;
        z-index: 40;
    }
    @media (min-width: 768px) {
        .ig-header-sticky {
            position: sticky;
            top: 0;
        }
        .ig-sidebar-sticky {
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            scrollbar-width: none;
        }
        .ig-sidebar-sticky::-webkit-scrollbar { display: none; }
    }

    /* Caption "more" clamp */
    .caption-clamp {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Comment input sticky bar */
    .comment-bar-sticky {
        position: sticky;
        bottom: 0;
    }
    @media (min-width: 768px) {
        .comment-bar-sticky {
            position: sticky;
            bottom: 0;
        }
    }

    /* Hide native video controls */
    video::-webkit-media-controls { display: none !important; }
    video::-webkit-media-controls-enclosure { display: none !important; }

    /* ── Frosted Glass Pills ─────────────────────────────────────────────── */
    .pill-frosted {
        background: rgba(255, 255, 255, 0.52);
        backdrop-filter: blur(22px) saturate(1.9) brightness(1.05);
        -webkit-backdrop-filter: blur(22px) saturate(1.9) brightness(1.05);
        border: 1px solid rgba(255, 255, 255, 0.65);
        box-shadow:
            0 6px 28px rgba(0, 0, 0, 0.07),
            inset 0 1px 0 rgba(255, 255, 255, 0.85),
            inset 0 -1px 0 rgba(0, 0, 0, 0.04);
        position: relative;
    }
    /* Subtle noise grain overlay for texture */
    .pill-frosted::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)' opacity='0.045'/%3E%3C/svg%3E");
        pointer-events: none;
        z-index: 0;
    }
    .pill-frosted > * { position: relative; z-index: 1; }

    .dark .pill-frosted {
        background: rgba(18, 18, 18, 0.42);
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow:
            0 6px 28px rgba(0, 0, 0, 0.35),
            inset 0 1px 0 rgba(255, 255, 255, 0.08),
            inset 0 -1px 0 rgba(0, 0, 0, 0.3);
    }
</style>
@endpush

@section('content')
{{-- ROOT ALPINE STATE --}}
<div x-data="{
    /* ── Like ── */
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

    /* ── Follow ── */
    isFollowing: {{ auth()->check() && $photo->user && auth()->user()->isFollowing($photo->user) ? 'true' : 'false' }},
    isFollowingLoading: false,
    toggleFollow() {
        this.isFollowingLoading = true;
        axios.post('{{ $photo->user ? route('user.follow', $photo->user->username) : '#' }}')
            .then(res => this.isFollowing = res.data.following)
            .finally(() => this.isFollowingLoading = false);
    },

    /* ── Share ── */
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

    /* ── Boards / Save ── */
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

{{-- =====================================================================
     MOBILE BACK BUTTON (hidden on desktop)
     ===================================================================== --}}
<div class="fixed top-[92px] left-4 z-[70] md:hidden pointer-events-none">
    <button onclick="history.back()"
        class="w-10 h-10 bg-white/60 dark:bg-black/40 backdrop-blur-xl rounded-full flex items-center justify-center shadow-lg shadow-black/10 border border-white/60 dark:border-white/10 active:scale-95 transition-all text-dark dark:text-white pointer-events-auto">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>
</div>

{{-- =====================================================================
     MAIN IG POST CARD
     ===================================================================== --}}
<div class="ig-post-container py-4 md:py-10">
    <div class="bg-white dark:bg-[#0a0a0a] md:rounded-2xl md:border md:border-gray-200 dark:border-white/10 overflow-hidden">

        <div class="ig-post-desktop">

            {{-- ================================================================
                 LEFT COLUMN — Media
                 ================================================================ --}}
            <div class="relative bg-black flex items-center justify-center min-h-[60vw] md:min-h-0"
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

                {{-- Colour splash while loading --}}
                <div class="absolute inset-0 transition-opacity duration-700"
                     :class="loaded ? 'opacity-0 pointer-events-none' : 'opacity-100'"
                     style="background-color: {{ $photo->dominant_color ?? '#111' }};"></div>

                @if($photo->isVideo())
                    {{-- ── VIDEO PLAYER ── --}}
                    <div class="relative w-full group/vp"
                         :class="fullscreen ? 'fixed inset-0 z-[9999] bg-black w-screen h-screen' : 'w-full'"
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
                         x-init="
                             document.addEventListener('fullscreenchange', () => {
                                 fullscreen = !!document.fullscreenElement;
                             });
                         "
                         @dblclick="$dispatch('double-tap-like')">

                        <video x-ref="vid"
                               src="{{ $photo->image_url }}"
                               class="w-full object-contain cursor-pointer"
                               :class="fullscreen ? 'h-screen max-h-screen' : 'max-h-[75vw] md:max-h-[70vh]'"
                               autoplay muted loop playsinline
                               @click="togglePlay"
                               @loadedmetadata="duration = $el.duration"
                               @timeupdate="currentTime = $el.currentTime; progress = duration ? (currentTime / duration) * 100 : 0"
                               x-on:loadeddata="loaded = true; $dispatch('video-loaded')"
                               x-on:error="error = true; loaded = false">
                        </video>

                        {{-- Controls overlay --}}
                        <div class="absolute bottom-0 left-0 right-0 px-4 pb-4 pt-10
                                    bg-gradient-to-t from-black/80 via-black/20 to-transparent
                                    opacity-0 group-hover/vp:opacity-100 transition-opacity duration-300 z-20"
                             :class="fullscreen ? 'opacity-100' : ''">

                            {{-- Progress bar --}}
                            <div x-ref="pgBar" @click="seek" class="relative w-full py-2 cursor-pointer group/pg mb-3">
                                <div class="w-full h-[3px] bg-white/30 rounded-full">
                                    <div class="h-full rounded-full bg-white transition-all"
                                         :style="`width: ${progress}%`"></div>
                                </div>
                                <div class="absolute top-1/2 -translate-y-1/2 w-3 h-3 bg-white rounded-full shadow pointer-events-none opacity-0 group-hover/pg:opacity-100 transition-opacity"
                                     :style="`left: calc(${progress}% - 6px)`"></div>
                            </div>

                            {{-- Buttons row --}}
                            <div class="flex items-center justify-between text-white text-xs select-none">
                                <div class="flex items-center gap-4">
                                    {{-- Play/Pause --}}
                                    <button @click="togglePlay" class="hover:opacity-70 transition-opacity">
                                        <svg x-show="!playing" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        <svg x-show="playing"  class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                    </button>
                                    {{-- Time --}}
                                    <span class="font-mono text-[11px] text-white/80">
                                        <span x-text="formatTime(currentTime)">0:00</span>
                                        <span class="opacity-40">/</span>
                                        <span x-text="formatTime(duration)">0:00</span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-4">
                                    {{-- Mute --}}
                                    <button @click="toggleMute" class="hover:opacity-70 transition-opacity">
                                        <svg x-show="muted" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.21.05-.42.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"/></svg>
                                        <svg x-show="!muted" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>
                                    </button>
                                    {{-- Fullscreen --}}
                                    <button @click="toggleFullscreen" class="hover:opacity-70 transition-opacity">
                                        <svg x-show="!fullscreen" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/></svg>
                                        <svg x-show="fullscreen"  class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Play/pause big tap feedback --}}
                        <div x-show="!playing"
                             class="absolute inset-0 flex items-center justify-center pointer-events-none z-10">
                            <div class="w-16 h-16 rounded-full bg-black/40 flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                        </div>
                    </div>

                @else
                    {{-- ── IMAGE with double-tap ── --}}
                    <div class="relative w-full" @dblclick="$dispatch('double-tap-like')">
                        <img x-ref="mainImage"
                             src="{{ $photo->image_url }}"
                             alt="{{ $photo->title }}"
                             class="w-full h-auto object-contain max-h-[90vw] md:max-h-[80vh] opacity-0 transition-opacity duration-700"
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

                {{-- ================================================================
                     DESKTOP OVERLAY — header, actions, caption, comment input
                     float directly on top of the photo (frosted glass over media).
                     Mobile is untouched — it keeps the original sidebar below.
                     ================================================================ --}}
                <div class="hidden md:block absolute inset-x-0 top-0 z-30 pointer-events-none">
                    <div class="pointer-events-auto">
                        @include('photos.partials.photo-header', ['suffix' => '-d', 'wrapperClass' => 'absolute inset-x-0 top-0 z-30 p-3'])
                    </div>
                </div>

                <div class="hidden md:block absolute inset-x-0 bottom-0 z-30 pointer-events-none bg-gradient-to-t from-black/50 via-black/10 to-transparent pt-16">
                    <div class="pointer-events-auto">
                        @include('photos.partials.photo-actions', ['suffix' => '-d'])
                        @include('photos.partials.photo-caption', ['suffix' => '-d'])
                    </div>
                </div>
            </div>

            {{-- ================================================================
                 RIGHT COLUMN — Info sidebar
                 Mobile: shows everything as original (header/actions/caption/
                 input/comments all here, untouched).
                 Desktop: only comments stay here — header/actions/caption/
                 input moved to the overlay above instead.
                 ================================================================ --}}
            <div class="ig-sidebar-sticky flex flex-col border-t border-gray-100 dark:border-white/5 md:border-t-0 md:border-l md:border-gray-200 dark:border-white/10">

                {{-- ── HEADER (sticky within sidebar) — mobile only ── --}}
                <div class="md:hidden">
                    @include('photos.partials.photo-header')
                </div>

                {{-- ── SCROLLABLE BODY ── --}}
                <div class="flex-1 overflow-y-auto scrollbar-hide">

                    {{-- ── ACTION BAR, CAPTION, COMMENT INPUT — mobile only ── --}}
                    <div class="md:hidden">
                        @include('photos.partials.photo-actions')
                        @include('photos.partials.photo-caption')
                    </div>

                    {{-- ── COMMENTS (always here, both mobile and desktop) ── --}}
                    @include('photos.partials.photo-comments')

                </div>

            </div>{{-- /right col --}}
        </div>{{-- /ig-post-desktop --}}
    </div>{{-- /card --}}
</div>{{-- /ig-post-container --}}

{{-- =====================================================================
     RELATED PHOTOS
     ===================================================================== --}}
@if($relatedPhotos->count() > 0)
<div class="mt-16 text-center px-4 md:px-0">
    <h2 class="text-xl font-bold text-dark dark:text-white mb-8 tracking-tight">Eksplorasi Lainnya</h2>
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

{{-- =====================================================================
     MODALS
     ===================================================================== --}}

{{-- ── Share Modal ── --}}
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
        <div class="w-10 h-1 bg-gray-300 dark:bg-gray-600 rounded-full mx-auto mt-3 mb-1 sm:hidden"></div>
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
            <h3 class="text-base font-bold text-dark dark:text-white">Bagikan</h3>
            <button @click="isShareModalOpen = false" class="w-7 h-7 flex items-center justify-center rounded-full bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-white/20 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-4 gap-3 mb-5">
                <a :href="'https://wa.me/?text=' + encodeURIComponent('Lihat ini di Bloxpin: ' + shareUrl)" target="_blank" class="flex flex-col items-center gap-1.5 group">
                    <div class="w-14 h-14 rounded-2xl bg-[#25D366] text-white flex items-center justify-center group-hover:scale-105 transition-transform">
                        <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">WhatsApp</span>
                </a>
                <a :href="'https://twitter.com/intent/tweet?text=' + encodeURIComponent('Lihat ini di Bloxpin: ') + '&url=' + encodeURIComponent(shareUrl)" target="_blank" class="flex flex-col items-center gap-1.5 group">
                    <div class="w-14 h-14 rounded-2xl bg-black text-white flex items-center justify-center group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">X</span>
                </a>
                <a :href="'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(shareUrl)" target="_blank" class="flex flex-col items-center gap-1.5 group">
                    <div class="w-14 h-14 rounded-2xl bg-[#1877F2] text-white flex items-center justify-center group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Facebook</span>
                </a>
                <a :href="'mailto:?subject=' + encodeURIComponent('Lihat ini di Bloxpin') + '&body=' + encodeURIComponent('Lihat foto menarik ini: ' + shareUrl)" class="flex flex-col items-center gap-1.5 group">
                    <div class="w-14 h-14 rounded-2xl bg-gray-500 text-white flex items-center justify-center group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Email</span>
                </a>
            </div>

            {{-- Copy Link --}}
            <div class="flex items-center gap-2 bg-gray-100 dark:bg-white/5 rounded-2xl px-4 py-3 mb-4">
                <span class="flex-1 text-xs text-gray-500 dark:text-gray-400 truncate" x-text="shareUrl"></span>
                <button @click="copyLink()" class="text-xs font-bold text-blue-500 hover:text-blue-600 transition-colors whitespace-nowrap">Salin</button>
            </div>

            {{-- Embed --}}
            <div x-data="{ embedCode: '<iframe src=\'{{ route('photos.embed', $photo->uid) }}\' width=\'100%\' height=\'500\' frameborder=\'0\' allowfullscreen></iframe>' }">
                <p class="text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Embed</p>
                <div class="flex items-center gap-2 bg-gray-100 dark:bg-white/5 rounded-2xl px-4 py-3">
                    <code class="flex-1 text-[10px] text-gray-500 dark:text-gray-400 font-mono truncate" x-text="embedCode"></code>
                    <button @click="navigator.clipboard.writeText(embedCode); window.showToast('Kode embed disalin!');" class="text-xs font-bold text-blue-500 hover:text-blue-600 transition-colors whitespace-nowrap">Salin</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Report Modal ── --}}
<div x-show="isReportModalOpen" style="display:none;"
     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div @click.away="isReportModalOpen = false"
         class="w-full max-w-md bg-white dark:bg-[#1c1c1e] rounded-3xl shadow-2xl overflow-hidden">
        <div class="p-6">
            <h3 class="text-lg font-bold text-dark dark:text-white text-center mb-1">Laporkan Postingan</h3>
            <p class="text-xs text-gray-400 text-center mb-6 uppercase tracking-widest">Bantu kami menjaga Bloxpin tetap aman</p>
            <form action="{{ route('photos.report', $photo) }}" method="POST" class="space-y-3">
                @csrf
                <select name="reason" required class="w-full bg-gray-100 dark:bg-white/10 border-none rounded-2xl px-4 py-3 text-sm text-dark dark:text-white focus:ring-2 focus:ring-red-400 transition-all">
                    <option value="">Pilih alasan...</option>
                    <option value="spam">Spam atau Penipuan</option>
                    <option value="inappropriate">Konten Tidak Pantas</option>
                    <option value="violence">Kekerasan atau Ancaman</option>
                    <option value="copyright">Pelanggaran Hak Cipta</option>
                    <option value="harassment">Pelecehan atau Bullying</option>
                    <option value="other">Alasan Lainnya</option>
                </select>
                <textarea name="description" placeholder="Ceritakan lebih lanjut..." class="w-full bg-gray-100 dark:bg-white/10 border-none rounded-2xl px-4 py-3 text-sm text-dark dark:text-white focus:ring-2 focus:ring-red-400 min-h-[80px] resize-none"></textarea>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="isReportModalOpen = false" class="flex-1 py-3 rounded-2xl font-bold text-sm text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5 transition-all">Batal</button>
                    <button type="submit" class="flex-1 py-3 rounded-2xl font-bold text-sm bg-red-500 text-white hover:bg-red-600 transition-all">Kirim</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Collection Modal ── --}}
<div x-show="isCollectionModalOpen" style="display:none;"
     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div @click.away="isCollectionModalOpen = false"
         class="w-full max-w-md bg-white dark:bg-[#1c1c1e] rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
            <h3 class="font-bold text-dark dark:text-white">Simpan ke Koleksi</h3>
            <button @click="isCollectionModalOpen = false" class="text-gray-400 hover:text-dark dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-3">
            <div x-data="{ showForm: false, title: '' }">
                <button x-show="!showForm" @click="showForm = true"
                    class="w-full py-3 border-2 border-dashed border-gray-200 dark:border-white/20 rounded-2xl text-gray-400 hover:border-blue-400 hover:text-blue-500 transition-all flex items-center justify-center gap-2 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Buat Koleksi Baru
                </button>
                <div x-show="showForm" class="bg-gray-100 dark:bg-white/5 p-4 rounded-2xl">
                    <input x-model="title" type="text" placeholder="Nama koleksi..." class="w-full bg-white dark:bg-white/10 border-none rounded-xl px-4 py-2.5 text-sm text-dark dark:text-white mb-3 focus:ring-2 focus:ring-blue-400 transition-all">
                    <div class="flex gap-2">
                        <button @click="showForm = false" class="flex-1 py-2 text-xs text-gray-500 font-medium">Batal</button>
                        <button @click="if(!title) return; axios.post('{{ route('collections.store') }}', { title }).then(res => { window.showToast(res.data.message); fetchCollections(); title=''; showForm=false; });"
                            class="flex-1 py-2 bg-blue-500 text-white rounded-xl text-xs font-bold hover:bg-blue-600 transition-all">Buat</button>
                    </div>
                </div>
            </div>
            <template x-for="collection in userCollections" :key="collection.id">
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-white/5 rounded-2xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gray-200 dark:bg-white/10 flex items-center justify-center overflow-hidden">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9l-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p x-text="collection.title" class="font-semibold text-sm text-dark dark:text-white"></p>
                            <p x-text="collection.photos_count + ' foto'" class="text-xs text-gray-400"></p>
                        </div>
                    </div>
                    <button @click="toggleInCollection(collection)"
                        :class="collection.is_attached ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300'"
                        class="px-3 py-1.5 rounded-full text-xs font-bold transition-all active:scale-90">
                        <span x-text="collection.is_attached ? 'Tersimpan' : 'Simpan'"></span>
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>

{{-- ── Board Modal ── --}}
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
        <div class="w-10 h-1 bg-gray-300 dark:bg-gray-600 rounded-full mx-auto mt-3 mb-1 sm:hidden"></div>
        <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
            <span class="font-bold text-dark dark:text-white">Simpan ke Board</span>
            <button @click="showBoards = false" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-black/5 dark:hover:bg-white/10 text-gray-400 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="max-h-72 overflow-y-auto p-2">
            <template x-for="board in boards" :key="board.id">
                <button @click="saveToBoard(board.id)"
                    class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5 flex items-center gap-3 transition-colors rounded-2xl">
                    <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-white/10 shrink-0 overflow-hidden">
                        <template x-if="board.cover_image">
                            <img :src="'/storage/' + board.cover_image" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!board.cover_image">
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </div>
                        </template>
                    </div>
                    <span x-text="board.title" class="font-semibold text-sm text-dark dark:text-white truncate"></span>
                </button>
            </template>
            <template x-if="boards.length === 0">
                <div class="text-center py-8 text-sm text-gray-400">Belum ada board. Buat board dulu.</div>
            </template>
        </div>
    </div>
</div>

</div>{{-- /root x-data --}}
@endsection