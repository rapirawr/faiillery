@extends('layouts.app')

@section('content')
<div class="w-full max-w-4xl mx-auto pt-8 px-4">
 <h1 class="text-3xl font-display font-bold text-cocoa mb-8">Buat Board</h1>

 <div class="bg-soft-cream rounded-3xl border border-sand p-6 md:p-10" x-data="{ saving: false }">
 <form action="{{ route('boards.store') }}" method="POST" @submit="saving = true">
 @csrf

 <div class="mb-6">
 <label class="block text-sm font-semibold text-cocoa mb-2">Nama</label>
 <input type="text" name="title" placeholder="Contoh: Tempat Belanja, Tempat Keren" required
 class="w-full rounded-2xl border-sand focus:border-sand focus:ring-sand/20 py-3 px-4 transition-colors">
 @error('title')
 <p class="text-red-500 text-sm mt-1 font-medium">{{ $message }}</p>
 @enderror
 </div>

 <div class="mb-6">
 <label class="block text-sm font-semibold text-cocoa mb-2">Deskripsi <span class="text-caramel font-normal">(opsional)</span></label>
 <textarea name="description" rows="3" placeholder="Apa tujuan dari board ini?"
 class="w-full rounded-2xl border-sand focus:border-sand focus:ring-sand/20 py-3 px-4 transition-colors resize-none"></textarea>
 </div>

 <div class="mb-8 flex items-center justify-between py-4 border-t border-sand">
 <div>
 <h3 class="font-semibold text-cocoa">Rahasiakan board</h3>
 <p class="text-sm text-caramel">Hanya Anda yang bisa melihat board ini.</p>
 </div>
 <label class="relative inline-flex items-center cursor-pointer">
 <input type="checkbox" name="is_private" value="1" class="sr-only peer">
 <div class="w-11 h-6 bg-caramel peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-soft-cream after:border-sand after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brown"></div>
 </label>
 </div>

 <div class="flex justify-end gap-3">
 <a href="{{ route('profile.show', auth()->user()->username ?? 'u') }}" class="btn-secondary">Batal</a>
 <button type="submit" class="btn-primary" :class="{ 'opacity-50 pointer-events-none': saving }">
 <span x-show="!saving">Buat</span>
 <span x-show="saving" style="display: none;">Membuat...</span>
 </button>
 </div>
 </form>
 </div>
</div>
@endsection
