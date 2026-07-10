@extends('layouts.app')

@section('content')
<div class="w-full max-w-7xl mx-auto pt-8">
 
 <!-- Profile Banner -->
 <div class="relative w-full h-48 md:h-64 lg:h-80 overflow-hidden rounded-b-[32px] md:rounded-b-[48px] bg-soft-cream group">
 @if($user->cover_photo_url)
 <img src="{{ $user->cover_photo_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
 @else
 <div class="w-full h-full bg-gradient-to-br from-soft-cream to-sand flex items-center justify-center">
 <svg class="w-12 h-12 text-sand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h14a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
 </div>
 @endif

 </div>

 <!-- Profile Header -->
 <div class="flex flex-col items-center px-4 mb-10 -mt-16 md:-mt-20 relative z-10">
 <div class="relative w-32 h-32 md:w-40 md:h-40 mb-4 group ring-8 ring-cream rounded-full overflow-hidden bg-cream shadow-xl">
 <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
 @if(auth()->check() && auth()->id() === $user->id)
 <a href="{{ route('profile.edit') }}" class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
 <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
 </a>
 @endif
 </div>
 
 <h1 class="text-3xl md:text-4xl font-display font-bold text-cocoa mb-1 flex items-center gap-2">
 {{ $user->name }}
 @if($user->is_verified)
 <x-verified-badge />
 @endif
 </h1>
 <div class="text-caramel mb-4">{{ '@' . $user->username }}</div>
 
 @if($user->bio)
 <p class="text-cocoa max-w-md text-center mb-6">{{ $user->bio }}</p>
 @endif

 <div class="flex gap-4 items-center font-medium text-cocoa mb-6">
 <div class="flex items-center gap-1">
 <span class="font-bold">{{ $photosCount }}</span> Foto
 </div>
 <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
 <div class="flex items-center gap-1">
 <span class="font-bold" id="followers-count">{{ $followersCount }}</span> Pengikut
 </div>
 <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
 <div class="flex items-center gap-1">
 <span class="font-bold">{{ $followingCount }}</span> Mengikuti
 </div>
 </div>

 <div class="flex gap-2" x-data="{ 
 isFollowing: {{ auth()->check() && auth()->user()->isFollowing($user) ? 'true' : 'false' }},
 followersCount: {{ $followersCount }},
 isLoading: false,
 toggleFollow() {
 if (this.isLoading) return;
 @if(!auth()->check())
 window.location.href = '{{ route('login') }}';
 return;
 @endif
 
 this.isLoading = true;
 fetch('{{ route('user.follow', $user) }}', {
 method: 'POST',
 headers: {
 'X-CSRF-TOKEN': '{{ csrf_token() }}',
 'Accept': 'application/json'
 }
 })
 .then(res => res.json())
 .then(data => {
 this.isFollowing = data.following;
 this.followersCount = data.followers_count;
 document.getElementById('followers-count').innerText = data.followers_count;
 this.isLoading = false;
 });
 }
 }">
 @if(auth()->check() && auth()->id() === $user->id)
 <a href="{{ route('profile.edit') }}" class="btn-secondary">Edit Profil</a>
 @else
 <button 
 @click="toggleFollow()" 
 :class="isFollowing ? 'bg-soft-cream text-cocoa' : 'bg-brown text-cream'"
 class="px-8 py-2.5 rounded-full font-bold transition-all active:scale-95 disabled:opacity-50"
 :disabled="isLoading"
 >
 <span x-text="isFollowing ? 'Mengikuti' : 'Ikuti'"></span>
 </button>
 <a href="{{ route('messages.show', $user->username) }}" class="w-11 h-11 bg-soft-cream hover:bg-soft-cream rounded-full flex items-center justify-center transition-colors text-cocoa border border-sand shadow-sm">
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
 </a>
 @endif
 </div>
 </div>

 <!-- Tabs Navigation -->
 <div class="w-full flex justify-center gap-8 border-b border-sand mb-8 px-4">
 <a href="{{ route('profile.show', ['user' => $user->username, 'tab' => 'created']) }}" 
 class="pb-3 font-semibold text-lg transition-colors relative {{ $tab === 'created' || $tab === '' ? 'text-cocoa' : 'text-caramel hover:text-cocoa' }}">
 Dibuat
 @if($tab === 'created' || $tab === '')
 <div class="absolute bottom-0 left-0 w-full h-1 bg-cocoa rounded-t-md"></div>
 @endif
 </a>
 <a href="{{ route('profile.show', ['user' => $user->username, 'tab' => 'saved']) }}" 
 class="pb-3 font-semibold text-lg transition-colors relative {{ $tab === 'saved' ? 'text-cocoa' : 'text-caramel hover:text-cocoa' }}">
 Disimpan
 @if($tab === 'saved')
 <div class="absolute bottom-0 left-0 w-full h-1 bg-cocoa rounded-t-md"></div>
 @endif
 </a>
 <a href="{{ route('profile.show', ['user' => $user->username, 'tab' => 'boards']) }}" 
 class="pb-3 font-semibold text-lg transition-colors relative {{ $tab === 'boards' ? 'text-cocoa' : 'text-caramel hover:text-cocoa' }}">
 Board
 @if($tab === 'boards')
 <div class="absolute bottom-0 left-0 w-full h-1 bg-cocoa rounded-t-md"></div>
 @endif
 </a>
 </div>

 <!-- Tab Content -->
 <div class="px-2 sm:px-4 md:px-6 mb-16">
 
 @if($tab === 'boards')
 <!-- Boards Grid -->
 @if($boards->count() > 0)
 <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
 @foreach($boards as $board)
 <a href="{{ route('boards.show', $board) }}" class="group block">
 <div class="aspect-square bg-soft-cream rounded-2xl md:rounded-3xl overflow-hidden mb-3 p-1 flex flex-wrap gap-1 relative group-hover:shadow-md transition-all">
 @if($board->cover_image)
 <div class="w-full h-full"><img src="{{ $board->cover_image_url }}" class="w-full h-full object-cover rounded-[20px]"></div>
 @else
 @php $previews = $board->preview_photos; @endphp
 @if($previews->count() >= 3)
 <!-- Pinterest 3-image layout -->
 <div class="w-[66%] h-full shrink-0"><img src="{{ $previews[0]->thumbnail_url }}" class="w-full h-full object-cover rounded-l-xl md:rounded-l-[20px]"></div>
 <div class="flex-1 flex flex-col gap-1 h-full">
 <div class="h-1/2 w-full"><img src="{{ $previews[1]->thumbnail_url }}" class="w-full h-full object-cover rounded-tr-xl md:rounded-tr-[20px]"></div>
 <div class="h-1/2 w-full"><img src="{{ $previews[2]->thumbnail_url }}" class="w-full h-full object-cover rounded-br-xl md:rounded-br-[20px]"></div>
 </div>
 @elseif($previews->count() > 0)
 <div class="w-full h-full"><img src="{{ $previews[0]->thumbnail_url }}" class="w-full h-full object-cover rounded-[20px]"></div>
 @else
 <!-- Empty Board -->
 <div class="w-full h-full bg-soft-cream flex items-center justify-center rounded-[20px]"></div>
 @endif
 @endif
 <div class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity rounded-2xl md:rounded-3xl"></div>
 </div>
 <h3 class="font-bold text-cocoa text-lg truncate px-1">{{ $board->title }}</h3>
 <div class="flex items-center gap-2 text-sm text-caramel px-1">
 <span>{{ $board->photos_count }} Pin</span>
 @if($board->is_private)
 <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C9.243 2 7 4.243 7 7v3H6c-1.103 0-2 .897-2 2v8c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2v-8c0-1.103-.897-2-2-2h-1V7c0-2.757-2.243-5-5-5zm-3 5c0-1.654 1.346-3 3-3s3 1.346 3 3v3H9V7zm9 13H6v-8h12v8z"></path></svg>
 @endif
 </div>
 </a>
 @endforeach
 </div>
 @else
 <div class="text-center py-20 text-caramel">
 Belum ada board yang dibuat.
 </div>
 @endif
 @else
 <!-- Photos Grid (Masonry) -->
 @if($photos->count() > 0)
 <div class="w-full mx-auto" x-data="{ msnry: null }" x-init="$nextTick(() => {
 const grid = $el.querySelector('.profile-grid');
 msnry = new window.Masonry(grid, { itemSelector: '.grid-item', columnWidth: '.w-1\\/2', percentPosition: true });
 msnry.layout();
 })">
 <div class="profile-grid w-full -ml-2 sm:-ml-4 text-left">
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
 <div class="text-center py-20 text-caramel">
 Belum ada pin untuk ditampilkan.
 </div>
 @endif
 @endif
 
 </div>
</div>
@endsection
