<!-- Floating Bottom Nav (Mobile Only) -->
<div x-data="{ openMenu: false }" class="fixed bottom-5 left-1/2 -translate-x-1/2 w-[92%] max-w-[400px] z-40 md:hidden">

 <!-- Action menu -->
 <div x-show="openMenu"
 x-transition:enter="transition ease-out duration-200"
 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
 x-transition:leave="transition ease-in duration-150"
 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
 x-transition:leave-end="opacity-0 translate-y-4 scale-95"
 class="absolute bottom-20 left-1/2 -translate-x-1/2 w-52 rounded-[22px] shadow-warm p-2 z-50"
 style="background:#FFF8ED;border:1px solid #E3C79A;display:none;"
 @click.away="openMenu = false">
 <div class="flex flex-col gap-0.5">
 <a href="{{ route('photos.create') }}" class="flex items-center gap-3 p-3 rounded-2xl transition-all" style="color:#5C3A21;" onmouseover="this.style.background='#F5E6CE'" onmouseout="this.style.background=''">
 <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#F5E6CE;">
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#8B5E3C;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
 </div>
 <span class="text-xs font-bold uppercase tracking-wider">Postingan</span>
 </a>
 <a href="{{ route('boards.create') }}" class="flex items-center gap-3 p-3 rounded-2xl transition-all" style="color:#5C3A21;" onmouseover="this.style.background='#F5E6CE'" onmouseout="this.style.background=''">
 <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#F5E6CE;">
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#8B5E3C;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
 </div>
 <span class="text-xs font-bold uppercase tracking-wider">Board</span>
 </a>
 <a href="{{ route('photos.photobooth') }}" class="flex items-center gap-3 p-3 rounded-2xl transition-all" style="color:#5C3A21;" onmouseover="this.style.background='#F5E6CE'" onmouseout="this.style.background=''">
 <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#F5E6CE;">
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#8B5E3C;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
 </div>
 <span class="text-xs font-bold uppercase tracking-wider">BStudio</span>
 </a>
 </div>
 </div>

 <!-- Nav bar -->
 <div class="ios-blur-bar rounded-[28px] flex items-center justify-around py-2 px-2">

 <!-- Home -->
 <a href="{{ route('home') }}" class="relative p-3 flex flex-col items-center justify-center transition-all duration-200 {{ request()->routeIs('home') ? '' : 'opacity-40' }}">
 <svg class="w-6 h-6" fill="{{ request()->routeIs('home') ? '#8B5E3C' : 'none' }}" stroke="#8B5E3C" viewBox="0 0 24 24" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
 @if(request()->routeIs('home'))<span class="absolute -bottom-1 w-1 h-1 rounded-full" style="background:#8B5E3C;"></span>@endif
 </a>

 <!-- Search -->
 <a href="{{ route('search') }}" class="relative p-3 flex flex-col items-center justify-center transition-all duration-200 {{ request()->routeIs('search') ? '' : 'opacity-40' }}">
 <svg class="w-6 h-6" fill="none" stroke="#8B5E3C" viewBox="0 0 24 24" stroke-width="2.2">
 <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z" />
 <path stroke-linecap="round" stroke-linejoin="round" d="M14.5 9.5L13 13l-3.5 1.5L11 11l3.5-1.5z" />
 </svg>
 @if(request()->routeIs('search'))<span class="absolute -bottom-1 w-1 h-1 rounded-full" style="background:#8B5E3C;"></span>@endif
 </a>

 @auth
 <!-- Create -->
 <button @click="openMenu = !openMenu" class="flex items-center justify-center mx-1 relative z-50">
 <div class="w-11 h-11 rounded-2xl flex items-center justify-center transition-all duration-200"
 :class="openMenu ? 'rotate-45 scale-90' : 'active:scale-90'"
 :style="openMenu ? 'background:#5C3A21;' : 'background:#8B5E3C;'"
 style="box-shadow:0 4px 12px rgba(139,94,60,0.3);">
 <svg class="w-6 h-6" fill="none" stroke="#FFF8ED" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
 </div>
 </button>

 <!-- Notifications -->
 <a href="{{ route('notifications.index') }}" class="relative p-3 flex flex-col items-center justify-center transition-all duration-200 {{ request()->routeIs('notifications.*') ? '' : 'opacity-40' }}">
 <svg class="w-6 h-6" fill="{{ request()->routeIs('notifications.*') ? '#8B5E3C' : 'none' }}" stroke="#8B5E3C" viewBox="0 0 24 24" stroke-width="2"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
 @if(auth()->user()->unreadNotifications()->count() > 0)
 <span class="absolute top-2.5 right-2.5 w-2.5 h-2.5 rounded-full ring-2 ring-cream" style="background:#8B5E3C;"></span>
 @endif
 @if(request()->routeIs('notifications.*'))<span class="absolute -bottom-1 w-1 h-1 rounded-full" style="background:#8B5E3C;"></span>@endif
 </a>

 <!-- Messages -->
 <a href="{{ route('messages.index') }}" class="relative p-3 flex flex-col items-center justify-center transition-all duration-200 {{ request()->routeIs('messages.*') ? '' : 'opacity-40' }}">
 <svg class="w-6 h-6" fill="{{ request()->routeIs('messages.*') ? '#8B5E3C' : 'none' }}" stroke="#8B5E3C" viewBox="0 0 24 24" stroke-width="2"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
 @if(request()->routeIs('messages.*'))<span class="absolute -bottom-1 w-1 h-1 rounded-full" style="background:#8B5E3C;"></span>@endif
 </a>
 @endauth
 </div>
</div>

<style>
 .ios-blur-bar {
     background: rgba(255, 248, 237, 0.60);
     -webkit-backdrop-filter: saturate(200%) blur(40px);
     backdrop-filter: saturate(200%) blur(40px);
     border: 1px solid rgba(227, 199, 154, 0.5);
     box-shadow:
         0 8px 32px rgba(91, 58, 33, 0.10),
         0 1px 0 rgba(255, 255, 255, 0.5) inset;
 }
</style>
