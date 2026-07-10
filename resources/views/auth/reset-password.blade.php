<x-auth-layout>
    <div class="mb-7">
        <h1 class="text-2xl font-bold" style="color:#3B2417;">Buat Password Baru</h1>
        <p class="mt-2 text-sm" style="color:#8B5E3C;">Masukkan password baru yang kuat untuk akunmu.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-form-label for="email" value="Alamat Email" />
            <x-form-input id="email" name="email" type="email" autocomplete="username" required
                :value="old('email', $request->email)"
                placeholder="nama@email.com"
                :error="$errors->has('email')" />
            <x-form-error :messages="$errors->get('email')" />
        </div>

        <div x-data="{ show: false }">
            <x-form-label for="password" value="Password Baru" />
            <div class="relative">
                <x-form-input id="password" name="password" ::type="show ? 'text' : 'password'"
                    autocomplete="new-password" required
                    class="pr-12"
                    placeholder="Minimal 8 karakter"
                    :error="$errors->has('password')" />
                <button type="button" @click="show = !show"
                    class="absolute right-4 top-1/2 -translate-y-1/2 transition-colors"
                    style="color:#C69C6D;">
                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                </button>
            </div>
            <x-form-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-form-label for="password_confirmation" value="Konfirmasi Password" />
            <x-form-input id="password_confirmation" name="password_confirmation" type="password"
                autocomplete="new-password" required
                placeholder="Ulangi password baru"
                :error="$errors->has('password_confirmation')" />
            <x-form-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="pt-1">
            <x-button class="w-full" size="lg">Simpan Password Baru</x-button>
        </div>
    </form>
</x-auth-layout>
