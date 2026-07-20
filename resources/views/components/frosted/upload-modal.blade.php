<div x-data="{ 
        openModal: false, 
        isDragging: false, 
        file: null, 
        filePreview: null, 
        isVideo: false,
        uploading: false, 
        progress: 0,
        title: '',
        description: '',
        
        // Editor properties
        selectedRatio: 'Bebas',
        selectedFilter: 'Normal',
        imageObj: null,

        handleDrop(e) {
            this.isDragging = false;
            if (e.dataTransfer.files.length > 0) {
                this.setFile(e.dataTransfer.files[0]);
            }
        },
        handleFileSelect(e) {
            if (e.target.files.length > 0) {
                this.setFile(e.target.files[0]);
            }
        },
        setFile(file) {
            this.isVideo = file.type.startsWith('video/');
            if (!file.type.startsWith('image/') && !this.isVideo) {
                window.showToast('Silakan pilih file gambar atau video yang valid.', 'error');
                return;
            }
            this.file = file;
            this.filePreview = URL.createObjectURL(file);
            
            if (!this.isVideo) {
                this.selectedRatio = 'Bebas';
                this.selectedFilter = 'Normal';
                
                // Initialize image object for editor canvas
                const img = new Image();
                img.src = this.filePreview;
                img.onload = () => {
                    this.imageObj = img;
                    this.$nextTick(() => this.applyEdits());
                };
            }
        },
        clearFile() {
            this.file = null;
            this.filePreview = null;
            this.isVideo = false;
            this.imageObj = null;
            this.progress = 0;
            this.uploading = false;
            this.title = '';
            this.description = '';
        },

        // Apply edits to canvas
        applyEdits() {
            if (this.isVideo || !this.imageObj) return;
            const canvas = this.$refs.editorCanvas;
            if (!canvas) return;
            const img = this.imageObj;

            const imgWidth = img.naturalWidth;
            const imgHeight = img.naturalHeight;

            let targetWidth = imgWidth;
            let targetHeight = imgHeight;

            if (this.selectedRatio === '1:1') {
                const size = Math.min(imgWidth, imgHeight);
                targetWidth = size;
                targetHeight = size;
            } else if (this.selectedRatio === '4:5') {
                if (imgWidth / imgHeight > 4 / 5) {
                    targetWidth = imgHeight * (4 / 5);
                    targetHeight = imgHeight;
                } else {
                    targetWidth = imgWidth;
                    targetHeight = imgWidth * (5 / 4);
                }
            } else if (this.selectedRatio === '16:9') {
                if (imgWidth / imgHeight > 16 / 9) {
                    targetWidth = imgHeight * (16 / 9);
                    targetHeight = imgHeight;
                } else {
                    targetWidth = imgWidth;
                    targetHeight = imgWidth * (9 / 16);
                }
            }

            const sx = (imgWidth - targetWidth) / 2;
            const sy = (imgHeight - targetHeight) / 2;

            // Limit destination canvas size to a premium standard size
            const maxDimension = 1200;
            let destWidth = targetWidth;
            let destHeight = targetHeight;
            if (Math.max(targetWidth, targetHeight) > maxDimension) {
                const scale = maxDimension / Math.max(targetWidth, targetHeight);
                destWidth = targetWidth * scale;
                destHeight = targetHeight * scale;
            }

            canvas.width = destWidth;
            canvas.height = destHeight;

            const ctx = canvas.getContext('2d');
            
            // Set canvas filter
            let filterStr = 'none';
            if (this.selectedFilter === 'Vintage') {
                filterStr = 'sepia(0.4) contrast(1.1) saturate(0.9)';
            } else if (this.selectedFilter === 'Lomo') {
                filterStr = 'saturate(1.3) contrast(1.25) sepia(0.1)';
            } else if (this.selectedFilter === 'Noir') {
                filterStr = 'grayscale(1) contrast(1.3)';
            } else if (this.selectedFilter === 'Cool') {
                filterStr = 'hue-rotate(-20deg) saturate(0.95) brightness(1.02)';
            }
            
            ctx.filter = filterStr;
            ctx.drawImage(img, sx, sy, targetWidth, targetHeight, 0, 0, destWidth, destHeight);
        },

        // Real upload function with progress bar tracking
        uploadFile() {
            if (!this.file) return;
            this.uploading = true;
            this.progress = 0;

            const performUpload = (fileBlob) => {
                const formData = new FormData();
                formData.append('image', fileBlob, this.isVideo ? this.file.name : 'upload.jpg');
                formData.append('title', this.title);
                formData.append('description', this.description);

                axios.post('{{ route('photos.store') }}', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    },
                    onUploadProgress: (progressEvent) => {
                        this.progress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    }
                })
                .then(res => {
                    this.uploading = false;
                    this.openModal = false;
                    this.clearFile();
                    if (window.showToast) window.showToast('Karya berhasil diunggah!');
                    if (res.data.redirect) {
                        setTimeout(() => { window.location.href = res.data.redirect; }, 600);
                    }
                })
                .catch(err => {
                    this.uploading = false;
                    this.progress = 0;
                    const errMsg = err.response?.data?.message || 'Gagal mengunggah file.';
                    window.showToast(errMsg, 'error');
                });
            };

            if (this.isVideo) {
                // Videos bypass crop/filter canvas
                performUpload(this.file);
            } else {
                // Export canvas content as JPEG Blob for storage efficiency
                const canvas = this.$refs.editorCanvas;
                if (canvas) {
                    canvas.toBlob((blob) => {
                        performUpload(blob);
                    }, 'image/jpeg', 0.9);
                } else {
                    performUpload(this.file);
                }
            }
        }
     }"
     @open-upload-modal.window="openModal = true"
     @keydown.escape.window="if (openModal && !uploading) { openModal = false; clearFile(); }">

    <!-- Trigger Button Standalone -->
    <button @click="openModal = true" 
            class="px-6 py-3 rounded-full font-bold text-sm bg-white/20 hover:bg-white/30 backdrop-blur-xl border border-white/30 text-white shadow-lg shadow-black/10 flex items-center gap-2 transition-all active:scale-95">
        <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        <span>Unggah Karya Baru</span>
    </button>

    <!-- Modal Backdrop & Container -->
    <div x-show="openModal" 
         style="display: none;"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 md:p-6 overflow-y-auto">

        <!-- Dark Blurred Backdrop Overlay -->
        <div x-show="openModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="if (!uploading) { openModal = false; clearFile(); }"
             class="fixed inset-0 bg-black/45 backdrop-blur-md transition-opacity"></div>

        <!-- Frosted Glass Modal Panel -->
        <div x-show="openModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative w-full max-w-xl bg-[#1c1611]/85 backdrop-blur-xl border border-white/10 rounded-[20px] shadow-2xl p-6 md:p-8 z-10 text-white overflow-hidden max-h-[90vh] overflow-y-auto scrollbar-thin">

            <!-- Close Header Button -->
            <button @click="openModal = false; clearFile();" 
                    type="button"
                    :disabled="uploading"
                    class="absolute top-5 right-5 w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 text-white/80 hover:text-white flex items-center justify-center transition-all z-20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Modal Header -->
            <div class="mb-5 pr-8">
                <h3 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h14a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>Unggah Karya ke Failerry</span>
                </h3>
            </div>

            <!-- Form -->
            <form @submit.prevent="uploadFile()">
                
                <!-- Drag & Drop Upload Zone -->
                <div class="mb-5">
                    <div @dragover.prevent="isDragging = true" 
                         @dragleave.prevent="isDragging = false" 
                         @drop.prevent="handleDrop($event)"
                         @click="if(!filePreview) $refs.fileInput.click()"
                         :class="{ 'border-white bg-white/20 scale-[1.01] shadow-xl': isDragging, 'border-white/20 bg-white/5 hover:bg-white/10 hover:border-white/40': !isDragging }"
                         class="border border-dashed rounded-[20px] p-6 text-center cursor-pointer transition-all duration-300 backdrop-blur-md relative overflow-hidden group">

                        <input type="file" 
                               name="image" 
                               x-ref="fileInput" 
                               @change="handleFileSelect($event)" 
                               accept="image/*,video/*" 
                               class="hidden" />

                        <!-- File Selected Preview / Editor Studio -->
                        <template x-if="filePreview">
                            <div class="relative flex flex-col items-center" @click.stop>
                                
                                <!-- Canvas / Video Preview -->
                                <div class="w-full max-h-60 rounded-xl overflow-hidden border border-white/20 shadow-inner mb-4 relative flex items-center justify-center bg-black/30">
                                    <template x-if="isVideo">
                                        <video :src="filePreview" class="w-full h-full object-contain" autoplay muted loop playsinline></video>
                                    </template>
                                    <template x-if="!isVideo">
                                        <canvas x-ref="editorCanvas" class="max-w-full max-h-60 object-contain shadow-lg rounded-lg"></canvas>
                                    </template>
                                    
                                    <button type="button" 
                                            @click="clearFile()" 
                                            class="absolute top-2 right-2 px-3 py-1.5 rounded-full bg-red-600/80 hover:bg-red-700 text-white text-[11px] font-bold transition-all z-10 shadow-md">
                                        Ganti Media
                                    </button>
                                </div>

                                <!-- Studio Crop & Filter Controls (Images Only) -->
                                <template x-if="!isVideo">
                                    <div class="w-full space-y-4 pt-1 bg-white/5 p-4 rounded-2xl border border-white/10">
                                        <!-- Aspect Ratio Selector -->
                                        <div>
                                            <span class="text-[10px] font-black uppercase tracking-wider text-white/60 block mb-2 text-left">Aspek Rasio (Crop)</span>
                                            <div class="grid grid-cols-4 gap-2">
                                                <template x-for="ratio in ['Bebas', '1:1', '4:5', '16:9']" :key="ratio">
                                                    <button type="button" 
                                                            @click="selectedRatio = ratio; applyEdits()"
                                                            :class="selectedRatio === ratio ? 'bg-[#8B5E3C] border-[#C69C6D]' : 'bg-white/10 border-white/10 hover:bg-white/15'"
                                                            class="py-1 px-2.5 rounded-full text-xs font-semibold border transition-all active:scale-95"
                                                            x-text="ratio">
                                                    </button>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Filter Presets Selector -->
                                        <div>
                                            <span class="text-[10px] font-black uppercase tracking-wider text-white/60 block mb-2 text-left">Filter Artistik</span>
                                            <div class="grid grid-cols-5 gap-1.5">
                                                <template x-for="filter in ['Normal', 'Vintage', 'Lomo', 'Noir', 'Cool']" :key="filter">
                                                    <button type="button" 
                                                            @click="selectedFilter = filter; applyEdits()"
                                                            :class="selectedFilter === filter ? 'bg-[#8B5E3C] border-[#C69C6D]' : 'bg-white/10 border-white/10 hover:bg-white/15'"
                                                            class="py-1 rounded-full text-[10px] font-semibold border transition-all active:scale-95"
                                                            x-text="filter">
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                            </div>
                        </template>

                        <!-- No File Selected Placeholder -->
                        <template x-if="!filePreview">
                            <div class="flex flex-col items-center justify-center py-6">
                                <div class="w-14 h-14 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center mb-4 transition-transform group-hover:scale-110 shadow-md">
                                    <svg class="w-7 h-7 text-white/95" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-white mb-1">
                                    Tarik & lepaskan file di sini, atau <span class="underline text-amber-300">Cari File</span>
                                </p>
                                <p class="text-[10px] text-white/60">Mendukung Gambar (JPG, PNG, WEBP) / Video (MP4) maks. 20MB</p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Title Input -->
                <div class="mb-4">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-white/70 mb-1.5">Judul Karya</label>
                    <input type="text" 
                           name="title" 
                           x-model="title" 
                           placeholder="Berikan judul karya..." 
                           required 
                           :disabled="uploading"
                           class="w-full px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 focus:bg-white/20 backdrop-blur-xl border border-white/20 focus:border-white/40 text-white placeholder-white/60 outline-none transition-all text-sm font-medium" />
                </div>

                <!-- Description Input -->
                <div class="mb-5">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-white/70 mb-1.5">Kisah di Balik Karya (Opsional)</label>
                    <textarea name="description" 
                              x-model="description" 
                              rows="2" 
                              :disabled="uploading"
                              placeholder="Ceritakan latar belakang atau deskripsi foto..." 
                              class="w-full px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 focus:bg-white/20 backdrop-blur-xl border border-white/20 focus:border-white/40 text-white placeholder-white/60 outline-none transition-all text-sm font-medium resize-none"></textarea>
                </div>

                <!-- Progress Bar -->
                <div x-show="uploading" class="mb-5">
                    <div class="flex items-center justify-between text-xs font-bold text-white/95 mb-1.5">
                        <span>Mengirim ke server...</span>
                        <span x-text="progress + '%'"></span>
                    </div>
                    <div class="w-full h-2.5 bg-white/10 backdrop-blur-md border border-white/20 rounded-full p-0.5 overflow-hidden shadow-inner">
                        <div class="h-full bg-gradient-to-r from-[#C69C6D] to-[#8B5E3C] rounded-full transition-all duration-100"
                             :style="`width: ${progress}%`"></div>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/15">
                    <button type="button" 
                            @click="openModal = false; clearFile();" 
                            :disabled="uploading"
                            class="px-6 py-2.5 rounded-full font-semibold text-xs md:text-sm bg-white/5 hover:bg-white/10 border border-white/25 text-white transition-all active:scale-95 disabled:opacity-50">
                        Batal
                    </button>

                    <button type="submit" 
                            :disabled="!file || uploading"
                            class="px-7 py-2.5 rounded-full font-bold text-xs md:text-sm bg-[#8B5E3C] hover:bg-[#5C3A21] text-[#FFF8ED] border border-[#C69C6D]/45 shadow-lg transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <template x-if="uploading">
                            <svg class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span>Unggah Foto</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<style>
    @supports not ((-webkit-backdrop-filter: blur(1px)) or (backdrop-filter: blur(1px))) {
        .backdrop-blur-xl, .backdrop-blur-md {
            background-color: rgba(28, 22, 17, 0.95) !important;
            color: #ffffff !important;
        }
    }
</style>
