@extends('layouts.admin')

@section('page-title', 'Photos')
@section('page-subtitle', 'Moderasi konten yang diunggah')

@section('content')

<!-- Fitur 4: Bulk Delete — Alpine wrapper + Form -->
<div x-data="bulkPhotos()" x-init="init()">

    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 mb-5">
        <!-- Search -->
        <form method="GET" action="{{ route('admin.photos') }}" class="flex flex-1 gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:#C69C6D;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari judul foto…"
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl text-sm outline-none border transition-all"
                       style="background:#FEFAF4;border-color:#E3C79A;color:#3B2417;"
                       onfocus="this.style.borderColor='#8B5E3C'" onblur="this.style.borderColor='#E3C79A'">
            </div>
            <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-white"
                    style="background:#8B5E3C;" onmouseover="this.style.background='#5C3A21'" onmouseout="this.style.background='#8B5E3C'">
                Search
            </button>
        </form>

        <!-- Bulk Select Toggle -->
        <button @click="toggleSelectMode()"
                class="px-4 py-2.5 rounded-xl text-sm font-semibold transition-all"
                :style="selectMode ? 'background:#8B5E3C;color:#FFF8ED;' : 'background:#F5E6CE;color:#8B5E3C;'"
                onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
            <span x-text="selectMode ? 'Keluar Mode Pilih' : '✓ Pilih Banyak'"></span>
        </button>
    </div>

    <!-- Photo Grid -->
    <form id="bulk-delete-form" action="{{ route('admin.photos.bulk-delete') }}" method="POST"
          @submit.prevent="confirmBulk()">
        @csrf
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            @foreach($photos as $photo)
            <div class="admin-card rounded-2xl overflow-hidden group transition-all duration-200 hover:shadow-md relative"
                 style="padding:0;"
                 :class="selectMode && selected.includes({{ $photo->id }}) ? 'ring-2 ring-offset-1' : ''"
                 :style="selectMode && selected.includes({{ $photo->id }}) ? 'ring-color:#8B5E3C;outline:2px solid #8B5E3C;' : ''">

                <!-- Select Checkbox (visible in select mode) -->
                <div x-show="selectMode" class="absolute top-2 left-2 z-10"
                     @click.stop="togglePhoto({{ $photo->id }})">
                    <div class="w-5 h-5 rounded flex items-center justify-center cursor-pointer border-2 transition-all"
                         :style="selected.includes({{ $photo->id }}) ? 'background:#8B5E3C;border-color:#8B5E3C;' : 'background:rgba(255,255,255,0.9);border-color:#C69C6D;'">
                        <svg x-show="selected.includes({{ $photo->id }})" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <input type="checkbox" name="photo_ids[]" value="{{ $photo->id }}" class="hidden"
                           :checked="selected.includes({{ $photo->id }})">
                </div>

                <!-- Thumbnail -->
                <div class="relative" style="aspect-ratio:3/4;" @click="selectMode && togglePhoto({{ $photo->id }})">
                    <img src="{{ $photo->thumbnail_url }}" class="w-full h-full object-cover" :class="selectMode ? 'cursor-pointer' : ''">

                    <!-- Hover overlay (only when NOT in select mode) -->
                    <div x-show="!selectMode" class="absolute inset-0 flex flex-col items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-all duration-200"
                         style="background:rgba(26,26,46,0.55);backdrop-filter:blur(2px);">
                        <a href="{{ route('photos.show', $photo->uid) }}" target="_blank"
                           class="flex items-center gap-1.5 text-white text-xs font-semibold px-3 py-2 rounded-lg w-4/5 justify-center transition-all"
                           style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);"
                           onmouseover="this.style.background='rgba(255,255,255,0.25)'"
                           onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            View
                        </a>
                        <form id="delete-photo-{{ $photo->id }}" action="{{ route('admin.photos.delete', $photo) }}" method="POST" class="w-4/5"
                            @submit.prevent="window.appConfirm('Hapus Foto', 'Hapus &quot;{{ addslashes($photo->title) }}&quot; secara permanen?', () => $el.submit(), 'Hapus')">
                            @csrf
                            @method('DELETE')
                            <button class="w-full flex items-center gap-1.5 justify-center text-white text-xs font-semibold px-3 py-2 rounded-lg transition-all"
                                style="background:rgba(233,44,30,0.7);border:1px solid rgba(233,44,30,0.5);"
                                onmouseover="this.style.background='rgba(233,44,30,0.9)'"
                                onmouseout="this.style.background='rgba(233,44,30,0.7)'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Info -->
                <div class="px-3 py-2.5" style="border-top:1px solid #F5E6CE;">
                    <div class="font-semibold text-xs truncate" style="color:#3B2417;">{{ $photo->title ?: 'Untitled' }}</div>
                    <div class="text-xs truncate mt-0.5" style="color:#C69C6D;">{{ $photo->user->name }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </form>

    <!-- Floating Bulk Action Bar -->
    <div x-show="selectMode && selected.length > 0"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl"
         style="background:#3B2417;color:#FFF8ED;min-width:280px;">
        <div class="flex-1">
            <span class="text-sm font-bold" x-text="selected.length + ' foto dipilih'"></span>
        </div>
        <button @click="selected = []" class="text-xs px-3 py-1.5 rounded-lg transition-all" style="background:rgba(255,248,237,0.15);" onmouseover="this.style.background='rgba(255,248,237,0.25)'" onmouseout="this.style.background='rgba(255,248,237,0.15)'">
            Batal
        </button>
        <button @click="confirmBulk()" class="text-xs font-bold px-3 py-1.5 rounded-lg transition-all" style="background:#e11d48;" onmouseover="this.style.background='#be123c'" onmouseout="this.style.background='#e11d48'">
            🗑 Hapus Terpilih
        </button>
    </div>

</div>

<div class="mt-8">
    {{ $photos->links() }}
</div>

<script>
function bulkPhotos() {
    return {
        selectMode: false,
        selected: [],

        init() {},

        toggleSelectMode() {
            this.selectMode = !this.selectMode;
            if (!this.selectMode) this.selected = [];
        },

        togglePhoto(id) {
            const idx = this.selected.indexOf(id);
            if (idx === -1) {
                this.selected.push(id);
            } else {
                this.selected.splice(idx, 1);
            }
        },

        confirmBulk() {
            if (this.selected.length === 0) return;
            window.appConfirm(
                'Bulk Delete',
                `Hapus ${this.selected.length} foto secara permanen? Aksi ini tidak bisa dibatalkan.`,
                () => {
                    // Sync checkboxes and submit
                    const form = document.getElementById('bulk-delete-form');
                    // Remove old hidden inputs
                    form.querySelectorAll('input[data-bulk]').forEach(el => el.remove());
                    this.selected.forEach(id => {
                        const input = document.createElement('input');
                        input.type  = 'hidden';
                        input.name  = 'photo_ids[]';
                        input.value = id;
                        input.setAttribute('data-bulk', '1');
                        form.appendChild(input);
                    });
                    form.submit();
                },
                'Hapus Semua'
            );
        }
    };
}
</script>
@endsection
