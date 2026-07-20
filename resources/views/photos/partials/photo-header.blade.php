<div class="{{ $wrapperClass ?? 'flex items-center justify-between gap-3 px-3.5 py-2 bg-white/80 dark:bg-black/70 backdrop-blur-xl backdrop-saturate-150 border border-white/60 dark:border-white/20 shadow-lg rounded-full' }}">
    {{-- Left: Avatar + User Info --}}
    <div class="flex items-center gap-2.5 min-w-0 flex-1">
        @if($photo->user)
        <a href="{{ route('profile.show', $photo->user) }}" class="shrink-0 group">
            <img src="{{ $photo->user->avatar_url }}"
                 alt="{{ $photo->user->name }}"
                 class="w-8 h-8 rounded-full object-cover ring-1 ring-sand/60 group-hover:ring-brown transition-all">
        </a>
        <div class="flex-1 min-w-0">
            <a href="{{ route('profile.show', $photo->user) }}" class="inline-flex items-center gap-1 max-w-full">
                <span class="font-bold text-xs text-cocoa dark:text-white truncate leading-tight">{{ $photo->user->name }}</span>
                @if($photo->user->is_verified)
                    <x-verified-badge size="w-3.5 h-3.5" checkSize="w-2 h-2" />
                @endif
            </a>
            @if($photo->location)
                <p class="text-[10px] text-caramel dark:text-gray-300 truncate leading-tight mt-0.5">{{ $photo->location }}</p>
            @endif
        </div>
        @endif
    </div>

    {{-- Right: Follow Button + Share Icon + Options --}}
    <div class="flex items-center gap-1.5 shrink-0">
        {{-- Follow Button --}}
        @if(!auth()->check() || (auth()->id() !== $photo->user_id && $photo->user))
        <button
            @auth
                @click="toggleFollow()"
                :disabled="isFollowingLoading"
            @endauth
            @guest onclick="window.location='{{ route('login') }}'" @endguest
            :class="isFollowing
                ? 'bg-white/40 text-cocoa dark:text-gray-200 border border-sand/60 hover:bg-sand/30'
                : 'bg-brown text-white hover:bg-espresso shadow-sm'"
            class="text-[11px] font-bold transition-all active:scale-95 disabled:opacity-50 px-3 py-1 rounded-full">
            <span x-text="isFollowing ? 'Mengikuti' : 'Ikuti'">Ikuti</span>
        </button>
        @endif

        {{-- Share Icon Button --}}
        <button @click="isShareModalOpen = true"
                class="w-7 h-7 flex items-center justify-center text-cocoa dark:text-white rounded-full hover:bg-sand/30 transition-colors"
                title="Bagikan foto">
            <svg class="w-4 h-4 fill-current text-cocoa dark:text-white" viewBox="0 0 24 24">
                <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z"/>
            </svg>
        </button>

        {{-- Options "..." --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="isOptionsOpen = !isOptionsOpen"
                    class="w-7 h-7 flex items-center justify-center text-cocoa dark:text-white rounded-full hover:bg-sand/30 transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                </svg>
            </button>

            {{-- Options dropdown --}}
            <div x-show="isOptionsOpen" @click.away="isOptionsOpen = false"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute top-full right-0 mt-2 w-52 bg-white/95 dark:bg-[#2c2c2e] backdrop-blur-xl rounded-2xl shadow-2xl border border-sand/40 overflow-hidden z-50"
                 style="display:none;">

                <button @click="navigator.clipboard.writeText(window.location.href); window.showToast('Tautan disalin!'); isOptionsOpen = false;"
                    class="w-full text-left px-4 py-3 text-xs text-cocoa dark:text-white flex items-center gap-3 hover:bg-cream dark:hover:bg-white/5 transition-colors font-medium">
                    <svg class="w-4 h-4 text-caramel" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                    Salin Tautan
                </button>

                <a href="{{ route('photos.download', $photo) }}"
                   class="w-full text-left px-4 py-3 text-xs text-cocoa dark:text-white flex items-center gap-3 hover:bg-cream dark:hover:bg-white/5 transition-colors font-medium">
                    <svg class="w-4 h-4 text-caramel" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Unduh Foto
                </a>

                <button @click="isShareModalOpen = true; isOptionsOpen = false"
                    class="w-full text-left px-4 py-3 text-xs text-cocoa dark:text-white flex items-center gap-3 hover:bg-cream dark:hover:bg-white/5 transition-colors font-medium">
                    <svg class="w-4 h-4 text-caramel" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    Embed Postingan
                </button>

                @auth
                @if(auth()->id() !== $photo->user_id)
                    <div class="h-px bg-sand/30 mx-3"></div>
                    <button @click="isReportModalOpen = true; isOptionsOpen = false"
                        class="w-full text-left px-4 py-3 text-xs text-red-500 flex items-center gap-3 hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Laporkan
                    </button>
                @endif
                @endauth

                @can('update', $photo)
                    <div class="h-px bg-sand/30 mx-3"></div>
                    <a href="{{ route('photos.edit', $photo) }}"
                       class="w-full text-left px-4 py-3 text-xs text-cocoa dark:text-white flex items-center gap-3 hover:bg-cream dark:hover:bg-white/5 transition-colors font-medium">
                        <svg class="w-4 h-4 text-caramel" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit Postingan
                    </a>
                    <form action="{{ route('photos.destroy', $photo) }}" method="POST"
                          @submit.prevent="window.appConfirm('Hapus Postingan', 'Apakah Anda yakin?', () => $el.submit(), 'Hapus')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full text-left px-4 py-3 text-xs text-red-600 flex items-center gap-3 hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Hapus
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</div>