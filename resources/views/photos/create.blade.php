@extends('layouts.app')

@section('title', 'Upload'. ' - Failerry')
@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 md:py-12">
 <div class="bg-soft-cream rounded-2xl shadow-minimal border border-sand overflow-hidden transition-colors">
 
 <div class="p-6 border-b border-sand flex items-center justify-between">
 <h1 class="text-2xl md:text-3xl font-display font-bold text-cocoa">Unggah Media</h1>
 <button onclick="history.back()" class="w-10 h-10 bg-cream hover:bg-soft-cream rounded-lg flex items-center justify-center transition-colors text-cocoa">
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
 </button>
 </div>

 <form action="{{ route('photos.store') }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-10 flex flex-col md:flex-row gap-10" x-data="uploadForm()">
 @csrf
 
 <!-- Left: Media Upload Area -->
 <div class="w-full md:w-1/2 shrink-0">
 <div class="space-y-4">
 <label for="imageUpload" 
 class="w-full bg-cream rounded-xl border-2 border-dashed border-sand flex flex-col items-center justify-center cursor-pointer hover:bg-soft-cream hover:border-gray-400 transition-all relative overflow-hidden group min-h-[300px]"
 :class="{'border-sand': dragover, 'bg-soft-cream': dragover}"
 @dragover.prevent="dragover = true"
 @dragenter.prevent="dragover = true"
 @dragleave.prevent="dragover = false"
 @drop.prevent="handleDrop($event)">
 
 <!-- Single Media Preview -->
 <div x-show="previews.length === 1" class="absolute inset-0 w-full h-full bg-black/5" style="display: none;">
 <template x-if="previews.length === 1 && !previews[0].isVideo">
 <img :src="previews[0].url" class="w-full h-full object-cover">
 </template>
 <template x-if="previews.length === 1 && previews[0].isVideo">
 <video :src="previews[0].url" class="w-full h-full object-cover" autoplay muted loop playsinline></video>
 </template>
 <div class="absolute inset-0 bg-cocoa/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-4 z-10">
 <button type="button" @click.stop.prevent="removeImage(0)" class="px-4 py-2 bg-red-600 text-white text-xs font-bold rounded-full hover:bg-red-700 transition-colors shadow-lg">
 Ganti Media
 </button>
 </div>
 </div>

 <!-- Multi Media Grid Preview (Simple Summary) -->
 <div x-show="previews.length > 1" class="absolute inset-0 flex flex-col items-center justify-center bg-soft-cream/50" style="display: none;">
 <div class="flex -space-x-4 mb-4">
 <template x-for="(p, i) in previews.slice(0, 3)">
 <div class="w-16 h-16 rounded-lg border-2 border-white overflow-hidden shadow-lg transform" :style="`z-index: ${10-i};`" :class="i === 1 ? 'rotate-3' : (i === 2 ? '-rotate-6' : '-rotate-3')">
 <template x-if="!p.isVideo">
 <img :src="p.url" class="w-full h-full object-cover">
 </template>
 <template x-if="p.isVideo">
 <div class="w-full h-full bg-cocoa flex items-center justify-center text-white">
 <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
 </div>
 </template>
 </div>
 </template>
 </div>
 <p class="text-sm font-bold text-cocoa" x-text="`${previews.length} file dipilih`"></p>
 <button type="button" @click.stop.prevent="triggerFileInput" class="mt-4 text-xs font-semibold text-caramel hover:text-cocoa transition-colors">
 Tambah atau Ganti
 </button>
 </div>

 <!-- Placeholder -->
 <div x-show="previews.length === 0" class="flex flex-col items-center justify-center text-center px-6 pointer-events-none">
 <div class="w-12 h-12 bg-brown text-cream rounded-lg flex items-center justify-center mb-6 shadow-minimal group-hover:-translate-y-1 transition-transform">
 <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
 </div>
 <h3 class="text-lg font-semibold text-cocoa mb-2">Pilih file atau seret ke sini</h3>
 <p class="text-sm text-caramel mb-4 max-w-[250px]">Dukung banyak foto atau video sekaligus</p>
 </div>
 
 <input type="file" id="imageUpload" name="image[]" class="hidden" accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime" multiple @change="handleFileSelect($event)">
 <input type="file" id="thumbnailUpload" name="thumbnail[]" class="hidden" multiple>
 </label>

 <!-- File Size Indicator -->
 <div x-show="previews.length > 0" class="mt-4 p-4 rounded-xl bg-cream border border-sand shadow-sm">
 <div class="flex justify-between items-center mb-2">
 <span class="text-[10px] font-black uppercase tracking-widest text-caramel">Total Upload Size</span>
 <span class="text-xs font-black" :class="totalSize > limit ? 'text-red-500 animate-pulse' : 'text-cocoa'" x-text="formatSize(totalSize)"></span>
 </div>
 <div class="w-full h-1.5 bg-soft-cream rounded-full overflow-hidden">
 <div class="h-full transition-all duration-500" 
 :class="totalSize > limit ? 'bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.5)]' : 'bg-cocoa'"
 :style="`width: ${Math.min((totalSize / limit) * 100, 100)}%`"
 ></div>
 </div>
 <p x-show="totalSize > limit" class="mt-2 text-[9px] font-bold text-red-500 uppercase tracking-tight flex items-center gap-1">
 <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
 Ukuran file melebihi batas maksimal ({{ \App\Models\Setting::get('max_upload_size_mb', 10) }}MB). Harap unggah file yang lebih kecil.
 </p>
 </div>

 <!-- Detailed Grid for Multiple Files -->
 <div x-show="previews.length > 1" class="grid grid-cols-4 gap-2" style="display: none;">
 <template x-for="(p, i) in previews">
 <div class="relative aspect-square group rounded-lg overflow-hidden border border-sand">
 <template x-if="!p.isVideo">
 <img :src="p.url" class="w-full h-full object-cover">
 </template>
 <template x-if="p.isVideo">
 <div class="w-full h-full bg-cocoa flex items-center justify-center text-white relative">
 <video :src="p.url" class="w-full h-full object-cover" autoplay muted loop playsinline></video>
 <div class="absolute inset-0 bg-black/30 flex items-center justify-center pointer-events-none">
 <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
 </div>
 </div>
 </template>
 <button type="button" @click="removeImage(i)" class="absolute top-1 right-1 w-6 h-6 bg-red-600 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity z-10">
 <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
 </button>
 </div>
 </template>
 </div>
 </div>

 @error('image')
 <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p>
 @enderror
 @error('image.*')
 <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p>
 @enderror
 </div>

 <!-- Right: Details Form -->
 <div class="w-full md:w-1/2 flex flex-col gap-8">
 <!-- Board Selection & Submit -->
 <div class="flex justify-between items-center bg-cream border border-sand p-2 rounded-xl relative transition-colors" x-data="{ selectedBoardName: 'Simpan ke Profil' }">
 <select name="board_id" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" @change="selectedBoardName = $event.target.options[$event.target.selectedIndex].text">
 <option value="">Simpan ke Profil</option>
 @foreach($boards as $board)
 <option value="{{ $board->id }}">{{ $board->title }}</option>
 @endforeach
 </select>
 <div class="flex-1 px-4 text-sm font-semibold text-cocoa truncate" x-text="selectedBoardName"></div>
 <button type="submit" class="btn-primary ml-2 z-10 shrink-0 shadow-minimal" 
 :disabled="previews.length === 0 || (!isAdmin && totalSize > limit)" 
 :class="{'opacity-50 cursor-not-allowed grayscale': previews.length === 0 || (!isAdmin && totalSize > limit)}">
 <span x-text="previews.length > 1 ? 'Publish Semua' : 'Publish'">Publish</span>
 </button>
 </div>

 <!-- Title -->
 <div>
 <input type="text" name="title" placeholder="Beri judul karya Anda" value="{{ old('title') }}"
 class="w-full border-none border-b-2 border-sand focus:border-sand focus:ring-0 px-0 py-3 text-3xl md:text-4xl font-display font-bold placeholder:text-caramel bg-transparent text-cocoa transition-colors">
 <p class="text-xs text-caramel mt-2" x-show="previews.length > 1">Jika kosong, nama file akan digunakan sebagai judul masing-masing foto.</p>
 @error('title')
 <p class="text-red-500 text-sm mt-1 font-medium">{{ $message }}</p>
 @enderror
 </div>

 <!-- User Profile Display -->
 <div class="flex items-center gap-4">
 <img src="{{ auth()->user()->avatar_url }}" alt="Profile" class="w-10 h-10 rounded-full object-cover ring-1 ring-sand ">
 <div>
 <div class="font-bold text-cocoa">{{ auth()->user()->name }}</div>
 <div class="text-xs text-caramel">{{ '@' . auth()->user()->username }}</div>
 </div>
 </div>

 <!-- Description -->
 <div>
 <label class="block text-xs font-semibold text-caramel uppercase tracking-wider mb-2">Deskripsi Visual</label>
 <textarea name="description" rows="3" placeholder="Ceritakan detail tentang karya visual ini..."
 class="w-full border border-sand rounded-lg focus:border-sand focus:ring-0 p-4 text-cocoa text-sm resize-none placeholder:text-caramel bg-cream transition-colors">{{ old('description') }}</textarea>
 </div>

 <!-- Tags -->
 <div>
 <label class="block text-xs font-semibold text-caramel uppercase tracking-wider mb-2">Kategori / Tag</label>
 <input type="text" name="tags" placeholder="Pisahkan dengan koma (contoh: kanvas, aesthetic)" value="{{ old('tags') }}"
 class="w-full border border-sand rounded-lg focus:border-sand focus:ring-0 p-4 text-cocoa text-sm placeholder:text-caramel bg-cream transition-colors">
 </div>
 </div>
 </form>
 </div>
</div>
@endsection

@push('scripts')
<script>
function uploadForm() {
 return {
 dragover: false,
 previews: [], // Array of {file, url, isVideo}
 totalSize: 0,
 limit: {{ \App\Models\Setting::get('max_upload_size_mb', 10) }} * 1024 * 1024,
 isAdmin: {{ auth()->check() && auth()->user()->is_admin ? 'true' : 'false' }},
 
 triggerFileInput() {
 document.getElementById('imageUpload').click();
 },

 handleDrop(event) {
 this.dragover = false;
 const files = event.dataTransfer.files;
 if (files.length > 0) {
 this.addFiles(files);
 }
 },
 
 handleFileSelect(event) {
 const files = event.target.files;
 if (files.length > 0) {
 this.addFiles(files);
 }
 },

 addFiles(files) {
 Array.from(files).forEach(file => {
 if (file && (file.type.startsWith('image/') || file.type.startsWith('video/'))) {
 const isVid = file.type.startsWith('video/');
 const reader = new FileReader();
 reader.onload = (e) => {
 const previewItem = {
 file: file,
 url: e.target.result,
 isVideo: isVid,
 thumbnailBlob: null
 };
 this.previews.push(previewItem);
 this.calculateTotalSize();
 this.syncInput();

 if (isVid) {
 this.extractVideoThumbnail(file, (blob) => {
 previewItem.thumbnailBlob = blob;
 this.syncInput();
 });
 }
 };
 reader.readAsDataURL(file);
 }
 });
 },

  extractVideoThumbnail(file, callback) {
  const video = document.createElement('video');
  video.preload = 'metadata';
  video.src = URL.createObjectURL(file);
  video.muted = true;
  video.playsInline = true;
  video.onloadedmetadata = () => {
  const seekTime = Math.min(1, video.duration / 2);
  video.currentTime = seekTime;
  };
  video.onseeked = () => {
  try {
  const canvas = document.createElement('canvas');
  canvas.width = video.videoWidth || 640;
  canvas.height = video.videoHeight || 480;
  const ctx = canvas.getContext('2d');
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
  canvas.toBlob((blob) => {
  callback(blob);
  URL.revokeObjectURL(video.src);
  }, 'image/jpeg', 0.85);
  } catch (err) {
  console.error("Canvas export failed:", err);
  URL.revokeObjectURL(video.src);
  }
  };
  video.onerror = (e) => {
  console.error("Video load error:", e);
  URL.revokeObjectURL(video.src);
  };
  video.load();
  },
 
 calculateTotalSize() {
 this.totalSize = this.previews.reduce((acc, p) => acc + p.file.size, 0);
 },

 formatSize(bytes) {
 if (bytes === 0) return '0 Bytes';
 const k = 1024;
 const sizes = ['Bytes', 'KB', 'MB', 'GB'];
 const i = Math.floor(Math.log(bytes) / Math.log(k));
 return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
 },
 
 removeImage(index) {
 this.previews.splice(index, 1);
 this.calculateTotalSize();
 this.syncInput();
 },

 syncInput() {
 const input = document.getElementById('imageUpload');
 const thumbInput = document.getElementById('thumbnailUpload');
 const dataTransfer = new DataTransfer();
 const thumbDataTransfer = new DataTransfer();

 this.previews.forEach(p => {
 dataTransfer.items.add(p.file);
 if (p.isVideo && p.thumbnailBlob) {
 const thumbFile = new File(
 [p.thumbnailBlob], 
 p.file.name.replace(/\.[^/.]+$/, "") + "_thumb.jpg", 
 { type: "image/jpeg" }
 );
 thumbDataTransfer.items.add(thumbFile);
 }
 });

 input.files = dataTransfer.files;
 if (thumbInput) {
 thumbInput.files = thumbDataTransfer.files;
 }
 }
 }
}
</script>
@endpush
