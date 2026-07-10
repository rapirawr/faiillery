@extends('layouts.app')

@section('content')
<div class="w-full max-w-4xl mx-auto pt-8 px-4">
 <div class="flex items-center justify-between mb-8">
 <h1 class="text-3xl font-display font-bold text-cocoa">Edit Board</h1>
 <button onclick="history.back()" class="text-sm font-bold text-caramel hover:text-cocoa transition-colors">Batal</button>
 </div>

 <div class="bg-soft-cream rounded-3xl shadow-minimal border border-sand p-6 md:p-10 transition-colors" x-data="{ saving: false }">
 <form action="{{ route('boards.update', $board) }}" method="POST" @submit="saving = true">
 @csrf
 @method('PUT')

 <div class="mb-6">
 <label class="block text-sm font-semibold text-cocoa mb-2">Nama Board</label>
 <input type="text" name="title" placeholder="Contoh: Tempat Belanja, Tempat Keren" required
 value="{{ old('title', $board->title) }}"
 class="w-full rounded-2xl border-sand focus:border-sand focus:ring-sand/20 py-3 px-4 transition-colors bg-cream text-cocoa">
 @error('title')
 <p class="text-red-500 text-sm mt-1 font-medium">{{ $message }}</p>
 @enderror
 </div>

 <div class="mb-6">
 <label class="block text-sm font-semibold text-cocoa mb-2">Deskripsi <span class="text-caramel font-normal">(opsional)</span></label>
 <textarea name="description" rows="4" placeholder="Apa tujuan dari board ini?"
 class="w-full rounded-2xl border-sand focus:border-sand focus:ring-sand/20 py-3 px-4 transition-colors bg-cream text-cocoa resize-none">{{ old('description', $board->description) }}</textarea>
 </div>

 <div class="mb-8 flex items-center justify-between py-6 border-t border-sand">
 <div>
 <h3 class="font-semibold text-cocoa">Rahasiakan board</h3>
 <p class="text-sm text-caramel">Hanya Anda yang bisa melihat board ini.</p>
 </div>
 <label class="relative inline-flex items-center cursor-pointer">
 <input type="checkbox" name="is_private" value="1" class="sr-only peer" {{ $board->is_private ? 'checked' : '' }}>
 <div class="w-11 h-6 bg-caramel peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-soft-cream after:border-sand after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brown"></div>
 </label>
 </div>

 <div class="flex items-center justify-between pt-6 border-t border-sand">
 <form action="{{ route('boards.destroy', $board) }}" method="POST" @submit.prevent="window.appConfirm('Hapus Board', 'Apakah Anda yakin ingin menghapus board ini? Semua pin di dalamnya akan tetap ada di profil Anda.', () => $el.submit(), 'Hapus')">
 @csrf
 @method('DELETE')
 <button type="submit" class="text-sm font-bold text-red-600 hover:underline">Hapus Board</button>
 </form>

 <div class="flex gap-3">
 <button type="submit" class="btn-primary px-8 shadow-minimal" :class="{ 'opacity-50 pointer-events-none': saving }">
 <span x-show="!saving">Simpan</span>
 <span x-show="saving" style="display: none;">Menyimpan...</span>
 </button>
 </div>
 </div>
 </form>
 </div>
</div>
@endsection
