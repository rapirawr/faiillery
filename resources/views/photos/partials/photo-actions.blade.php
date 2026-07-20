{{-- =====================================================================
     PARTIAL: photos/partials/photo-actions.blade.php
     Separated Action Bar: 
     - Left Pill: Like, Comment, Share, and Like Count
     - Right Pill: Collection/Bookmark (Save to Board)
     ===================================================================== --}}
<div class="flex items-center gap-3 px-3 py-2">
    
    {{-- ── BAGIAN 1: Like, Comment, Share, Like Count ── --}}
    <div class="flex-1 flex items-center justify-between px-4 py-2.5 bg-white/25 dark:bg-neutral-900/40 backdrop-blur-xl backdrop-saturate-150 border border-white/40 dark:border-white/10 shadow-lg shadow-black/10 rounded-full">
        
        <div class="flex items-center gap-4">
            {{-- Like button --}}
            <button id="like-btn{{ $suffix ?? '' }}"
                @auth @click="toggleLike()" @endauth
                @guest onclick="window.location='{{ route('login') }}'" @endguest
                :class="{ 'like-pop': likeAnimating }"
                class="transition-transform active:scale-90 text-dark dark:text-white flex items-center justify-center">
                <svg class="w-[22px] h-[22px] transition-all duration-150"
                     :class="liked ? 'text-red-500 fill-red-500' : 'text-dark dark:text-white fill-none'"
                     stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </button>

            {{-- Comment bubble --}}
            <button onclick="const f = document.getElementById('comment-input-field'); f?.scrollIntoView({ behavior: 'smooth', block: 'center' }); f?.focus();"
                    class="text-dark dark:text-white transition-opacity hover:opacity-60 flex items-center justify-center">
                <svg class="w-[22px] h-[22px] fill-none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </button>

            {{-- Share --}}
            <button @click="isShareModalOpen = true"
                    class="text-dark dark:text-white transition-opacity hover:opacity-60 flex items-center justify-center">
                <svg class="w-[22px] h-[22px] fill-none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </div>

        {{-- Like count --}}
        <div class="text-[13px] font-semibold text-dark dark:text-white select-none">
            <template x-if="likesCount > 0">
                <span>
                    <span x-text="formatLikes(likesCount)" class="tabular-nums"></span> suka
                </span>
            </template>
            <template x-if="likesCount === 0">
                <span></span>
            </template>
        </div>
    </div>

    {{-- ── BAGIAN 2: Koleksi (Save to Board) ── --}}
    <div class="relative shrink-0">
        <div class="flex items-center justify-center w-11 h-11 bg-white/25 dark:bg-neutral-900/40 backdrop-blur-xl backdrop-saturate-150 border border-white/40 dark:border-white/10 shadow-lg shadow-black/10 rounded-full">
            @auth
            <button @click="showBoards = !showBoards"
                    class="w-full h-full flex items-center justify-center transition-transform active:scale-90 text-dark dark:text-white">
                <svg class="w-5 h-5 transition-all duration-150"
                     :class="pinned ? 'fill-dark dark:fill-white' : 'fill-none'"
                     stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
            </button>

            {{-- Board modal dirender di root show.blade.php --}}
            @endauth

            @guest
            <a href="{{ route('login') }}" class="w-full h-full flex items-center justify-center text-dark dark:text-white">
                <svg class="w-5 h-5 fill-none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
            </a>
            @endguest
        </div>
    </div>
</div>