<section x-data="profileEdit()">
 <header class="mb-8">
 <h2 class="text-2xl font-display font-bold text-cocoa">
 Informasi Publik
 </h2>
 <p class="mt-1 text-sm text-caramel">
 Kelola detail profil Anda yang akan dilihat oleh pengguna lain.
 </p>
 </header>

 <form method="post" action="{{ route('profile.update') }}" class="space-y-10" enctype="multipart/form-data">
 @csrf
 @method('patch')

 <!-- Visuals Section (Avatar & Cover) -->
 <div class="space-y-6">
 <label class="block text-xs font-semibold text-caramel uppercase tracking-widest">Visual Profil</label>
 
 <div class="relative">
 <!-- Cover Photo Container -->
 <div class="h-48 w-full bg-cream rounded-2xl overflow-hidden relative group border border-sand">
 <img :src="coverUrl" class="w-full h-full object-cover" x-show="coverUrl">
 <div x-show="!coverUrl" class="w-full h-full flex items-center justify-center text-caramel">
 <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
 </div>
 
 <!-- Cover Change Button -->
 <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
 <button type="button" @click="$refs.coverInput.click()" class="bg-white/90 hover:bg-cream text-cocoa text-xs font-bold px-4 py-2 rounded-full backdrop-blur-sm transition-transform active:scale-95">
 Ganti Sampul
 </button>
 </div>
 <input type="file" x-ref="coverInput" name="cover_photo" class="hidden" accept="image/*" @change="handleFileSelect($event, 'cover')">
 <x-form-error class="mt-2" :messages="$errors->get('cover_photo')" />
 </div>

 <!-- Avatar Container -->
 <div class="absolute -bottom-10 left-8">
 <div class="w-32 h-32 rounded-full border-4 border-white bg-cream overflow-hidden relative group shadow-xl">
 <img :src="avatarUrl" class="w-full h-full object-cover" x-show="avatarUrl">
 
 <!-- Avatar Change Overlay -->
 <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-1 cursor-pointer" @click="$refs.avatarInput.click()">
 <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
 <span class="text-[10px] text-white font-bold uppercase tracking-tighter">Ubah</span>
 </div>
 <input type="file" x-ref="avatarInput" name="avatar" class="hidden" accept="image/*" @change="handleFileSelect($event, 'avatar')">
 <x-form-error class="mt-2" :messages="$errors->get('avatar')" />
 </div>
 </div>
 </div>
 <div class="h-10"></div> <!-- Spacer for avatar offset -->
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-6">
 <!-- Name -->
 <div class="space-y-2">
 <x-form-label for="name" value="Nama Lengkap" class="text-xs uppercase tracking-widest text-caramel font-bold" />
 <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
 class="w-full bg-cream border border-sand rounded-xl px-4 py-3 focus:ring-2 focus:ring-brown/5 focus:border-sand transition-all text-cocoa font-medium">
 <x-form-error class="mt-1" :messages="$errors->get('name')" />
 </div>

 <!-- Username -->
 <div class="space-y-2">
 <x-form-label for="username" value="Username" class="text-xs uppercase tracking-widest text-caramel font-bold" />
 <div class="relative">
 <span class="absolute left-4 top-1/2 -translate-y-1/2 text-caramel font-medium">@</span>
 <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" required
 class="w-full bg-cream border border-sand rounded-xl pl-8 pr-4 py-3 focus:ring-2 focus:ring-brown/5 focus:border-sand transition-all text-cocoa font-medium">
 </div>
 <x-form-error class="mt-1" :messages="$errors->get('username')" />
 </div>

 <!-- Email -->
 <div class="space-y-2">
 <x-form-label for="email" value="Alamat Email" class="text-xs uppercase tracking-widest text-caramel font-bold" />
 <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
 class="w-full bg-cream border border-sand rounded-xl px-4 py-3 focus:ring-2 focus:ring-brown/5 focus:border-sand transition-all text-cocoa font-medium">
 <x-form-error class="mt-1" :messages="$errors->get('email')" />
 </div>

 <!-- Bio -->
 <div class="space-y-2 md:col-span-2">
 <x-form-label for="bio" value="Bio Singkat" class="text-xs uppercase tracking-widest text-caramel font-bold" />
 <textarea id="bio" name="bio" rows="4"
 class="w-full bg-cream border border-sand rounded-xl px-4 py-3 focus:ring-2 focus:ring-brown/5 focus:border-sand transition-all text-cocoa font-medium resize-none"
 placeholder="Ceritakan sedikit tentang siapa Anda...">{{ old('bio', $user->bio) }}</textarea>
 <x-form-error class="mt-1" :messages="$errors->get('bio')" />
 <p class="text-[10px] text-caramel">Bio akan ditampilkan di halaman profil publik Anda.</p>
 </div>
 </div>

 <div class="flex items-center justify-between pt-6 border-t border-sand">
 <p x-show="status === 'profile-updated'" 
 x-transition:enter="transition ease-out duration-300"
 x-transition:enter-start="opacity-0 translate-x-4"
 x-transition:enter-end="opacity-100 translate-x-0"
 class="text-sm font-bold text-green-600">
 Tersimpan!
 </p>
 <div class="flex-1"></div>
 <button type="submit" class="btn-primary px-10 shadow-lg shadow-warm">
 Simpan Profil
 </button>
 </div>
 </form>

 <script>
 function profileEdit() {
 return {
 avatarUrl: '{{ $user->avatar_url }}',
 coverUrl: '{{ $user->cover_photo_url }}',
 status: '{{ session('status') }}',

 handleFileSelect(event, type) {
 const file = event.target.files[0];
 if (file) {
 const reader = new FileReader();
 reader.onload = (e) => {
 if (type === 'avatar') this.avatarUrl = e.target.result;
 if (type === 'cover') this.coverUrl = e.target.result;
 };
 reader.readAsDataURL(file);
 }
 }
 }
 }
 </script>
</section>
