<x-auth-layout>
    <div>
        <h1 class="text-3xl font-display font-black tracking-tight text-cocoa">Selamat Datang Kembali</h1>
        <p class="mt-2 text-sm text-caramel">
            Belum punya akun?
            @if($cms['registration_open'])
            <a href="{{ route('register') }}" class="font-bold text-cocoa hover:underline decoration-2 underline-offset-4">Daftar sekarang gratis</a>
            @else
            <span class="text-sand">Pendaftaran sedang ditutup.</span>
            @endif
        </p>
    </div>

    <div class="mt-8">
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            {{-- Email --}}
            <div>
                <x-form-label for="email" value="Alamat Email" />
                <x-form-input
                    id="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    required
                    :value="old('email')"
                    placeholder="nama@email.com"
                    :error="$errors->has('email')"
                />
                <x-form-error :messages="$errors->get('email')" />
            </div>

            {{-- Password --}}
            <div x-data="{ show: false }">
                <div class="flex items-center justify-between mb-2">
                    <span class="block text-xs font-bold uppercase tracking-wider" style="color:#8B5E3C;letter-spacing:0.08em;">Kata Sandi</span>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-xs font-bold text-caramel hover:text-cocoa transition-colors">
                            Lupa kata sandi?
                        </a>
                    @endif
                </div>
                <div class="flex items-stretch rounded-2xl overflow-hidden" style="background-color:#FFF8ED; box-shadow: 0 0 0 1px #E3C79A;">
                    <input
                        id="password"
                        name="password"
                        x-bind:type="show ? 'text' : 'password'"
                        autocomplete="current-password"
                        required
                        placeholder="Masukkan kata sandi"
                        class="flex-1 py-3 pl-4 pr-2 text-sm outline-none text-cocoa placeholder:text-sand"
                        style="background-color:#FFF8ED !important; -webkit-box-shadow: 0 0 0 9999px #FFF8ED inset !important; box-shadow: 0 0 0 9999px #FFF8ED inset !important; -webkit-text-fill-color:#3B2417 !important; border:none; outline:none;"
                    />
                    <button
                        type="button"
                        @click="show = !show"
                        class="flex items-center justify-center w-10 h-full text-caramel hover:text-cocoa transition-colors flex-shrink-0 pr-2"
                        tabindex="-1"
                    >
                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                        </svg>
                    </button>
                </div>
                <x-form-error :messages="$errors->get('password')" />
            </div>

            {{-- Remember Me --}}
            <div class="flex items-center gap-3">
                <input
                    id="remember_me"
                    name="remember"
                    type="checkbox"
                    class="h-4 w-4 rounded border-sand text-cocoa focus:ring-sand bg-cream"
                >
                <label for="remember_me" class="text-sm font-bold text-caramel cursor-pointer select-none">
                    Ingat saya
                </label>
            </div>

            {{-- Submit --}}
            <div class="pt-2">
                <x-button class="w-full" size="lg">
                    Masuk ke Faiillery
                </x-button>
            </div>
        </form>
    </div>
</x-auth-layout>
