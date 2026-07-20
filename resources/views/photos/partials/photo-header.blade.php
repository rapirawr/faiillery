<div class="{{ $wrapperClass ?? 'sticky top-0 z-30 p-3 bg-transparent' }}">
    <div class="flex items-center gap-3 px-4 py-2.5 bg-white/25 dark:bg-neutral-900/40 backdrop-blur-xl backdrop-saturate-150 border border-white/40 dark:border-white/10 shadow-lg shadow-black/10 rounded-full">

        {{-- Avatar --}}
        @if($photo->user)
        <a href="{{ route('profile.show', $photo->user) }}" class="shrink-0">
            <img src="{{ $photo->user->avatar_url }}"
                 alt="{{ $photo->user->name }}"
                 class="w-9 h-9 rounded-full object-cover ring-[1.5px] ring-gray-200 dark:ring-white/20">
        </a>
        @endif

        {{-- Username + location --}}
        <div class="flex-1 min-w-0">
    @if($photo->user)
    <a href="{{ route('profile.show', $photo->user) }}" class="flex items-center gap-1">
        <span class="font-semibold text-[13px] text-white truncate leading-tight">{{ $photo->user->name }}</span>
        @if($photo->user->is_verified)
            <x-verified-badge size="w-3.5 h-3.5" checkSize="w-2 h-2" />
        @endif
    </a>
    @endif
    @if($photo->location)
        <p class="text-[11px] text-white truncate leading-tight mt-0.5">{{ $photo->location }}</p>
    @endif
</div>

        {{-- Follow button (if not own post) --}}
        @if(!auth()->check() || (auth()->id() !== $photo->user_id && $photo->user))
        <button
            @auth
                @click="toggleFollow()"
                :disabled="isFollowingLoading"
            @endauth
            @guest onclick="window.location='{{ route('login') }}'" @endguest
            :class="isFollowing
                ? 'text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-white/20'
                : 'text-blue-500 font-semibold'"
            class="text-[13px] transition-all active:scale-95 disabled:opacity-50 px-2 py-0.5 rounded-lg shrink-0">
            <span x-text="isFollowing ? 'Mengikuti' : 'Ikuti'">Ikuti</span>
        </button>
        @endif

        {{-- Options "..." --}}
        <div class="relative shrink-0" x-data="{ open: false }">
            <button @click="isOptionsOpen = !isOptionsOpen"
        class="w-8 h-8 flex items-center justify-center text-white rounded-full hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
        <svg class="w-[22px] h-[22px]" fill="currentColor" viewBox="0 0 24 24">
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
                 class="absolute top-full right-0 mt-2 w-52 bg-white dark:bg-[#2c2c2e] rounded-2xl shadow-2xl shadow-black/20 border border-gray-100 dark:border-white/10 overflow-hidden z-50"
                 style="display:none;">

                <button @click="navigator.clipboard.writeText(window.location.href); window.showToast('Tautan disalin!'); isOptionsOpen = false;"
                    class="w-full text-left px-4 py-3.5 text-sm text-dark dark:text-white flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                    Salin Tautan
                </button>

                <a href="{{ route('photos.download', $photo) }}"
                   class="w-full text-left px-4 py-3.5 text-sm text-dark dark:text-white flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Unduh
                </a>

                <button @click="isShareModalOpen = true; isOptionsOpen = false"
                    class="w-full text-left px-4 py-3.5 text-sm text-dark dark:text-white flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    Embed Postingan
                </button>

                @auth
                @if(auth()->id() !== $photo->user_id)
                    <div class="h-px bg-gray-100 dark:bg-white/10 mx-3"></div>
                    <button @click="isReportModalOpen = true; isOptionsOpen = false"
                        class="w-full text-left px-4 py-3.5 text-sm text-red-500 flex items-center gap-3 hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Laporkan
                    </button>
                @endif
                @endauth

                @can('update', $photo)
                    <div class="h-px bg-gray-100 dark:bg-white/10 mx-3"></div>
                    <a href="{{ route('photos.edit', $photo) }}"
                       class="w-full text-left px-4 py-3.5 text-sm text-dark dark:text-white flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit Postingan
                    </a>
                    <form action="{{ route('photos.destroy', $photo) }}" method="POST"
                          @submit.prevent="window.appConfirm('Hapus Postingan', 'Apakah Anda yakin?', () => $el.submit(), 'Hapus')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full text-left px-4 py-3.5 text-sm text-red-600 flex items-center gap-3 hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Hapus
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</div>