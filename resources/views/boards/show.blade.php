@extends('layouts.app')

@section('content')
@php
    $boardPhotos = $photos->map(function($p) {
        return [
            'uid' => $p->uid,
            'title' => addslashes($p->title),
            'description' => addslashes($p->description),
            'image_url' => $p->image_url,
            'uploader_name' => addslashes($p->user->name ?? 'Anonymous'),
            'uploader_username' => $p->user->username ?? 'anonymous',
            'uploader_avatar' => $p->user->avatar_url ?? 'https://i.pravatar.cc/150',
            'likes_count' => $p->likes_count,
            'comments_count' => $p->comments()->count(),
            'is_liked' => auth()->check() && auth()->user()->hasLiked($p),
            'detail_url' => route('photos.show', $p->uid),
            'is_video' => $p->isVideo(),
        ];
    })->values()->toJson();
@endphp

<div class="w-full max-w-7xl mx-auto pt-8 px-4 mb-16" x-data="{ boardPhotos: {!! $boardPhotos !!} }">
 
 <!-- Board Header -->
 <div class="flex flex-col items-center mb-10">
 <h1 class="text-3xl md:text-5xl font-display font-bold text-cocoa mb-4 text-center">{{ $board->title }}</h1>
 
 <!-- Board Info -->
 <div class="flex items-center gap-3 mb-6">
 @if($board->is_private)
 <div class="flex items-center gap-1 text-caramel bg-soft-cream px-3 py-1 rounded-full text-sm font-semibold">
 <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C9.243 2 7 4.243 7 7v3H6c-1.103 0-2 .897-2 2v8c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2v-8c0-1.103-.897-2-2-2h-1V7c0-2.757-2.243-5-5-5zm-3 5c0-1.654 1.346-3 3-3s3 1.346 3 3v3H9V7zm9 13H6v-8h12v8z"></path></svg>
 <span>Rahasia</span>
 </div>
 @endif
 <div class="font-bold text-cocoa">{{ $photos->total() }} Pin</div>
 <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
 <a href="{{ route('profile.show', $board->user) }}" class="flex items-center gap-2 hover:bg-soft-cream p-1 pr-3 rounded-full transition-colors">
 <img src="{{ $board->user->avatar_url }}" alt="Profile" class="w-6 h-6 rounded-full object-cover">
 <span class="font-semibold text-sm">{{ $board->user->name }}</span>
 </a>
 </div>

 <!-- Putar Slideshow Button -->
 @if($photos->count() > 0)
 <div class="flex justify-center mb-6">
     <button @click="$dispatch('open-theater', { photos: boardPhotos, startIndex: 0 })"
             class="flex items-center gap-2 px-5 py-2.5 bg-[#8B5E3C] hover:bg-[#5C3A21] text-[#FFF8ED] rounded-full text-sm font-bold shadow-md transition-all active:scale-95 border border-[#C69C6D]/30">
         <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
             <path d="M8 5v14l11-7z"/>
         </svg>
         <span>Putar Slideshow</span>
     </button>
 </div>
 @endif

 @if($board->description)
 <p class="text-cocoa max-w-lg text-center mb-6">{{ $board->description }}</p>
 @endif

 @if(auth()->check() && auth()->id() === $board->user_id)
 <div class="flex gap-2">
 <a href="{{ route('boards.edit', $board) }}" class="w-12 h-12 bg-soft-cream hover:bg-sand rounded-full flex items-center justify-center transition-colors">
 <svg class="w-5 h-5 text-cocoa" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
 </a>
 <button class="w-12 h-12 bg-soft-cream hover:bg-sand rounded-full flex items-center justify-center transition-colors">
 <svg class="w-5 h-5 text-cocoa" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
 </button>
 </div>
 @endif
 </div>

 <!-- Photos Grid (Masonry) -->
 @if($photos->count() > 0)
 <div class="w-full mx-auto" x-data="{ msnry: null }" x-init="$nextTick(() => {
 const grid = $el.querySelector('.board-grid');
 msnry = new window.Masonry(grid, { itemSelector: '.grid-item', columnWidth: '.w-1\\/2', percentPosition: true });
 msnry.layout();
 })">
 <div class="board-grid w-full -ml-2 sm:-ml-4 text-left">
 <div class="w-1/2 sm:w-1/3 md:w-1/4 lg:w-1/5 xl:w-[16.666%] h-0"></div>
 @foreach($photos as $photo)
 <div class="grid-item w-1/2 sm:w-1/3 md:w-1/4 lg:w-1/5 xl:w-[16.666%] pl-2 sm:pl-4 mb-2 sm:mb-4">
 @include('components.photo-card', ['photo' => $photo])
 </div>
 @endforeach
 </div>
 </div>
 
 <div class="mt-8">
 {{ $photos->links() }}
 </div>
 @else
 <div class="text-center py-20 text-caramel flex flex-col items-center">
 <div class="w-16 h-16 bg-soft-cream rounded-full flex items-center justify-center mb-4">
 <svg class="w-8 h-8 text-caramel" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
 </div>
 <h3 class="text-xl font-bold text-cocoa mb-2">Belum ada Pin untuk board ini</h3>
 <p>Simpan Pin yang Anda sukai ke board ini.</p>
 <a href="{{ route('home') }}" class="btn-primary mt-6">Cari Ide</a>
 </div>
 @endif
</div>
@endsection

