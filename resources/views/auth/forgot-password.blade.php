<x-auth-layout>
    <div class="mb-7">
        <h1 class="text-2xl font-bold" style="color:#3B2417;">Reset Kata Sandi</h1>
        <p class="mt-2 text-sm leading-relaxed" style="color:#8B5E3C;">
            Masukkan email kamu, lalu klik Reset Kata Sandi. Sistem akan membuat kode angka sebagai password sementara kamu.
        </p>
    </div>

    <x-auth-session-status class="mb-5 text-sm font-semibold text-green-600 bg-green-50 border border-green-200 rounded-xl px-4 py-3" :status="session('status')" />

    @if(session('temporary_password'))
        <div class="mb-5 rounded-2xl border border-brown/20 bg-brown/10 p-4 text-sm text-brown">
            <p class="font-bold mb-2">Password sementara kamu:</p>
            <p class="text-lg font-semibold tracking-[0.18em]">{{ session('temporary_password') }}</p>
            <p class="mt-3 text-xs text-caramel">Harap segera ganti kata sandi setelah login.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-form-label for="email" value="Alamat Email" />
            <x-form-input id="email" name="email" type="email" autocomplete="email" required
                :value="old('email')"
                placeholder="nama@email.com"
                :error="$errors->has('email')" />
            <x-form-error :messages="$errors->get('email')" />
        </div>

        <div class="pt-1">
            <x-button class="w-full" size="lg">Dapatkan kode sementara </x-button>
        </div>
    </form>

    <p class="text-center text-sm mt-6" style="color:#C69C6D;">
        Ingat password?
        <a href="{{ route('login') }}" class="font-semibold hover:underline" style="color:#5C3A21;">Kembali masuk</a>
    </p>
</x-auth-layout>
