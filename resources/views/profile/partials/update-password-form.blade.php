<section>
 <header>
 <h2 class="text-lg font-bold text-cocoa">
 Perbarui Kata Sandi
 </h2>

 <p class="mt-1 text-sm text-caramel">
 Pastikan akun Anda menggunakan kata sandi yang panjang dan acak untuk tetap aman.
 </p>
 </header>

 <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
 @csrf
 @method('put')

 <div>
 <x-form-label for="update_password_current_password" value="Kata Sandi Saat Ini" />
 <x-form-input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" :error="$errors->updatePassword->has('current_password')" />
 <x-form-error :messages="$errors->updatePassword->get('current_password')" />
 </div>

 <div>
 <x-form-label for="update_password_password" value="Kata Sandi Baru" />
 <x-form-input id="update_password_password" name="password" type="password" autocomplete="new-password" :error="$errors->updatePassword->has('password')" />
 <x-form-error :messages="$errors->updatePassword->get('password')" />
 </div>

 <div>
 <x-form-label for="update_password_password_confirmation" value="Konfirmasi Kata Sandi" />
 <x-form-input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" :error="$errors->updatePassword->has('password_confirmation')" />
 <x-form-error :messages="$errors->updatePassword->get('password_confirmation')" />
 </div>

 <div class="flex items-center gap-4">
 <x-button variant="primary">Simpan Kata Sandi</x-button>

 @if (session('status') === 'password-updated')
 <p
 x-data="{ show: true }"
 x-show="show"
 x-transition
 x-init="setTimeout(() => show = false, 2000)"
 class="text-sm text-caramel font-medium"
 >Berhasil disimpan.</p>
 @endif
 </div>
 </form>
</section>
