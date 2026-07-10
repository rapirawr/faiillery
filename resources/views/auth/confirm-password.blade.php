<x-auth-layout>
 <div class="mb-6">
 <h1 class="text-3xl font-display font-black tracking-tight text-cocoa">Konfirmasi Keamanan</h1>
 <p class="mt-2 text-sm text-caramel">
 Ini adalah area aman aplikasi. Harap konfirmasikan kata sandi Anda sebelum melanjutkan.
 </p>
 </div>

 <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
 @csrf

 <!-- Password -->
 <div>
 <x-form-label for="password" value="Kata Sandi" />
 <x-form-input id="password" class="block mt-1 w-full"
 type="password"
 name="password"
 required autocomplete="current-password"
 placeholder="••••••••"
 :error="$errors->has('password')" />
 <x-form-error :messages="$errors->get('password')" />
 </div>

 <div class="pt-2">
 <x-button class="w-full" size="lg">
 Konfirmasi Kata Sandi
 </x-button>
 </div>
 </form>
</x-auth-layout>
