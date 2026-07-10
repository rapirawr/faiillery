<x-auth-layout>
 <div class="mb-6">
 <h1 class="text-3xl font-display font-black tracking-tight text-cocoa">Verifikasi Email</h1>
 <p class="mt-2 text-sm text-caramel">
 Terima kasih telah mendaftar! Sebelum memulai, harap verifikasi alamat email Anda dengan mengeklik tautan yang baru saja kami kirimkan.
 </p>
 </div>

 @if (session('status') == 'verification-link-sent')
 <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-sm font-bold text-green-600">
 Tautan verifikasi baru telah dikirim ke alamat email Anda.
 </div>
 @endif

 <div class="mt-8 flex flex-col gap-4">
 <form method="POST" action="{{ route('verification.send') }}">
 @csrf
 <x-button class="w-full" size="lg">
 Kirim Ulang Email Verifikasi
 </x-button>
 </form>

 <form method="POST" action="{{ route('logout') }}" class="text-center">
 @csrf
 <button type="submit" class="text-sm font-bold text-caramel hover:text-red-500 transition-colors">
 Keluar dari Akun
 </button>
 </form>
 </div>
</x-auth-layout>
