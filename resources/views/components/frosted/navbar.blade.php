<nav x-data="{ 
        scrolled: false, 
        profileOpen: false, 
        createOpen: false,
        searchQuery: '{{ request('q') }}',
        searchFocused: false,
        init() {
            this.scrolled = window.pageYOffset > 15;
        }
     }" 
     @scroll.window="scrolled = (window.pageYOffset > 15)"
     class="fixed top-0 left-0 w-full h-[64px] md:h-[72px] z-50 transition-all duration-300 flex items-center px-4 md:px-8 justify-between gap-4 bg-[#FFF8ED]/85 backdrop-blur-xl backdrop-saturate-180 border-b border-[#E3C79A]/60 shadow-md text-[#3B2417]"
     :class="{ 'shadow-lg bg-[#FFF8ED]/95 border-[#C69C6D]/70': scrolled }">

    <!-- Brand / Logo -->
    <div class="flex items-center gap-6 shrink-0">
        <a href="{{ route('home') }}" class="group transition-transform duration-300 hover:scale-105 flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-[#8B5E3C] border border-[#C69C6D] flex items-center justify-center shadow-md transition-all group-hover:bg-[#5C3A21]">
                <span class="font-black text-lg text-[#FFF8ED] tracking-tighter">FA</span>
            </div>
            <span class="hidden md:block font-black text-xl tracking-tight text-[#3B2417] font-['Outfit']">
                {{ $cms['site_name'] ?? 'Failerry' }}
            </span>
        </a>
        <div class="hidden lg:flex items-center gap-1">
            <a href="{{ route('search') }}" 
               class="px-4 py-2 rounded-full text-sm font-semibold transition-all hover:bg-[#F5E6CE] text-[#8B5E3C] hover:text-[#3B2417] flex items-center gap-1.5"
               title="Explore">
                <svg class="w-4 h-4 text-[#8B5E3C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 100 20 10 10 0 000-20zM12 13l3-4-4 3-3 4 4-3 3-4z" />
                </svg>
                <span>Jelajahi</span>
            </a>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="flex-1 max-w-2xl mx-2">
        <form action="{{ route('search') }}" method="GET" class="relative w-full">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 z-10 pointer-events-none text-[#C69C6D]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text" 
                   name="q" 
                   x-model="searchQuery"
                   @focus="searchFocused = true" 
                   @blur="searchFocused = false"
                   placeholder="Cari foto di Failerry..."
                   class="w-full pl-11 pr-4 py-2 rounded-full text-sm font-medium text-[#3B2417] placeholder-[#C69C6D] outline-none transition-all duration-300 bg-[#F5E6CE]/70 hover:bg-[#F5E6CE] focus:bg-[#FFF8ED] border border-[#E3C79A] focus:border-[#C69C6D] shadow-inner"
                   :class="{ 'ring-2 ring-[#C69C6D]/40': searchFocused }"
                   autocomplete="off" />
        </form>
    </div>

    <!-- Actions -->
    <div class="flex items-center gap-3 shrink-0">
        @auth
            <!-- Create Button & Dropdown -->
            <div class="relative hidden md:block" @click.away="createOpen = false">
                <button @click="createOpen = !createOpen"
                        class="group px-5 py-2 rounded-full font-bold text-sm bg-[#8B5E3C] hover:bg-[#5C3A21] text-[#FFF8ED] shadow-md flex items-center gap-2 transition-all active:scale-95">
                    <span>Create</span>
                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>

                <div x-show="createOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                     class="absolute right-0 mt-3 w-56 rounded-[20px] bg-[#FFF8ED]/90 backdrop-blur-xl border border-[#E3C79A] shadow-2xl p-2 z-50 text-[#3B2417]"
                     style="display: none;">
                    <a href="{{ route('photos.create') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-[#F5E6CE] text-sm font-semibold transition-all">
                        <svg class="w-4 h-4 text-[#8B5E3C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h14a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Postingan</span>
                    </a>
                    <a href="{{ route('boards.create') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-[#F5E6CE] text-sm font-semibold transition-all">
                        <svg class="w-4 h-4 text-[#8B5E3C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Board</span>
                    </a>
                    <a href="{{ route('photos.photobooth') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-[#F5E6CE] text-sm font-semibold transition-all">
                        <svg class="w-4 h-4 text-[#8B5E3C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>BStudio</span>
                    </a>
                </div>
            </div>

            <!-- Messages Link -->
            @if($cms['allow_messages'] ?? true)
            <a href="{{ route('messages.index') }}" 
               class="relative w-10 h-10 hidden md:flex items-center justify-center rounded-full bg-[#F5E6CE]/70 hover:bg-[#F5E6CE] border border-[#E3C79A] text-[#8B5E3C] hover:text-[#3B2417] transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
            </a>
            @endif

            <!-- Notifications Link -->
            <a href="{{ route('notifications.index') }}" 
               class="relative w-10 h-10 hidden md:flex items-center justify-center rounded-full bg-[#F5E6CE]/70 hover:bg-[#F5E6CE] border border-[#E3C79A] text-[#8B5E3C] hover:text-[#3B2417] transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                @if(auth()->user()->unreadNotifications()->count() > 0)
                    <span class="absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-[#8B5E3C] ring-2 ring-[#FFF8ED]"></span>
                @endif
            </a>

            <!-- Profile Menu -->
            <div class="relative" @click.away="profileOpen = false">
                <button @click="profileOpen = !profileOpen"
                        class="relative p-0.5 rounded-full border-2 border-[#E3C79A] hover:border-[#8B5E3C] transition-all shadow-sm overflow-hidden active:scale-95">
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-9 h-9 rounded-full object-cover" />
                </button>

                <div x-show="profileOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                     class="absolute right-0 mt-3 w-72 rounded-[20px] bg-[#FFF8ED]/95 backdrop-blur-xl border border-[#E3C79A] shadow-2xl p-3 z-50 text-[#3B2417]"
                     style="display: none;">

                    <div class="p-3 bg-[#F5E6CE]/80 rounded-2xl border border-[#E3C79A]/60 mb-2 flex items-center gap-3">
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-10 h-10 rounded-full object-cover border border-[#E3C79A]" />
                        <div class="overflow-hidden">
                            <div class="font-bold text-sm text-[#3B2417] truncate">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-[#8B5E3C] truncate">{{ auth()->user()->email }}</div>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <a href="{{ route('profile.show', auth()->user()) }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-[#F5E6CE] text-sm font-semibold transition-all">
                            <span class="w-7 h-7 rounded-lg bg-[#F5E6CE] flex items-center justify-center">
                                <svg class="w-4 h-4 text-[#8B5E3C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </span>
                            <span>Profil Saya</span>
                        </a>
                        <a href="{{ route('photos.photobooth') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-[#F5E6CE] text-sm font-semibold transition-all">
                            <span class="w-7 h-7 rounded-lg bg-[#F5E6CE] flex items-center justify-center">
                                <svg class="w-4 h-4 text-[#8B5E3C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </span>
                            <span>Photobooth Studio</span>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-[#F5E6CE] text-sm font-semibold transition-all">
                            <span class="w-7 h-7 rounded-lg bg-[#F5E6CE] flex items-center justify-center">
                                <svg class="w-4 h-4 text-[#8B5E3C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </span>
                            <span>Pengaturan</span>
                        </a>

                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-[#F5E6CE] text-[#8B5E3C] text-sm font-semibold transition-all">
                                <span class="w-7 h-7 rounded-lg bg-[#F5E6CE] flex items-center justify-center">
                                    <svg class="w-4 h-4 text-[#8B5E3C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19h14v2H5v-2z"/></svg>
                                </span>
                                <span>God Mode</span>
                            </a>
                        @endif
                    </div>

                    <div class="h-px bg-[#E3C79A] my-2"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-red-50 text-red-600 text-sm font-semibold transition-all text-left">
                            <span class="w-7 h-7 rounded-lg bg-red-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            </span>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        @endauth

        @guest
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="text-sm font-bold text-[#8B5E3C] hover:text-[#5C3A21] hover:bg-[#F5E6CE] px-4 py-2 rounded-full transition-all">Masuk</a>
                @if($cms['registration_open'] ?? true)
                <a href="{{ route('register') }}" class="px-5 py-2 rounded-full font-bold text-sm bg-[#8B5E3C] hover:bg-[#5C3A21] text-[#FFF8ED] shadow-md transition-all active:scale-95">Daftar</a>
                @endif
            </div>
        @endguest
    </div>
</nav>

<style>
    @supports not ((-webkit-backdrop-filter: blur(1px)) or (backdrop-filter: blur(1px))) {
        nav {
            background-color: #FFF8ED !important;
            border-bottom-color: #E3C79A !important;
        }
    }
</style>
