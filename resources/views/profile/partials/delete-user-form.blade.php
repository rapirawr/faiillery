<section class="space-y-6">
 <header>
 <h2 class="text-lg font-bold text-cocoa">
 Hapus Akun
 </h2>

 <p class="mt-1 text-sm text-caramel">
 Setelah akun Anda dihapus, semua sumber daya dan datanya akan dihapus secara permanen. Sebelum menghapus akun Anda, harap unduh data atau informasi apa pun yang ingin Anda simpan.
 </p>
 </header>

 <x-button variant="danger"
 x-data=""
 x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
 >Hapus Akun Permanen</x-button>

 <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
 <form method="post" action="{{ route('profile.destroy') }}" class="p-8">
 @csrf
 @method('delete')

 <h2 class="text-xl font-bold text-cocoa">
 Apakah Anda yakin ingin menghapus akun?
 </h2>

 <p class="mt-2 text-sm text-caramel">
 Setelah akun Anda dihapus, semua sumber daya dan datanya akan dihapus secara permanen. Harap masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun Anda secara permanen.
 </p>

 <div class="mt-6">
 <x-form-label for="password" value="Kata Sandi" class="sr-only" />

 <x-form-input
 id="password"
 name="password"
 type="password"
 class="mt-1 block w-full sm:w-3/4"
 placeholder="Masukkan kata sandi Anda"
 :error="$errors->userDeletion->has('password')"
 />

 <x-form-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
 </div>

 <div class="mt-8 flex justify-end gap-3">
 <x-button variant="secondary" type="button" x-on:click="$dispatch('close')">
 Batal
 </x-button>

 <x-button variant="danger">
 Hapus Akun
 </x-button>
 </div>
 </form>
 </x-modal>
</section>
