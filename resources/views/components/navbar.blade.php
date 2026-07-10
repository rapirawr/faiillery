<nav class="fixed top-0 left-0 w-full h-[64px] glass-nav z-50 flex items-center px-6 md:px-12 justify-between gap-8 transition-all duration-300">

    <!-- Brand -->
    <div class="flex items-center gap-6 shrink-0">
        <a href="{{ route('home') }}" class="group transition-transform duration-300 hover:scale-105 flex items-center gap-2.5">
            <div class="w-9 h-9 bg-cocoa flex items-center justify-center rounded-xl shrink-0" style="background:#8B5E3C;">
                <span class="font-black text-base leading-none" style="color:#FFF8ED;">FA</span>
            </div>
            <span class="hidden md:block font-black text-xl tracking-tight" style="color:#3B2417;">{{ $cms['site_name'] }}</span>
        </a>
        <div class="hidden lg:flex items-center gap-1">
            <a href="{{ route('search') }}" class="nav-link" title="Explore">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#8B5E3C;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 100 20 10 10 0 000-20zM12 13l3-4-4 3-3 4 4-3 3-4z" />
                </svg>
            </a>
        </div>
    </div>

    <!-- Search bar -->
    <div class="flex-1 max-w-3xl flex items-center h-full group"
         style="align-self: center;"
         x-data="{ query: '{{ request('q') }}', focused: false }">
        <form action="{{ route('search') }}" method="GET" class="relative w-full">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 z-10 pointer-events-none transition-colors duration-200 flex items-center" style="color:#C69C6D;">
                <!-- <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg> -->
            </div>
            <input
                type="text" name="q"
                x-model="query"
                @focus="focused = true" @blur="setTimeout(() => focused = false, 200)"
                placeholder="Cari foto di Faiillery..."
                class="w-full py-2.5 pl-12 pr-4 rounded-full text-sm font-medium placeholder:text-sand outline-none transition-all duration-300 leading-none"
                :style="focused
                    ? 'background:#FFF8ED; border: 1px solid #C69C6D; color:#5C3A21;'
                    : 'background:rgba(255,248,237,0.6); border: 1px solid #E3C79A; color:#5C3A21;'"
                autocomplete="off"
            >
        </form>
    </div>

    <!-- Actions -->
    <div class="flex items-center gap-3 shrink-0">
        @auth
            <!-- Create button -->
            <div class="relative hidden md:flex" x-data="{ openUpload: false, upTop: 0, upLeft: 0 }"
                x-init="$watch('openUpload', v => { if (v) { const r = $refs.createBtn.getBoundingClientRect(); upTop = r.bottom + 12; upLeft = Math.max(8, r.right - 192); } })">
                <button x-ref="createBtn" @click="openUpload = !openUpload"
                    @click.away="if (!$refs.createBtn.contains($event.target)) openUpload = false"
                    class="group flex items-center gap-2 px-5 py-2.5 rounded-full font-semibold text-sm transition-all duration-300 hover:shadow-warm active:scale-95"
                    style="background:#8B5E3C;color:#FFF8ED;">
                    <span>Create</span>
                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                </button>
                <template x-teleport="body">
                    <div x-show="openUpload"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-3"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        @click.away="if (!$refs.createBtn.contains($event.target)) openUpload = false"
                        class="fixed w-48 rounded-2xl border p-2 z-50"
                        :style="`top:${upTop}px; left:${upLeft}px; background:rgba(255,248,237,0.55);-webkit-backdrop-filter:saturate(180%) blur(30px);backdrop-filter:saturate(180%) blur(30px);border-color:rgba(227,199,154,0.5);box-shadow:0 8px 32px rgba(91,58,33,0.14),0 1px 0 rgba(255,255,255,0.5) inset;`">
                        <a href="{{ route('photos.create') }}" class="flex items-center gap-3 px-4 py-3 text-sm rounded-xl transition-all" style="color:#5C3A21;" onmouseover="this.style.background='#F5E6CE'" onmouseout="this.style.background=''">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h14a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="font-medium">Postingan</span>
                        </a>
                        <a href="{{ route('boards.create') }}" class="flex items-center gap-3 px-4 py-3 text-sm rounded-xl transition-all" style="color:#5C3A21;" onmouseover="this.style.background='#F5E6CE'" onmouseout="this.style.background=''">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <span class="font-medium">Board</span>
                        </a>
                        <a href="{{ route('photos.photobooth') }}" class="flex items-center gap-3 px-4 py-3 text-sm rounded-xl transition-all" style="color:#5C3A21;" onmouseover="this.style.background='#F5E6CE'" onmouseout="this.style.background=''">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="font-medium">BStudio</span>
                        </a>
                    </div>
                </template>
            </div>

            <!-- Messages -->
            @if($cms['allow_messages'])
            <a href="{{ route('messages.index') }}" class="relative w-10 h-10 hidden md:flex items-center justify-center rounded-full transition-all duration-200"
                style="color:#8B5E3C;" onmouseover="this.style.background='#F5E6CE'" onmouseout="this.style.background=''">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
            </a>
            @endif

            <!-- Notifications -->
            <a href="{{ route('notifications.index') }}" class="relative w-10 h-10 hidden md:flex items-center justify-center rounded-full transition-all duration-200"
                style="color:#8B5E3C;" onmouseover="this.style.background='#F5E6CE'" onmouseout="this.style.background=''">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                @if(auth()->user()->unreadNotifications()->count() > 0)
                    <span class="absolute top-2 right-2 w-2 h-2 rounded-full ring-2 ring-cream" style="background:#8B5E3C;"></span>
                @endif
            </a>

            <!-- Profile dropdown -->
            <div class="relative" x-data="{ open: false, pTop: 0, pLeft: 0 }"
                x-init="$watch('open', v => { if (v) { const r = $refs.profileBtn.getBoundingClientRect(); pTop = r.bottom + 12; pLeft = Math.max(8, r.right - 288); } })">
                <button x-ref="profileBtn" @click="open = !open"
                    @click.away="if (!$refs.profileBtn.contains($event.target)) open = false"
                    class="relative p-0.5 rounded-full ring-2 hover:ring-caramel transition-all duration-300 overflow-hidden"
                    style="ring-color:#E3C79A;">
                    <img src="{{ auth()->user()->avatar_url }}" alt="Profile" class="w-9 h-9 rounded-full object-cover">
                </button>

                <template x-teleport="body">
                    <div x-show="open"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-3"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-3"
                        @click.away="if (!$refs.profileBtn.contains($event.target)) open = false"
                        class="fixed w-72 rounded-[24px] z-50"
                        :style="`top:${pTop}px; left:${pLeft}px; background:rgba(255,248,237,0.55);-webkit-backdrop-filter:saturate(180%) blur(30px);backdrop-filter:saturate(180%) blur(30px);border:1px solid rgba(227,199,154,0.5);box-shadow:0 8px 32px rgba(91,58,33,0.14),0 1px 0 rgba(255,255,255,0.5) inset;`">

                        <!-- Profile header -->
                        <div class="p-5 flex items-center gap-3" style="background:rgba(245,230,206,0.6);border-bottom:1px solid rgba(227,199,154,0.35);border-radius:24px 24px 0 0;">
                            <img src="{{ auth()->user()->avatar_url }}" alt="Profile" class="w-11 h-11 rounded-full object-cover" style="border:2px solid #E3C79A;">
                            <div class="overflow-hidden">
                                <div class="font-bold text-sm truncate" style="color:#3B2417;">{{ auth()->user()->name }}</div>
                                <div class="text-xs truncate" style="color:#C69C6D;">{{ auth()->user()->email }}</div>
                            </div>
                        </div>

                        <!-- Menu items -->
                        <div class="p-2 space-y-0.5" style="border-radius:0 0 24px 24px;">
                            @php
                                $menuItem = 'flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all w-full text-left';
                            @endphp
                            <a href="{{ route('profile.show', auth()->user()) }}" class="{{ $menuItem }}" style="color:#5C3A21;" onmouseover="this.style.background='#F5E6CE'" onmouseout="this.style.background=''">
                                <span class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#F5E6CE;">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </span>
                                Profil Saya
                            </a>
                            <a href="{{ route('photos.photobooth') }}" class="{{ $menuItem }}" style="color:#5C3A21;" onmouseover="this.style.background='#F5E6CE'" onmouseout="this.style.background=''">
                                <span class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#F5E6CE;">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </span>
                                Photobooth Studio
                            </a>
                            <a href="{{ route('profile.edit') }}" class="{{ $menuItem }}" style="color:#5C3A21;" onmouseover="this.style.background='#F5E6CE'" onmouseout="this.style.background=''">
                                <span class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#F5E6CE;">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </span>
                                Pengaturan
                            </a>

                            @if(auth()->user()->is_admin)
                                <a href="{{ route('admin.dashboard') }}" class="{{ $menuItem }}" style="color:#8B5E3C;" onmouseover="this.style.background='#F5E6CE'" onmouseout="this.style.background=''">
                                    <span class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#F5E6CE;">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19h14v2H5v-2z"></path>
                                        </svg>
                                    </span>
                                    God Mode
                                </a>
                            @endif

                            <div class="h-px my-1 mx-2" style="background:#E3C79A;"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="{{ $menuItem }}" style="color:#b91c1c;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background=''">
                                    <span class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fef2f2;">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    </span>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </template>
            </div>
        @endauth

        @guest
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="text-sm font-semibold transition-colors" style="color:#8B5E3C;" onmouseover="this.style.color='#5C3A21'" onmouseout="this.style.color='#8B5E3C'">Masuk</a>
                @if($cms['registration_open'])
                <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-full font-bold text-sm transition-all hover:scale-105 active:scale-95 shadow-warm" style="background:#8B5E3C;color:#FFF8ED;">Daftar</a>
                @endif
            </div>
        @endguest
    </div>
</nav>