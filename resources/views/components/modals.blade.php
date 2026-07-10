<div x-data="appModals"
 @app-confirm.window="openConfirm($event.detail)"
 @app-prompt.window="openPrompt($event.detail)"
 @keydown.escape.window="closeModal()">

 <!-- Confirm Modal -->
 <div x-show="confirmData.show" style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
 <div x-show="confirmData.show" x-transition.opacity class="absolute inset-0 backdrop-blur-sm" style="background:rgba(59,36,23,0.4);" @click="closeModal()"></div>
 <div x-show="confirmData.show"
 x-transition:enter="transition ease-out duration-250"
 x-transition:enter-start="opacity-0 scale-95"
 x-transition:enter-end="opacity-100 scale-100"
 x-transition:leave="transition ease-in duration-150"
 x-transition:leave-start="opacity-100 scale-100"
 x-transition:leave-end="opacity-0 scale-95"
 class="relative rounded-2xl shadow-warm p-6 w-[90%] max-w-sm mx-auto z-10 text-center"
 style="background:#FFF8ED;border:1px solid #E3C79A;">

 <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4"
 :class="(confirmData.type === 'primary' || confirmData.type === 'success') ? '' : ''"
 style="background:#F5E6CE;">
 <template x-if="confirmData.type === 'primary' || confirmData.type === 'alert'">
 <svg class="w-7 h-7" fill="none" stroke="#8B5E3C" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
 </template>
 <template x-if="confirmData.type === 'success'">
 <svg class="w-7 h-7" fill="none" stroke="#8B5E3C" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
 </template>
 <template x-if="confirmData.type !== 'primary' && confirmData.type !== 'alert' && confirmData.type !== 'success'">
 <svg class="w-7 h-7" fill="none" stroke="#8B5E3C" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
 </template>
 </div>

 <h3 class="text-lg font-bold mb-2" style="color:#3B2417;" x-text="confirmData.title"></h3>
 <p class="text-sm mb-6 leading-relaxed" style="color:#8B5E3C;" x-text="confirmData.message"></p>

 <div class="flex gap-3">
 <template x-if="confirmData.type !== 'alert'">
 <button @click="closeModal()" class="flex-1 py-2.5 px-4 rounded-xl font-semibold text-sm transition-all" style="background:#F5E6CE;color:#5C3A21;border:1px solid #E3C79A;" onmouseover="this.style.background='#EEDFC0'" onmouseout="this.style.background='#F5E6CE'">
 Batal
 </button>
 </template>
 <button @click="confirmAction()" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-sm transition-all shadow-warm" style="background:#8B5E3C;color:#FFF8ED;" onmouseover="this.style.background='#5C3A21'" onmouseout="this.style.background='#8B5E3C'">
 <span x-text="confirmData.confirmText || 'Ya, Lanjutkan'"></span>
 </button>
 </div>
 </div>
 </div>

 <!-- Prompt Modal -->
 <div x-show="promptData.show" style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
 <div x-show="promptData.show" x-transition.opacity class="absolute inset-0 backdrop-blur-sm" style="background:rgba(59,36,23,0.4);" @click="closeModal()"></div>
 <div x-show="promptData.show"
 x-transition:enter="transition ease-out duration-250"
 x-transition:enter-start="opacity-0 scale-95"
 x-transition:enter-end="opacity-100 scale-100"
 x-transition:leave="transition ease-in duration-150"
 x-transition:leave-start="opacity-100 scale-100"
 x-transition:leave-end="opacity-0 scale-95"
 class="relative rounded-2xl shadow-warm p-6 w-[90%] max-w-sm mx-auto z-10 text-center"
 style="background:#FFF8ED;border:1px solid #E3C79A;">

 <h3 class="text-lg font-bold mb-2" style="color:#3B2417;" x-text="promptData.title"></h3>
 <p class="text-sm mb-4 leading-relaxed" style="color:#8B5E3C;" x-text="promptData.message"></p>

 <input type="text" x-model="promptData.input" x-ref="promptInput"
 @keydown.enter="promptAction()"
 class="w-full mb-5 rounded-xl px-4 py-3 text-sm outline-none transition-all"
 style="background:#FFF8ED;border:1px solid #E3C79A;color:#3B2417;"
 :placeholder="promptData.placeholder">

 <div class="flex gap-3">
 <button @click="closeModal()" class="flex-1 py-2.5 px-4 rounded-xl font-semibold text-sm transition-all" style="background:#F5E6CE;color:#5C3A21;border:1px solid #E3C79A;" onmouseover="this.style.background='#EEDFC0'" onmouseout="this.style.background='#F5E6CE'">
 Batal
 </button>
 <button @click="promptAction()" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-sm shadow-warm transition-all" style="background:#8B5E3C;color:#FFF8ED;" onmouseover="this.style.background='#5C3A21'" onmouseout="this.style.background='#8B5E3C'">
 <span x-text="promptData.confirmText || 'Simpan'"></span>
 </button>
 </div>
 </div>
 </div>
</div>
