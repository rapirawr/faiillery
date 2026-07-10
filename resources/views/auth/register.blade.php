<x-auth-layout>
 <div>
 <h1 class="text-3xl font-display font-black tracking-tight text-cocoa">Buat Akun Baru</h1>
 <p class="mt-2 text-sm text-caramel">
 Sudah punya akun? 
 <a href="{{ route('login') }}" class="font-bold text-cocoa hover:underline decoration-2 underline-offset-4">Masuk ke akun Anda</a>
 </p>
 </div>

 <div class="mt-8">
 <form method="POST" action="{{ route('register') }}" class="space-y-5">
 @csrf


 <!-- Username -->
 <div>
 <x-form-label for="username" value="Username" />
 <x-form-input id="username" name="username" type="text" autocomplete="username" required 
 :value="old('username')"
 placeholder="johndoe123"
 :error="$errors->has('username')" />
 <x-form-error :messages="$errors->get('username')" />
 </div>

 <!-- Email Address -->
 <div>
 <x-form-label for="email" value="Alamat Email" />
 <x-form-input id="email" name="email" type="email" autocomplete="email" required 
 :value="old('email')"
 placeholder="nama@email.com"
 :error="$errors->has('email')" />
 <x-form-error :messages="$errors->get('email')" />
 </div>

 <!-- Password -->
 <div x-data="{ show: false }">
 <x-form-label for="password" value="Kata Sandi" />
 <div class="mt-2 relative">
 <x-form-input id="password" name="password" ::type="show ? 'text' : 'password'" autocomplete="new-password" required 
 class="pr-12"
 placeholder="Minimal 8 karakter"
 :error="$errors->has('password')" />
 
 <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-caramel hover:text-cocoa transition-colors">
 <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
 <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
 </button>
 </div>
 <x-form-error :messages="$errors->get('password')" />
 </div>


 <div class="pt-2">
 <x-button class="w-full" size="lg">
 Daftar Akun Faiillery
 </x-button>
 </div>
 </form>

 <p class="mt-8 text-center text-xs text-caramel">
 Dengan mendaftar, Anda menyetujui 
 <a href="#" class="font-bold text-cocoa hover:underline">Ketentuan Layanan</a> 
 dan 
 <a href="#" class="font-bold text-cocoa hover:underline">Kebijakan Privasi</a> kami.
 </p>
 </div>
</x-auth-layout>
