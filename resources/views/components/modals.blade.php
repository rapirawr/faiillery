<div x-data="appModals"
     @app-confirm.window="openConfirm($event.detail)"
     @app-prompt.window="openPrompt($event.detail)"
     @keydown.escape.window="closeModal()">

    <!-- Confirm Modal (Frosted Glass) -->
    <div x-show="confirmData.show" style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div x-show="confirmData.show" x-transition.opacity class="absolute inset-0 bg-black/40 backdrop-blur-md" @click="closeModal()"></div>
        <div x-show="confirmData.show"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="relative rounded-[20px] bg-white/20 backdrop-blur-xl backdrop-saturate-150 border border-white/20 shadow-2xl p-6 w-[90%] max-w-sm mx-auto z-10 text-center text-white">

            <div class="w-14 h-14 rounded-full bg-white/15 border border-white/20 flex items-center justify-center mx-auto mb-4 shadow-md">
                <template x-if="confirmData.type === 'primary' || confirmData.type === 'alert'">
                    <svg class="w-7 h-7 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
                <template x-if="confirmData.type === 'success'">
                    <svg class="w-7 h-7 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </template>
                <template x-if="confirmData.type !== 'primary' && confirmData.type !== 'alert' && confirmData.type !== 'success'">
                    <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </template>
            </div>

            <h3 class="text-lg font-bold mb-2 text-white" x-text="confirmData.title"></h3>
            <p class="text-sm mb-6 leading-relaxed text-white/80" x-text="confirmData.message"></p>

            <div class="flex gap-3">
                <template x-if="confirmData.type !== 'alert'">
                    <button @click="closeModal()" class="flex-1 py-2.5 px-4 rounded-full font-semibold text-sm bg-white/10 hover:bg-white/20 border border-white/20 text-white transition-all">
                        Batal
                    </button>
                </template>
                <button @click="confirmAction()" class="flex-1 py-2.5 px-4 rounded-full font-bold text-sm bg-[#8B5E3C] hover:bg-[#5C3A21] text-[#FFF8ED] border border-[#C69C6D]/40 shadow-md transition-all active:scale-95">
                    <span x-text="confirmData.confirmText || 'Ya, Lanjutkan'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Prompt Modal (Frosted Glass) -->
    <div x-show="promptData.show" style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div x-show="promptData.show" x-transition.opacity class="absolute inset-0 bg-black/40 backdrop-blur-md" @click="closeModal()"></div>
        <div x-show="promptData.show"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="relative rounded-[20px] bg-white/20 backdrop-blur-xl backdrop-saturate-150 border border-white/20 shadow-2xl p-6 w-[90%] max-w-sm mx-auto z-10 text-center text-white">

            <h3 class="text-lg font-bold mb-2 text-white" x-text="promptData.title"></h3>
            <p class="text-sm mb-4 leading-relaxed text-white/80" x-text="promptData.message"></p>

            <input type="text" 
                   x-model="promptData.input" 
                   x-ref="promptInput"
                   @keydown.enter="promptAction()"
                   class="w-full mb-5 rounded-full px-4 py-2.5 text-sm bg-white/15 hover:bg-white/25 border border-white/20 text-white placeholder-white/60 outline-none transition-all"
                   :placeholder="promptData.placeholder">

            <div class="flex gap-3">
                <button @click="closeModal()" class="flex-1 py-2.5 px-4 rounded-full font-semibold text-sm bg-white/10 hover:bg-white/20 border border-white/20 text-white transition-all">
                    Batal
                </button>
                <button @click="promptAction()" class="flex-1 py-2.5 px-4 rounded-full font-bold text-sm bg-[#8B5E3C] hover:bg-[#5C3A21] text-[#FFF8ED] border border-[#C69C6D]/40 shadow-md transition-all active:scale-95">
                    <span x-text="promptData.confirmText || 'Simpan'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Upload Photo Modal (Frosted Glass) -->
    @if(!request()->routeIs('admin.*'))
        @include('components.frosted.upload-modal')
    @endif

    <!-- Theater Mode Slideshow Overlay (Frosted Glass) -->
    <div x-data="theaterModeData"
         @open-theater.window="open($event.detail)"
         @keydown.right.window="next()"
         @keydown.left.window="prev()"
         @keydown.escape.window="close()"
         x-show="show"
         style="display:none;"
         class="fixed inset-0 z-[120] bg-black/95 backdrop-blur-2xl flex flex-col md:flex-row text-white select-none">
        
        <!-- Left Section: Main Image Carousel Area -->
        <div class="flex-1 relative flex items-center justify-center p-4 min-h-0 bg-black/40">
            
            <!-- Floating Navigation Controls -->
            <!-- Back/Prev Button -->
            <button @click="prev()" class="absolute left-4 z-[150] w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center transition-all active:scale-90 shadow-lg text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            
            <!-- Forward/Next Button -->
            <button @click="next()" class="absolute right-4 z-[150] w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center transition-all active:scale-90 shadow-lg text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            <!-- Main Slide Container -->
            <template x-if="currentPhoto">
                <div class="max-w-full max-h-[85vh] flex items-center justify-center relative overflow-hidden rounded-2xl border border-white/10 shadow-2xl z-10">
                    <!-- Video player support -->
                    <template x-if="currentPhoto.is_video">
                        <video :src="currentPhoto.image_url" 
                               :key="currentPhoto.uid"
                               autoplay muted loop playsinline controls
                               class="max-w-full max-h-[85vh] object-contain rounded-2xl"></video>
                    </template>
                    <!-- Image viewer support -->
                    <template x-if="!currentPhoto.is_video">
                        <img :src="currentPhoto.image_url" 
                             :key="currentPhoto.uid"
                             class="max-w-full max-h-[85vh] object-contain select-none pointer-events-none transition-all duration-300" />
                    </template>
                </div>
            </template>

            <!-- Floating Top Left Indicator -->
            <div class="absolute top-4 left-4 z-[150] flex items-center gap-3">
                <button @click="close()" class="px-4 py-2 rounded-full bg-white/10 hover:bg-white/25 border border-white/20 text-xs font-bold transition-all active:scale-95 shadow-md flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span>Tutup</span>
                </button>
                <div class="px-3.5 py-2 rounded-full bg-white/5 border border-white/10 text-xs font-semibold" x-text="`${index + 1} / ${photos.length}`"></div>
            </div>

            <!-- Floating Bottom Media Controls Area -->
            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 z-[150] flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-xl border border-white/20 shadow-lg">
                <!-- Autoplay Button -->
                <button @click="toggleAutoplay()" class="w-8 h-8 rounded-full flex items-center justify-center text-white transition-all active:scale-90" :class="autoplay ? 'text-amber-400 bg-white/10' : 'hover:bg-white/10'">
                    <template x-if="autoplay">
                        <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
                        </svg>
                    </template>
                    <template x-if="!autoplay">
                        <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/>
                        </svg>
                    </template>
                </button>
                
                <!-- Separator -->
                <div class="h-4 w-px bg-white/20 mx-1"></div>

                <!-- Info Toggle Button -->
                <button @click="showSidebar = !showSidebar" class="w-8 h-8 rounded-full flex items-center justify-center text-white transition-all hover:bg-white/10 active:scale-90" :class="showSidebar ? 'text-amber-400' : ''" title="Toggle Detail Panel">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>
            </div>

        </div>

        <!-- Right Section: Frosted details sidebar -->
        <div x-show="showSidebar"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="w-full md:w-[360px] bg-white/5 border-t md:border-t-0 md:border-l border-white/10 backdrop-blur-xl flex flex-col justify-between p-6 z-10 shrink-0">
            
            <!-- Top Section: Photo Details -->
            <div class="space-y-6">
                <!-- Header: Uploader Info -->
                <template x-if="currentPhoto">
                    <div class="flex items-center gap-3">
                        <img :src="currentPhoto.uploader_avatar" class="w-10 h-10 rounded-full object-cover border border-white/20 shadow-md" />
                        <div class="flex flex-col">
                            <span class="font-bold text-sm leading-tight text-white" x-text="currentPhoto.uploader_name"></span>
                            <span class="text-xs text-white/60 font-medium" x-text="'@' + currentPhoto.uploader_username"></span>
                        </div>
                    </div>
                </template>

                <!-- Divider -->
                <div class="h-px bg-white/10"></div>

                <!-- Info: Title & Description -->
                <template x-if="currentPhoto">
                    <div class="space-y-2">
                        <h2 class="text-lg font-bold leading-snug text-white" x-text="currentPhoto.title || 'Tanpa Judul'"></h2>
                        <p class="text-sm text-white/80 leading-relaxed max-h-[25vh] overflow-y-auto pr-1" x-text="currentPhoto.description || 'Tidak ada deskripsi.'"></p>
                    </div>
                </template>
            </div>

            <!-- Bottom Section: Quick Actions & Navigation Link -->
            <div class="space-y-4 pt-6 border-t border-white/10">
                <!-- Action Buttons: Likes, Shares -->
                <template x-if="currentPhoto">
                    <div class="flex items-center justify-between">
                        <button @click="toggleLike()" class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 hover:bg-white/15 border border-white/10 text-xs font-bold transition-all active:scale-95">
                            <svg class="w-4.5 h-4.5" :class="currentPhoto.is_liked ? 'text-red-500 fill-current' : 'text-white fill-none'" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            <span x-text="`${currentPhoto.likes_count} Suka`"></span>
                        </button>

                        <div class="flex items-center gap-1.5 text-xs text-white/60 font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <span x-text="`${currentPhoto.comments_count} Komentar`"></span>
                        </div>
                    </div>
                </template>

                <!-- Full details redirect link -->
                <template x-if="currentPhoto">
                    <a :href="currentPhoto.detail_url" class="block w-full text-center py-3 rounded-full font-bold text-sm bg-[#8B5E3C] hover:bg-[#5C3A21] text-[#FFF8ED] border border-[#C69C6D]/30 transition-all active:scale-95 shadow-md">
                        Buka Detail Postingan
                    </a>
                </template>
            </div>

        </div>

    </div>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('theaterModeData', () => ({
            show: false,
            photos: [],
            index: 0,
            autoplay: false,
            timer: null,
            showSidebar: true,

            open(detail) {
                this.photos = detail.photos || [];
                this.index = detail.startIndex || 0;
                this.show = true;
                this.showSidebar = true;
                this.autoplay = false;
                this.clearTimer();
            },
            close() {
                this.show = false;
                this.clearTimer();
            },
            next() {
                if (this.photos.length === 0) return;
                this.index = (this.index + 1) % this.photos.length;
                this.resetAutoplay();
            },
            prev() {
                if (this.photos.length === 0) return;
                this.index = (this.index - 1 + this.photos.length) % this.photos.length;
                this.resetAutoplay();
            },
            toggleAutoplay() {
                this.autoplay = !this.autoplay;
                if (this.autoplay) {
                    this.startTimer();
                } else {
                    this.clearTimer();
                }
            },
            startTimer() {
                this.clearTimer();
                this.timer = setInterval(() => {
                    this.next();
                }, 4000);
            },
            clearTimer() {
                if (this.timer) {
                    clearInterval(this.timer);
                    this.timer = null;
                }
            },
            resetAutoplay() {
                if (this.autoplay) {
                    this.startTimer();
                }
            },
            get currentPhoto() {
                return this.photos[this.index] || null;
            },
            toggleLike() {
                const photo = this.currentPhoto;
                if (!photo) return;
                
                axios.post(`/photo/${photo.uid}/like`)
                    .then(res => {
                        photo.is_liked = res.data.liked;
                        photo.likes_count = res.data.likes_count;
                        window.dispatchEvent(new CustomEvent('photo-liked-status-changed', {
                            detail: { uid: photo.uid, liked: res.data.liked, count: res.data.likes_count }
                        }));
                    })
                    .catch(() => {
                        window.showToast('Gagal menyukai foto', 'error');
                    });
            }
        }));
    });
    </script>
</div>
