@extends('layouts.app')

@section('content')
<div class="w-full max-w-3xl mx-auto pt-12 px-4">
 <div class="flex items-center justify-between mb-10">
 <div>
 <h1 class="text-4xl font-display font-black tracking-tight text-cocoa">Notifikasi</h1>
 <!-- <p class="text-caramel mt-2">Tetap terhubung dengan aktivitas di Failerry.</p> -->
 </div>
 
 @if($notifications->count() > 0)
 <button onclick="markAllRead()" class="text-sm font-bold text-accent hover:text-accent-soft transition-colors">
 Tandai semua dibaca
 </button>
 @endif
 </div>

 <div class="bg-soft-cream rounded-3xl shadow-minimal border border-sand overflow-hidden">
 @if($notifications->count() > 0)
 <div class="divide-y divide-sand">
 @foreach($notifications as $notification)
 <div class="p-6 flex items-start gap-4 hover:bg-cream transition-colors {{ !$notification->read_at ? 'bg-accent/5' : '' }}">
 <!-- Actor Avatar -->
 <a href="{{ route('profile.show', $notification->actor->username) }}" class="shrink-0">
 <img src="{{ $notification->actor->avatar_url }}" alt="{{ $notification->actor->name }}" class="w-12 h-12 rounded-full object-cover shadow-sm">
 </a>

 <!-- Content -->
 <div class="flex-1">
 <div class="text-cocoa">
 <a href="{{ route('profile.show', $notification->actor->username) }}" class="font-bold hover:underline">
 {{ $notification->actor->name }}
 </a>
 <span class="text-espresso ">
 {{ $notification->data['message'] ?? 'melakukan aktivitas.' }}
 </span>
 </div>
 
 @if(in_array($notification->type, ['like', 'pin', 'comment', 'mention']) && isset($notification->data['photo_id']))
 <a href="{{ route('photos.show', $notification->data['photo_id']) }}" class="inline-block mt-2 text-xs font-bold text-accent hover:underline">
 Lihat foto →
 </a>
 @endif

 <div class="flex items-center gap-2 mt-1">
 <span class="text-xs text-caramel">
 {{ $notification->created_at->diffForHumans() }}
 </span>
 @if(!$notification->read_at)
 <div class="w-1.5 h-1.5 bg-accent rounded-full"></div>
 @endif
 </div>
 </div>

 <!-- Action/Type Icon -->
 <div class="shrink-0 pt-1">
 @if($notification->type === 'follow')
 <svg class="w-5 h-5 text-accent" fill="currentColor" viewBox="0 0 24 24">
 <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
 </svg>
 @elseif($notification->type === 'like')
 <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24">
 <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
 </svg>
 @elseif($notification->type === 'pin')
 <svg class="w-5 h-5 text-accent" fill="currentColor" viewBox="0 0 24 24">
 <path d="M16 5l-8 0c-1.1 0-2 .9-2 2v14l6-3 6 3v-14c0-1.1-.9-2-2-2z"/>
 </svg>
 @elseif(in_array($notification->type, ['comment', 'mention']))
 <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
 <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
 </svg>
 @else
 <svg class="w-5 h-5 text-sand" fill="currentColor" viewBox="0 0 24 24">
 <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
 </svg>
 @endif
 </div>
 </div>
 @endforeach
 </div>
 
 @if($notifications->hasPages())
 <div class="p-6 border-t border-sand ">
 {{ $notifications->links() }}
 </div>
 @endif
 @else
 <div class="py-24 text-center">
 <div class="inline-flex items-center justify-center w-20 h-20 bg-cream rounded-full mb-4">
 <svg class="w-10 h-10 text-sand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
 </svg>
 </div>
 <h3 class="text-xl font-bold text-cocoa mb-1">Belum ada notifikasi</h3>
 <p class="text-caramel">Kami akan memberi tahu Anda jika ada aktivitas baru.</p>
 </div>
 @endif
 </div>
</div>

<script>
 function markAllRead() {
 fetch('{{ route('notifications.mark-all-read') }}', {
 method: 'POST',
 headers: {
 'X-CSRF-TOKEN': '{{ csrf_token() }}',
 'Accept': 'application/json'
 }
 }).then(() => {
 window.location.reload();
 });
 }
</script>
@endsection
