@props(['photo' => null])

@php
    // Fallback data if photo prop is empty or missing
    $photoTitle = $photo->title ?? 'Abstract Waves & Flow';
    $photoUrl = $photo->image_url ?? $photo->thumbnail_url ?? 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=800&q=80';
    $photoAspect = ($photo && isset($photo->height, $photo->width) && $photo->width > 0) 
        ? ($photo->height / $photo->width) * 100 
        : 125;
    $dominantColor = $photo->dominant_color ?? '#3B2417';
    $uploaderName = $photo->user->name ?? 'Aria Designer';
    $uploaderUsername = $photo->user->username ?? 'ariadesign';
    $uploaderAvatar = $photo->user->avatar_url ?? 'https://i.pravatar.cc/150?u='.$uploaderUsername;
    $likesCount = $photo->likes_count ?? 142;
    $commentsCount = $photo->comments_count ?? 28;
    $photoShowRoute = $photo ? route('photos.show', $photo->uid ?? $photo->id ?? 1) : '#';
    $profileShowRoute = ($photo && isset($photo->user)) ? route('profile.show', $photo->user) : '#';
@endphp

<div x-data="{ 
        hovered: false, 
        liked: {{ ($photo->is_liked ?? false) ? 'true' : 'false' }}, 
        likesCount: {{ $likesCount }},
        saved: false,
        toggleLike() {
            this.liked = !this.liked;
            this.likesCount += this.liked ? 1 : -1;
        }
     }" 
     @mouseenter="hovered = true" 
     @mouseleave="hovered = false"
     class="relative group rounded-[20px] overflow-hidden shadow-lg shadow-black/10 border border-white/20 transition-all duration-300 hover:shadow-2xl hover:shadow-black/20 transform hover:-translate-y-1"
     style="padding-bottom: {{ $photoAspect }}%; background: {{ $dominantColor }};">

    <!-- Photo Media Container -->
    <a href="{{ $photoShowRoute }}" class="absolute inset-0 w-full h-full block overflow-hidden">
        <img src="{{ $photoUrl }}" 
             alt="{{ $photoTitle }}" 
             class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
             loading="lazy" />
    </a>

    <!-- Top Corner Floating Glass Action Pills -->
    <div class="absolute top-3 right-3 flex items-center gap-2 pointer-events-auto z-10">
        <button @click.prevent="toggleLike()" 
                class="h-8 px-3 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur-xl backdrop-saturate-150 border border-white/20 text-white flex items-center gap-1.5 text-xs font-bold transition-all duration-200 active:scale-90 shadow-md">
            <svg class="w-3.5 h-3.5 transition-colors duration-200" 
                 :class="liked ? 'text-red-400 fill-current' : 'text-white fill-none'" 
                 stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <span x-text="likesCount"></span>
        </button>

        <button @click.prevent="saved = !saved" 
                class="h-8 w-8 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur-xl backdrop-saturate-150 border border-white/20 text-white flex items-center justify-center text-xs transition-all duration-200 active:scale-90 shadow-md"
                :class="{ '!bg-[#8B5E3C] !border-[#C69C6D] !text-[#FFF8ED]': saved }"
                title="Simpan ke Board">
            <svg class="w-4 h-4" :class="saved ? 'fill-current' : 'fill-none'" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
            </svg>
        </button>
    </div>

    <!-- Frosted Glass Overlay (Appears on Hover) -->
    <div x-show="hovered" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2 backdrop-blur-none"
         x-transition:enter-end="opacity-100 translate-y-0 backdrop-blur-xl"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 backdrop-blur-xl"
         x-transition:leave-end="opacity-0 translate-y-2 backdrop-blur-none"
         class="absolute inset-0 bg-white/10 backdrop-blur-xl backdrop-saturate-150 border border-white/20 p-4 flex flex-col justify-between z-20 pointer-events-auto transition-all"
         style="display: none;">

        <div class="flex items-center justify-between text-xs font-semibold text-white/90">
            <div class="flex items-center gap-3">
                <span class="flex items-center gap-1 bg-white/15 px-2.5 py-1 rounded-full border border-white/20">
                    <svg class="w-3.5 h-3.5 text-amber-300 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    <span>4.9</span>
                </span>
                <span class="flex items-center gap-1 bg-white/15 px-2.5 py-1 rounded-full border border-white/20">
                    <svg class="w-3.5 h-3.5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span>{{ $commentsCount }}</span>
                </span>
            </div>
            <a href="{{ $photoShowRoute }}" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 border border-white/30 flex items-center justify-center text-white transition-all transform hover:rotate-45">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>

        <div class="my-auto py-2">
            <a href="{{ $photoShowRoute }}" class="block">
                <h3 class="text-white font-bold text-base md:text-lg leading-snug drop-shadow-sm line-clamp-2 hover:underline">
                    {{ $photoTitle }}
                </h3>
            </a>
        </div>

        <div class="pt-3 border-t border-white/20 flex items-center justify-between">
            <a href="{{ $profileShowRoute }}" class="flex items-center gap-2.5 group/user">
                <img src="{{ $uploaderAvatar }}" alt="{{ $uploaderName }}" class="w-8 h-8 rounded-full object-cover border border-white/40 shadow-sm transition-transform group-hover/user:scale-110" />
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-white leading-tight group-hover/user:underline truncate max-w-[110px]">
                        {{ $uploaderName }}
                    </span>
                    <span class="text-[10px] text-white/70 font-medium truncate max-w-[110px]">
                        @ {{ $uploaderUsername }}
                    </span>
                </div>
            </a>
            <a href="{{ $photoShowRoute }}" 
               class="px-3.5 py-1.5 rounded-full bg-[#8B5E3C] hover:bg-[#5C3A21] text-[#FFF8ED] text-xs font-bold shadow-md transition-all active:scale-95 border border-[#C69C6D]/40">
                Detail
            </a>
        </div>
    </div>
</div>

<style>
    @supports not ((-webkit-backdrop-filter: blur(1px)) or (backdrop-filter: blur(1px))) {
        .backdrop-blur-xl, .backdrop-blur-md {
            background-color: rgba(255, 248, 237, 0.92) !important;
            color: #3B2417 !important;
        }
    }
</style>
