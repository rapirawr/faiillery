@extends('layouts.app')

@section('title', $photo->title . ' - Bloxpin')
@section('meta_description', str()->limit($photo->description, 160) ?: 'Temukan momen menarik ini di Bloxpin.')
@section('meta_image', $photo->image_url)
@section('meta_image_width', $photo->width ?? '1200')
@section('meta_image_height', $photo->height ?? '630')

@section('content')
<div class="max-w-6xl mx-auto px-0 sm:px-4 py-4 md:py-8" x-data="{ 
    liked: {{ auth()->check() && auth()->user()->hasLiked($photo) ? 'true' : 'false' }}, 
    likesCount: {{ $photo->likes_count }},
    isFollowing: {{ auth()->check() && $photo->user && auth()->user()->isFollowing($photo->user) ? 'true' : 'false' }},
    isFollowingLoading: false,
    shareUrl: window.location.href,
    isShareModalOpen: false,
    isReportModalOpen: false,
    init() {
        @auth this.fetchCollections(); @endauth
    },
    isCollectionModalOpen: false,
    userCollections: [],
    fetchCollections() {
        axios.get('{{ route('collections.index') }}?photo_id={{ $photo->id }}').then(res => {
            this.userCollections = res.data;
        });
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
                collection.photos_count = res.data.attached ? collection.photos_count + 1 : Math.max(0, collection.photos_count - 1);
            });
    }
}">
    <!-- Back Button (Mobile Fixed) -->
    <div class="fixed top-[92px] left-4 z-[70] md:hidden pointer-events-none">
        <button onclick="history.back()" class="w-10 h-10 bg-white/40 dark:bg-black/30 backdrop-blur-xl backdrop-saturate-150 rounded-full flex items-center justify-center shadow-lg shadow-black/10 border border-white/60 dark:border-white/10 active:scale-95 transition-all text-dark dark:text-white pointer-events-auto">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
        </button>
    </div>

    <!-- Main Card -->
    <div class="bg-white/70 dark:bg-black/40 backdrop-blur-2xl backdrop-saturate-150 md:rounded-[32px] shadow-xl shadow-black/5 dark:shadow-black/40 border border-white/60 dark:border-white/10 flex flex-col md:flex-row relative z-20 transition-colors md:overflow-hidden"
        
        <!-- Left: Image -->
        <div class="w-full md:w-1/2 bg-light dark:bg-[#0A0A0A] flex items-center justify-center p-0 md:p-8 shrink-0 min-h-[40vh] md:min-h-[50vh] transition-colors border-r border-white/40 dark:border-white/10 relative overflow-hidden"
             x-data="{ 
                loaded: false,
                error: false,
                checkLoad() {
                    if (this.$refs.mainImage.complete) {
                        this.loaded = true;
                    }
                }
             }"
             x-init="checkLoad()">
            
            <!-- Dominant Color Placeholder (Fades out) -->
            <div class="absolute inset-0 transition-opacity duration-1000"
                 :class="loaded ? 'opacity-0' : 'opacity-100'"
                 style="background-color: {{ $photo->dominant_color ?? '#e0e0e0' }};">
                 <!-- Error state -->
                 <template x-if="error">
                     <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 p-4">
                         <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                         <span class="text-xs font-bold uppercase tracking-widest text-center">Gagal memuat gambar</span>
                     </div>
                 </template>
            </div>

            <img x-ref="mainImage" src="{{ $photo->image_url }}" alt="{{ $photo->title }}" 
                 class="w-full h-auto object-contain md:rounded-2xl max-h-[70vh] md:max-h-[80vh] opacity-0 transition-opacity duration-700 relative z-10"
                 :class="{ 'opacity-100': loaded }"
                 x-on:load="loaded = true"
                 x-on:error="error = true; loaded = false">
        </div>

        <!-- Right: Info -->
        <div class="w-full md:w-1/2 p-6 md:p-10 flex flex-col pt-8 md:pt-10 relative">
            
            <!-- Desktop Top Actions (Save) -->
            <div class="hidden md:flex justify-between items-center mb-6">
                <div class="flex items-center gap-4">
                    <!-- Actions Menu -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="w-10 h-10 rounded-full hover:bg-black/5 dark:hover:bg-white/10 flex items-center justify-center transition-colors text-dark dark:text-white">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute top-full left-0 mt-2 w-56 bg-white/70 dark:bg-black/50 backdrop-blur-2xl backdrop-saturate-150 rounded-3xl shadow-2xl shadow-black/20 border border-white/60 dark:border-white/10 py-2 z-50" style="display: none;">
                            <button @click="
                                navigator.clipboard.writeText(window.location.href);
                                window.showToast('Tautan disalin!');
                                open = false;
                            " class="w-full text-left px-4 py-3 hover:bg-black/5 dark:hover:bg-white/10 flex items-center gap-3 text-dark dark:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                <span class="font-medium">Salin Tautan</span>
                            </button>
                            
                            <a href="{{ route('photos.download', $photo) }}" class="w-full text-left px-4 py-3 hover:bg-black/5 dark:hover:bg-white/10 flex items-center gap-3 text-dark dark:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                <span class="font-medium">Unduh Gambar</span>
                            </a>

                            <button @click="isShareModalOpen = true; open = false" class="w-full text-left px-4 py-3 hover:bg-black/5 dark:hover:bg-white/10 flex items-center gap-3 text-dark dark:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                                <span class="font-medium">Embed Postingan</span>
                            </button>

                            @auth
                            @if(auth()->id() !== $photo->user_id)
                                <button @click="isReportModalOpen = true; open = false" class="w-full text-left px-4 py-3 hover:bg-red-50 dark:hover:bg-red-900/10 flex items-center gap-3 text-red-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    <span class="font-medium">Laporkan Postingan</span>
                                </button>
                            @endif
                            @endauth

                            @can('update', $photo)
                                <div class="h-px bg-borderlight dark:bg-borderdark my-1"></div>
                                <a href="{{ route('photos.edit', $photo) }}" class="w-full text-left px-4 py-3 hover:bg-black/5 dark:hover:bg-white/10 flex items-center gap-3 text-dark dark:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    <span class="font-medium">Edit Postingan</span>
                                </a>
                                
                                <form action="{{ route('photos.destroy', $photo) }}" method="POST" @submit.prevent="window.appConfirm('Hapus Postingan', 'Apakah Anda yakin ingin menghapus postingan ini?', () => $el.submit(), 'Hapus')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full text-left px-4 py-3 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-3 text-red-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        <span class="font-medium">Hapus</span>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                    <!-- Share -->
                    <button @click="isShareModalOpen = true" class="w-10 h-10 rounded-full hover:bg-black/5 dark:hover:bg-white/10 flex items-center justify-center transition-colors text-dark dark:text-white">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z"/></svg>
                    </button>

                    <!-- Collection -->
                    @auth
                    <button @click="isCollectionModalOpen = true" class="w-10 h-10 rounded-full hover:bg-black/5 dark:hover:bg-white/10 flex items-center justify-center transition-colors text-dark dark:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
                    </button>
                    @endauth
                </div>

                @auth
                <div x-data="{ 
                    showBoards: false, 
                    pinned: {{ auth()->user()->hasPinned($photo) ? 'true' : 'false' }},
                    boards: {{ $userBoards ? $userBoards->toJson() : '[]' }},
                    saving: false
                }" class="flex items-center relative">
                    <button @click="showBoards = !showBoards" class="flex justify-between items-center gap-2 bg-black/5 dark:bg-white/10 backdrop-blur-sm hover:bg-gray-200 dark:hover:bg-gray-700 px-4 py-2.5 rounded-l-lg font-semibold text-dark dark:text-white transition-colors border border-white/40 dark:border-white/10 border-r-0">
                        <span class="truncate max-w-[120px] text-sm">Pilih Board</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <button class="bg-dark text-white hover:bg-black dark:bg-white dark:text-dark dark:hover:bg-gray-100 font-semibold px-6 py-2.5 rounded-r-lg transition-colors border border-dark dark:border-white text-sm">
                        Simpan
                    </button>

                    <!-- Dropdown Boards -->
                    <div x-show="showBoards" @click.away="showBoards = false" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute top-full right-0 mt-2 w-64 bg-white/70 dark:bg-black/50 backdrop-blur-2xl backdrop-saturate-150 rounded-xl shadow-xl shadow-black/10 overflow-hidden z-20 border border-white/60 dark:border-white/10" style="display: none;">
                        <div class="p-3 text-sm font-semibold text-center border-b border-white/40 dark:border-white/10 text-dark dark:text-white">Simpan ke board</div>
                        <div class="max-h-64 overflow-y-auto p-2 scroll-smooth" style="-webkit-overflow-scrolling: touch;">
                            <template x-for="board in boards" :key="board.id">
                                <button @click="
                                    saving = true;
                                    axios.post('{{ route('pins.store') }}', { photo_id: {{ $photo->id }}, board_id: board.id })
                                        .then(res => {
                                            window.showToast(res.data.message);
                                            showBoards = false;
                                            pinned = true;
                                        })
                                        .catch(err => {
                                            window.showToast(err.response?.data?.message || 'Gagal menyimpan', 'error');
                                        })
                                        .finally(() => saving = false);
                                " class="w-full text-left px-3 py-2 hover:bg-black/5 dark:hover:bg-white/10 rounded-lg flex items-center justify-between group transition-colors">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <div class="w-10 h-10 bg-black/5 dark:bg-white/10 backdrop-blur-sm rounded border border-white/40 dark:border-white/10 shrink-0">
                                            <template x-if="board.cover_image">
                                                <img :src="'/storage/' + board.cover_image" class="w-full h-full object-cover rounded">
                                            </template>
                                        </div>
                                        <span x-text="board.title" class="font-medium text-sm text-dark dark:text-white truncate"></span>
                                    </div>
                                    <span class="bg-dark text-white dark:bg-white dark:text-dark text-xs font-bold px-3 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">Simpan</span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
                @endauth
                @guest
                <a href="{{ route('login') }}" class="btn-primary">Simpan</a>
                @endguest
            </div>

            <!-- Scrollable Content (Only scrollable on desktop, natural on mobile) -->
            <div class="flex-1 md:overflow-y-auto hide-scrollbar -mx-6 px-6 md:inertia-scroll overscroll-y-contain">
                <!-- Mobile Save (Hidden on desktop) -->
                <div class="md:hidden flex justify-between items-center mb-6 pt-4">
                    <div class="flex gap-2" x-data="{ open: false }">
                        <div class="relative">
                            <button @click="open = !open" class="w-12 h-12 bg-black/5 dark:bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center text-dark dark:text-white transition-colors active:scale-90">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                            </button>

                            <div x-show="open" @click.away="open = false" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute top-full left-0 mt-2 w-64 bg-white/70 dark:bg-black/50 backdrop-blur-2xl backdrop-saturate-150 rounded-3xl shadow-2xl shadow-black/20 border border-white/60 dark:border-white/10 py-2 z-50" style="display: none;">
                                
                                <button @click="copyLink(); open = false" class="w-full text-left px-4 py-4 hover:bg-black/5 dark:hover:bg-white/10 flex items-center gap-3 text-dark dark:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                    <span class="font-bold text-sm">Salin Tautan</span>
                                </button>
                                
                                <a href="{{ route('photos.download', $photo) }}" class="w-full text-left px-4 py-4 hover:bg-black/5 dark:hover:bg-white/10 flex items-center gap-3 text-dark dark:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    <span class="font-bold text-sm">Unduh Gambar</span>
                                </a>

                                <button @click="isShareModalOpen = true; open = false" class="w-full text-left px-4 py-4 hover:bg-black/5 dark:hover:bg-white/10 flex items-center gap-3 text-dark dark:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                                    <span class="font-bold text-sm">Embed Postingan</span>
                                </button>

                                @auth
                                    @if(auth()->id() !== $photo->user_id)
                                        <button @click="isReportModalOpen = true; open = false" class="w-full text-left px-4 py-4 hover:bg-red-50 dark:hover:bg-red-900/10 flex items-center gap-3 text-red-500 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                            <span class="font-bold text-sm">Laporkan</span>
                                        </button>
                                    @endif

                                    @can('update', $photo)
                                        <div class="h-px bg-borderlight dark:bg-borderdark my-1"></div>
                                        <a href="{{ route('photos.edit', $photo) }}" class="w-full text-left px-4 py-4 hover:bg-black/5 dark:hover:bg-white/10 flex items-center gap-3 text-dark dark:text-white transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            <span class="font-bold text-sm">Edit Postingan</span>
                                        </a>
                                        
                                        <form action="{{ route('photos.destroy', $photo) }}" method="POST" @submit.prevent="window.appConfirm('Hapus Postingan', 'Apakah Anda yakin ingin menghapus postingan ini?', () => $el.submit(), 'Hapus')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full text-left px-4 py-4 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-3 text-red-600 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                <span class="font-bold text-sm">Hapus</span>
                                            </button>
                                        </form>
                                    @endcan
                                @endauth
                            </div>
                        </div>
                        
                        <button @click="isShareModalOpen = true" class="w-12 h-12 bg-black/5 dark:bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center text-dark dark:text-white transition-colors active:scale-90">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z"/></svg>
                        </button>
                    </div>
                    @auth
                    <div x-data="{ 
                        showBoards: false, 
                        pinned: {{ auth()->user()->hasPinned($photo) ? 'true' : 'false' }},
                        boards: {{ $userBoards ? $userBoards->toJson() : '[]' }},
                        saving: false
                    }" class="relative">
                        <button @click="showBoards = !showBoards" class="btn-primary py-3 px-6 shadow-lg shadow-pinterest/20 transition-transform active:scale-95">
                            Simpan
                        </button>

                        <!-- Dropdown Boards Mobile -->
                        <div x-show="showBoards" @click.away="showBoards = false" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute top-full right-0 mt-2 w-64 bg-white/70 dark:bg-black/50 backdrop-blur-2xl backdrop-saturate-150 rounded-2xl shadow-xl shadow-black/10 border border-white/60 dark:border-white/10 overflow-hidden z-[60]" style="display: none;">
                            <div class="p-4 text-sm font-bold text-center border-b border-white/40 dark:border-white/10 text-dark dark:text-white bg-black/5 dark:bg-white/5">Simpan ke board</div>
                            <div class="max-h-60 overflow-y-auto p-2 scroll-smooth">
                                <template x-for="board in boards" :key="board.id">
                                    <button @click="
                                        saving = true;
                                        axios.post('{{ route('pins.store') }}', { photo_id: {{ $photo->id }}, board_id: board.id })
                                            .then(res => {
                                                window.showToast(res.data.message);
                                                showBoards = false;
                                                pinned = true;
                                            })
                                            .catch(err => {
                                                window.showToast(err.response?.data?.message || 'Gagal menyimpan', 'error');
                                            })
                                            .finally(() => saving = false);
                                    " class="w-full text-left px-3 py-2 hover:bg-black/5 dark:hover:bg-white/10 rounded-xl flex items-center justify-between group transition-colors">
                                        <div class="flex items-center gap-3 overflow-hidden">
                                            <div class="w-10 h-10 bg-black/5 dark:bg-white/10 backdrop-blur-sm rounded border border-white/40 dark:border-white/10 shrink-0 overflow-hidden flex items-center justify-center">
                                                <template x-if="board.cover_image">
                                                    <img :src="'/storage/' + board.cover_image" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!board.cover_image">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"></path></svg>
                                                </template>
                                            </div>
                                            <span x-text="board.title" class="font-bold text-sm text-dark dark:text-white truncate"></span>
                                        </div>
                                    </button>
                                </template>
                                <template x-if="boards.length === 0">
                                    <div class="text-center py-6 text-gray-500 text-sm">
                                        Belum ada board.<br>Buat board baru dari menu +.
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    @endauth
                    @guest
                    <a href="{{ route('login') }}" class="btn-primary py-3 px-6 shadow-lg shadow-pinterest/20 transition-transform active:scale-95">Simpan</a>
                    @endguest
                </div>

                <div x-data="{ expanded: false, title: {{ json_encode($photo->title) }}, limit: 30 }">
                    <h1 class="text-3xl md:text-4xl font-display font-bold text-dark dark:text-white mb-4 leading-tight cursor-pointer break-all"
                        @click="expanded = !expanded"
                        x-text="expanded || title.length <= limit ? title : title.substring(0, limit) + '...'">
                    </h1>
                </div>
                
                @if($photo->description)
                    <div x-data="{ expanded: false, desc: {{ json_encode($photo->description) }}, limit: 160 }">
                        <p class="text-dark dark:text-gray-300 whitespace-pre-wrap mb-8 text-base md:text-lg leading-relaxed cursor-pointer"
                           @click="expanded = !expanded"
                           x-html="expanded || desc.length <= limit ? desc.replace(/@([a-zA-Z0-9_]+)/g, '<a href=\'/user/$1\' class=\'text-pinterest hover:underline font-bold\'>@$1</a>') : desc.substring(0, limit).replace(/@([a-zA-Z0-9_]+)/g, '<a href=\'/user/$1\' class=\'text-pinterest hover:underline font-bold\'>@$1</a>') + '...'">
                        </p>
                    </div>
                @endif

                @if($photo->user)
                <!-- Uploader Profile -->
                <div class="flex items-center justify-between mb-8">
                        <a href="{{ $photo->user ? route('profile.show', $photo->user) : '#' }}" class="flex items-center gap-3 group">
                        <img src="{{ $photo->user->avatar_url }}" alt="Profile" class="w-12 h-12 rounded-full object-cover ring-1 ring-borderlight dark:ring-borderdark">
                        <div>
                            <div class="font-bold text-dark dark:text-white group-hover:underline flex items-center gap-1.5">
                                {{ $photo->user->name }}
                                @if($photo->user->is_verified)
                                    <x-verified-badge size="w-4 h-4" checkSize="w-2.5 h-2.5" />
                                @endif
                            </div>
                            <!-- <div class="text-sm text-gray-500 dark:text-gray-400">{{ $photo->user->photos()->count() }} unggahan</div> -->
                        </div>
                    </a>
                    <!-- Follow Button (Hide if current user) -->
                    @if(!auth()->check() || (auth()->id() !== $photo->user_id && $photo->user))
                        <button 
                            @auth
                                @click="
                                    isFollowingLoading = true;
                                    axios.post('{{ $photo->user ? route('user.follow', $photo->user->username) : '#' }}')
                                        .then(res => {
                                            isFollowing = res.data.following;
                                        })
                                        .finally(() => isFollowingLoading = false);
                                "
                            @endauth
                            @guest
                                onclick="window.location='{{ route('login') }}'"
                            @endguest
                            :class="isFollowing ? 'bg-black/5 dark:bg-white/10 backdrop-blur-sm text-dark dark:text-white border-white/40 dark:border-white/10' : 'bg-dark dark:bg-white text-white dark:text-dark border-dark dark:border-white'"
                            class="px-6 py-2.5 rounded-full font-bold text-sm transition-all active:scale-95 border disabled:opacity-50"
                            :disabled="isFollowingLoading"
                        >
                            <span x-text="isFollowing ? 'Mengikuti' : 'Ikuti'"></span>
                        </button>
                    @endif
                </div>
                @endif

                <!-- Tags -->
                @if($photo->tags->count() > 0)
                    <div class="flex flex-wrap gap-2 mb-8">
                        @foreach($photo->tags as $tag)
                            <a href="{{ route('search', ['tag' => $tag->slug]) }}" class="px-4 py-2 border border-white/40 dark:border-white/10 hover:bg-black/5 dark:hover:bg-white/10 text-dark dark:text-white rounded-lg text-sm transition-colors font-medium">
                                {{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <!-- Interaction Stats & Like Button -->
                <div class="flex items-center justify-between pt-6 border-t border-white/40 dark:border-white/10">
                    <div class="flex items-center gap-4">
                        <div class="font-display font-bold text-xl text-dark dark:text-white">Komentar</div>
                        <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 text-sm font-bold bg-black/5 dark:bg-white/10 backdrop-blur-sm px-3 py-1.5 rounded-full">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            {{ number_format($photo->views_count) }}
                        </div>
                    </div>
                    
                    <button @auth @click="
                                const previousLiked = liked;
                                const previousCount = likesCount;
                                liked = !liked;
                                likesCount = liked ? likesCount + 1 : Math.max(0, likesCount - 1);
                                
                                axios.post('{{ route('photos.like', $photo) }}')
                                     .then(res => {
                                         liked = res.data.liked;
                                         likesCount = res.data.likes_count;
                                     })
                                     .catch(err => {
                                         // Revert on error
                                         liked = previousLiked;
                                         likesCount = previousCount;
                                         window.showToast('Gagal menyukai foto', 'error');
                                     })
                            " @endauth @guest onclick="window.location='{{ route('login') }}'" @endguest
                            class="flex items-center gap-2 group transition-transform active:scale-90">
                        <div class="w-12 h-12 rounded-full border border-white/40 dark:border-white/10 flex items-center justify-center group-hover:bg-black/5 dark:group-hover:bg-white/10 transition-colors">
                            <svg class="w-6 h-6 transition-colors" :class="liked ? 'text-dark dark:text-white fill-current' : 'text-dark dark:text-white fill-none'" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                        <span class="font-bold text-dark dark:text-white" x-text="likesCount > 0 ? likesCount : ''"></span>
                    </button>
                </div>

                <!-- Comments Section -->
                <div class="mt-8" x-data="{ 
                    comments: {{ $photo->comments->map(fn($c) => ['id' => $c->id, 'body' => $c->body, 'user' => ['name' => $c->user->name, 'avatar_url' => $c->user->avatar_url, 'username' => $c->user->username], 'created_at' => $c->created_at->diffForHumans()])->toJson() }},
                    newComment: '',
                    submitting: false,
                    
                    init() {
                        // Realtime Listen for New Comments via Supabase
                        window.supabase
                            .channel('public:comments')
                            .on('postgres_changes', { 
                                event: 'INSERT', 
                                schema: 'public', 
                                table: 'comments',
                                filter: `photo_id=eq.{{ $photo->id }}`
                            }, async (payload) => {
                                if (!this.comments.find(c => c.id === payload.new.id)) {
                                    // Refresh logic or fetch single comment
                                }
                            })
                            .subscribe();
                    },

                    postComment() {
                        if (!this.newComment.trim()) return;
                        this.submitting = true;
                        axios.post('{{ route('comments.store', $photo) }}', { body: this.newComment })
                            .then(res => {
                                this.comments.unshift({
                                    id: res.data.comment.id,
                                    body: res.data.comment.body,
                                    user: res.data.user,
                                    created_at: 'Baru saja'
                                });
                                this.newComment = '';
                                window.showToast('Komentar terkirim!');
                            })
                            .catch(err => {
                                window.showToast(err.response?.data?.message || 'Gagal mengirim komentar', 'error');
                            })
                            .finally(() => this.submitting = false);
                    },

                    deleteComment(id) {
                        window.appConfirm('Hapus Komentar', 'Apakah Anda yakin ingin menghapus komentar ini?', () => {
                            axios.delete('/comments/' + id)
                                .then(res => {
                                    this.comments = this.comments.filter(c => c.id !== id);
                                    window.showToast('Komentar dihapus!');
                                })
                                .catch(err => {
                                    window.showToast(err.response?.data?.message || 'Gagal menghapus komentar', 'error');
                                });
                        }, 'Hapus');
                    }
                }">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-bold text-dark dark:text-white"><span x-text="comments.length"></span> Komentar</h3>
                    </div>

                    <!-- Comment Input -->
                    @auth
                        <div class="flex gap-4 mb-8">
                            <img src="{{ auth()->user()->avatar_url }}" class="w-10 h-10 rounded-full shrink-0 object-cover ring-1 ring-borderlight dark:ring-borderdark">
                            <div class="flex-1 relative">
                                <textarea 
                                    x-model="newComment"
                                    @keydown.enter.prevent="postComment()"
                                    placeholder="Tambahkan komentar..." 
                                    class="w-full bg-black/5 dark:bg-white/10 backdrop-blur-sm border-none rounded-2xl py-3 px-4 text-sm focus:ring-2 focus:ring-dark dark:focus:ring-white transition-all resize-none min-h-[44px]"
                                    rows="1"
                                    oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"
                                ></textarea>
                                <div class="flex justify-end mt-2" x-show="newComment.length > 0">
                                    <button @click="postComment()" :disabled="submitting" class="px-4 py-1.5 bg-dark dark:bg-white text-white dark:text-dark rounded-full text-xs font-bold transition-all hover:scale-105 disabled:opacity-50">
                                        Kirim
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endauth
                    @guest
                        <div class="bg-black/5 dark:bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-center mb-8">
                            <p class="text-sm text-gray-500 mb-4">Ingin berdiskusi? Masuk untuk menulis komentar.</p>
                            <a href="{{ route('login') }}" class="px-6 py-2 bg-dark dark:bg-white text-white dark:text-dark rounded-full text-xs font-bold inline-block">Masuk</a>
                        </div>
                    @endguest

                    <!-- Comments List -->
                    <div class="space-y-6">
                        <template x-for="comment in comments" :key="comment.id">
                            <div class="flex gap-4 group animate-fade-in">
                                <a :href="'/user/' + comment.user.username">
                                    <img :src="comment.user.avatar_url" class="w-10 h-10 rounded-full shrink-0 object-cover ring-1 ring-borderlight dark:ring-borderdark">
                                </a>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <a :href="'/user/' + comment.user.username" class="font-bold text-sm text-dark dark:text-white hover:underline" x-text="comment.user.name"></a>
                                        <template x-if="comment.user.is_verified">
                                            <svg class="w-3.5 h-3.5 text-blue-500 fill-current" viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zM10 17l-5-5 1.4-1.4 3.6 3.6 7.6-7.6L19 8l-9 9z"/></svg>
                                        </template>
                                        <span class="text-xs text-gray-400" x-text="comment.created_at"></span>
                                    </div>
                                    <p class="text-sm text-dark dark:text-gray-300 leading-relaxed" 
                                       x-html="comment.body.replace(/@([a-zA-Z0-9_]+)/g, '<a href=\'/user/$1\' class=\'text-pinterest hover:underline font-bold\'>@$1</a>')"></p>
                                    
                                    <div class="flex items-center gap-4 mt-2">
                                        <!-- <button class="text-xs font-bold text-gray-400 hover:text-dark dark:hover:text-white transition-colors">Suka</button>
                                        <button class="text-xs font-bold text-gray-400 hover:text-dark dark:hover:text-white transition-colors">Balas</button> -->
                                        
                                        @auth
                                        <template x-if="comment.user.username === '{{ auth()->user()->username }}'">
                                            <button @click="deleteComment(comment.id)" class="text-xs font-bold text-red-400 opacity-0 group-hover:opacity-100 transition-opacity hover:text-red-600">Hapus</button>
                                        </template>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Photos -->
    @if($relatedPhotos->count() > 0)
    <div class="mt-16 text-center">
        <h2 class="text-2xl md:text-3xl font-display font-bold text-dark dark:text-white mb-8">Eksplorasi Lainnya</h2>
        
        <div class="w-full mx-auto" x-data="{ msnry: null }" x-init="$nextTick(() => {
            const grid = $el.querySelector('.related-grid');
            msnry = new window.Masonry(grid, { itemSelector: '.grid-item', columnWidth: '.w-1\\/2', percentPosition: true });
            msnry.layout();
        })">
            <div class="related-grid w-full -ml-2 sm:-ml-4 text-left">
                <div class="w-1/2 sm:w-1/3 md:w-1/4 lg:w-1/5 xl:w-[16.666%] h-0"></div>
                @foreach($relatedPhotos as $relPhoto)
                    <div class="grid-item w-1/2 sm:w-1/3 md:w-1/4 lg:w-1/5 xl:w-[16.666%] pl-2 sm:pl-4 mb-2 sm:mb-4">
                        @include('components.photo-card', ['photo' => $relPhoto])
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Share Modal -->
    <div x-show="isShareModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-0">
        <!-- Backdrop -->
        <div x-show="isShareModalOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="isShareModalOpen = false" 
             class="fixed inset-0 bg-black/30 backdrop-blur-md"></div>

        <!-- Modal Content -->
        <div x-show="isShareModalOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-white/70 dark:bg-black/50 backdrop-blur-2xl backdrop-saturate-150 rounded-3xl shadow-2xl shadow-black/20 w-full max-w-md overflow-hidden transform transition-all z-10 border border-white/60 dark:border-white/10">
             
             <div class="px-6 py-4 border-b border-white/40 dark:border-white/10 flex items-center justify-between">
                 <h3 class="text-xl font-bold text-dark dark:text-white font-display">Bagikan</h3>
                 <button @click="isShareModalOpen = false" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-black/5 dark:hover:bg-white/10 text-dark dark:text-gray-400 transition-colors">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                 </button>
             </div>
             
             <div class="p-6">
                 <div class="grid grid-cols-4 gap-4 mb-6">
                     <!-- WhatsApp -->
                     <a :href="'https://wa.me/?text=' + encodeURIComponent('Lihat ini di Bloxpin: ' + shareUrl)" target="_blank" class="flex flex-col items-center gap-2 group">
                         <div class="w-12 h-12 rounded-full bg-[#25D366] text-white flex items-center justify-center group-hover:scale-110 transition-transform shadow-md">
                             <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                         </div>
                         <span class="text-xs font-medium text-dark dark:text-gray-300">WhatsApp</span>
                     </a>
                     
                     <!-- Twitter/X -->
                     <a :href="'https://twitter.com/intent/tweet?text=' + encodeURIComponent('Lihat ini di Bloxpin: ') + '&url=' + encodeURIComponent(shareUrl)" target="_blank" class="flex flex-col items-center gap-2 group">
                         <div class="w-12 h-12 rounded-full bg-dark dark:bg-white text-white dark:text-dark flex items-center justify-center group-hover:scale-110 transition-transform shadow-md">
                             <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                         </div>
                         <span class="text-xs font-medium text-dark dark:text-gray-300">X</span>
                     </a>
                     
                     <!-- Facebook -->
                     <a :href="'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(shareUrl)" target="_blank" class="flex flex-col items-center gap-2 group">
                         <div class="w-12 h-12 rounded-full bg-[#1877F2] text-white flex items-center justify-center group-hover:scale-110 transition-transform shadow-md">
                             <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                         </div>
                         <span class="text-xs font-medium text-dark dark:text-gray-300">Facebook</span>
                     </a>
                     
                     <!-- Email -->
                     <a :href="'mailto:?subject=' + encodeURIComponent('Lihat ini di Bloxpin') + '&body=' + encodeURIComponent('Lihat foto menarik ini: ' + shareUrl)" class="flex flex-col items-center gap-2 group">
                         <div class="w-12 h-12 rounded-full bg-gray-500 text-white flex items-center justify-center group-hover:scale-110 transition-transform shadow-md">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                         </div>
                         <span class="text-xs font-medium text-dark dark:text-gray-300">Email</span>
                     </a>
                 </div>
                 
                 <!-- Copy Link -->
                 <div class="mt-4">
                     <p class="text-sm font-bold text-dark dark:text-white mb-2">Atau salin tautan</p>
                     <div class="flex items-center gap-2">
                         <div class="flex-1 bg-black/5 dark:bg-white/5 backdrop-blur-sm rounded-xl px-4 py-3 text-sm text-gray-500 dark:text-gray-400 truncate border border-white/40 dark:border-white/10">
                             <span x-text="shareUrl"></span>
                         </div>
                         <button @click="copyLink()" class="px-6 py-3 bg-dark dark:bg-white text-white dark:text-dark rounded-xl font-bold text-sm hover:scale-105 active:scale-95 transition-all whitespace-nowrap">
                             Salin
                         </button>
                     </div>
                 </div>

                 <!-- Embed Code -->
                 <div class="mt-6 pt-6 border-t border-white/40 dark:border-white/10" x-data="{ 
                     embedCode: '<iframe src=\'{{ route('photos.embed', $photo->uid) }}\' width=\'100%\' height=\'500\' frameborder=\'0\' scrolling=\'no\' allowfullscreen></iframe>' 
                 }">
                     <p class="text-sm font-bold text-dark dark:text-white mb-2 font-display">Embed di website lain</p>
                     <div class="flex items-center gap-2">
                         <div class="flex-1 bg-black/5 dark:bg-white/5 backdrop-blur-sm rounded-xl px-4 py-3 text-[10px] text-gray-500 dark:text-gray-400 font-mono overflow-x-auto whitespace-nowrap border border-white/40 dark:border-white/10 scrollbar-hide">
                             <code x-text="embedCode"></code>
                         </div>
                         <button @click="navigator.clipboard.writeText(embedCode); window.showToast('Kode embed disalin!');" class="px-6 py-3 bg-dark dark:bg-white text-white dark:text-dark rounded-xl font-bold text-sm hover:scale-105 active:scale-95 transition-all whitespace-nowrap">
                             Salin
                         </button>
                     </div>
                 </div>
             </div>
        </div>
    </div>
    <!-- Report Modal -->
    <div x-show="isReportModalOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/30 backdrop-blur-md" style="display: none;">
        
        <div @click.away="isReportModalOpen = false" 
             class="bg-white/70 dark:bg-black/50 backdrop-blur-2xl backdrop-saturate-150 w-full max-w-md rounded-[32px] overflow-hidden shadow-2xl shadow-black/20 border border-white/60 dark:border-white/10 animate-modal-up">
            
            <div class="p-8">
                <h3 class="text-2xl font-black text-dark dark:text-white text-center mb-2">Laporkan Postingan</h3>
                <p class="text-gray-500 text-sm text-center mb-8 uppercase tracking-widest font-bold">Bantu kami menjaga Bloxpin tetap aman</p>

                <form action="{{ route('photos.report', $photo) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest pl-4">Alasan Pelaporan</label>
                        <select name="reason" required class="w-full bg-black/5 dark:bg-white/10 backdrop-blur-sm border-none rounded-2xl px-6 py-4 text-dark dark:text-white focus:ring-2 focus:ring-pinterest transition-all appearance-none">
                            <option value="">Pilih alasan...</option>
                            <option value="spam">Spam atau Penipuan</option>
                            <option value="inappropriate">Konten Tidak Pantas / Dewasa</option>
                            <option value="violence">Kekerasan atau Ancaman</option>
                            <option value="copyright">Pelanggaran Hak Cipta</option>
                            <option value="harassment">Pelecehan atau Bullying</option>
                            <option value="other">Alasan Lainnya</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest pl-4">Detail Tambahan (Opsional)</label>
                        <textarea name="description" placeholder="Ceritakan lebih lanjut..." class="w-full bg-black/5 dark:bg-white/10 backdrop-blur-sm border-none rounded-2xl px-6 py-4 text-dark dark:text-white focus:ring-2 focus:ring-pinterest transition-all min-h-[100px] resize-none"></textarea>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="button" @click="isReportModalOpen = false" class="flex-1 px-6 py-4 rounded-2xl font-bold text-dark dark:text-white hover:bg-gray-100 dark:hover:bg-white/5 transition-all active:scale-95">Batal</button>
                        <button type="submit" class="flex-1 bg-pinterest text-white px-6 py-4 rounded-2xl font-bold hover:bg-pinterest-hover shadow-lg shadow-pinterest/20 transition-all active:scale-95">Kirim Laporan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Collection Modal -->
    <div x-show="isCollectionModalOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/30 backdrop-blur-md" style="display: none;">
        
        <div @click.away="isCollectionModalOpen = false" 
             class="bg-white/70 dark:bg-black/50 backdrop-blur-2xl backdrop-saturate-150 w-full max-w-md rounded-[32px] overflow-hidden shadow-2xl shadow-black/20 border border-white/60 dark:border-white/10 animate-modal-up flex flex-col max-h-[90vh]">
            
            <div class="p-6 border-b border-white/40 dark:border-white/10 flex items-center justify-between">
                <h3 class="text-xl font-bold text-dark dark:text-white">Simpan ke Koleksi</h3>
                <button @click="isCollectionModalOpen = false" class="text-gray-400 hover:text-dark dark:hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-4 hide-scrollbar">
                <!-- Create New Collection Form -->
                <div x-data="{ showForm: false, title: '' }" class="mb-6">
                    <button x-show="!showForm" @click="showForm = true" class="w-full py-4 border-2 border-dashed border-white/50 dark:border-white/20 rounded-2xl text-gray-400 hover:text-pinterest hover:border-pinterest transition-all flex items-center justify-center gap-2 font-bold italic uppercase text-xs tracking-widest">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                        Buat Koleksi Baru
                    </button>
                    
                    <div x-show="showForm" class="bg-black/5 dark:bg-white/5 backdrop-blur-sm p-4 rounded-2xl">
                        <input x-model="title" type="text" placeholder="Nama koleksi..." class="w-full bg-white/60 dark:bg-black/30 backdrop-blur-sm border-none rounded-xl px-4 py-3 text-sm text-dark dark:text-white mb-3 focus:ring-2 focus:ring-pinterest transition-all">
                        <div class="flex gap-2">
                            <button @click="showForm = false" class="flex-1 py-2 text-xs font-bold uppercase tracking-widest text-gray-500 hover:text-dark dark:hover:text-white transition-colors">Batal</button>
                            <button @click="
                                if(!title) return;
                                axios.post('{{ route('collections.store') }}', { title }).then(res => {
                                    window.showToast(res.data.message);
                                    fetchCollections();
                                    title = '';
                                    showForm = false;
                                });
                            " class="flex-1 py-2 bg-pinterest text-white rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-pinterest-hover transition-all">Simpan</button>
                        </div>
                    </div>
                </div>

                <!-- Collection List -->
                <template x-for="collection in userCollections" :key="collection.id">
                    <div class="flex items-center justify-between p-4 bg-black/5 dark:bg-white/5 backdrop-blur-sm rounded-2xl group transition-all hover:bg-black/10 dark:hover:bg-white/10">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-white/60 dark:bg-black/30 backdrop-blur-sm rounded-xl flex items-center justify-center overflow-hidden">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9l-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <h4 x-text="collection.title" class="font-bold text-dark dark:text-white"></h4>
                                <p x-text="collection.photos_count + ' foto'" class="text-[10px] text-gray-500 font-bold uppercase tracking-widest"></p>
                            </div>
                        </div>
                        <button @click="toggleInCollection(collection)" :class="collection.is_attached ? 'bg-pinterest text-white' : 'bg-white/60 dark:bg-black/30 backdrop-blur-sm text-gray-400 border border-white/50 dark:border-white/10'" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest transition-all active:scale-90">
                            <span x-text="collection.is_attached ? 'Tersimpan' : 'Simpan'"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

</div>
@endsection