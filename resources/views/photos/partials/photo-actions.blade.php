<div class="flex items-center justify-between gap-3 px-4 py-2.5 bg-white/60 dark:bg-white/5 rounded-2xl border border-sand/30">
    {{-- Left: Like & Comment --}}
    <div class="flex items-center gap-3.5">
        {{-- Like button --}}
        <button id="like-btn{{ $suffix ?? '' }}"
            @auth @click="toggleLike()" @endauth
            @guest onclick="window.location='{{ route('login') }}'" @endguest
            :class="{ 'like-pop': likeAnimating }"
            class="transition-transform active:scale-90 flex items-center gap-1.5 text-cocoa dark:text-white">
            <svg class="w-5 h-5 transition-all duration-150"
                 :class="liked ? 'text-red-500 fill-red-500 stroke-red-500' : 'text-cocoa dark:text-white fill-none stroke-current'"
                 stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <span class="text-xs font-bold text-cocoa dark:text-white select-none tabular-nums" x-text="formatLikes(likesCount) + ' suka'"></span>
        </button>

        {{-- Theater Mode button --}}
        <button @click="$dispatch('open-theater', { 
                    photos: [
                        {
                            uid: '{{ $photo->uid }}',
                            title: '{{ addslashes($photo->title) }}',
                            description: '{{ addslashes($photo->description) }}',
                            image_url: '{{ $photo->image_url }}',
                            uploader_name: '{{ addslashes($photo->user->name ?? "Anonymous") }}',
                            uploader_username: '{{ $photo->user->username ?? "anonymous" }}',
                            uploader_avatar: '{{ $photo->user->avatar_url ?? "https://i.pravatar.cc/150" }}',
                            likes_count: {{ $photo->likes_count }},
                            comments_count: {{ $photo->comments()->count() }},
                            is_liked: {{ auth()->check() && auth()->user()->hasLiked($photo) ? 'true' : 'false' }},
                            detail_url: '{{ route('photos.show', $photo->uid) }}',
                            is_video: {{ $photo->isVideo() ? 'true' : 'false' }}
                        }
                        @if($relatedPhotos->count() > 0)
                            @foreach($relatedPhotos as $rel)
                            , {
                                uid: '{{ $rel->uid }}',
                                title: '{{ addslashes($rel->title) }}',
                                description: '{{ addslashes($rel->description) }}',
                                image_url: '{{ $rel->image_url }}',
                                uploader_name: '{{ addslashes($rel->user->name ?? "Anonymous") }}',
                                uploader_username: '{{ $rel->user->username ?? "anonymous" }}',
                                uploader_avatar: '{{ $rel->user->avatar_url ?? "https://i.pravatar.cc/150" }}',
                                likes_count: {{ $rel->likes_count }},
                                comments_count: {{ $rel->comments()->count() }},
                                is_liked: {{ auth()->check() && auth()->user()->hasLiked($rel) ? 'true' : 'false' }},
                                detail_url: '{{ route('photos.show', $rel->uid) }}',
                                is_video: {{ $rel->isVideo() ? 'true' : 'false' }}
                            }
                            @endforeach
                        @endif
                    ], 
                    startIndex: 0 
                })"
                class="w-7 h-7 rounded-full bg-sand/30 hover:bg-brown hover:text-white text-brown dark:text-caramel flex items-center justify-center transition-all active:scale-90 shadow-sm border border-white/20"
                title="Theater Mode / Slideshow">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
        </button>

        {{-- Comment bubble --}}
        {{-- <button onclick="const f = document.getElementById('comment-input-field'); f?.focus();"
                class="text-cocoa dark:text-white hover:text-brown transition-colors flex items-center justify-center"
                title="Tulis komentar">
            <svg class="w-5 h-5 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </button> --}}
    </div>

    {{-- Right: Save Action Button --}}
    <div class="flex items-center gap-2 shrink-0">
        {{-- Save to board button --}}
        @auth
        <button @click="showBoards = !showBoards"
                class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-brown hover:bg-espresso text-white transition-all active:scale-95 text-xs font-semibold shadow-sm"
                title="Simpan ke Papan">
            <svg class="w-4 h-4 transition-all duration-150"
                 :class="pinned ? 'fill-white stroke-white' : 'fill-none stroke-white'"
                 stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
            </svg>
            <span x-text="pinned ? 'Tersimpan' : 'Simpan'">Simpan</span>
        </button>
        @endauth

        @guest
        <a href="{{ route('login') }}" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-brown hover:bg-espresso text-white transition-all text-xs font-semibold shadow-sm">
            <svg class="w-4 h-4 fill-none stroke-white" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
            </svg>
            <span>Simpan</span>
        </a>
        @endguest
    </div>
</div>