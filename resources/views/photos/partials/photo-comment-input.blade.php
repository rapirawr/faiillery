{{-- =====================================================================
     PARTIAL: photos/partials/photo-comment-input.blade.php
     Floating comment input bar
     ===================================================================== --}}
<div class="px-3 py-2"
     x-data="{
         localSubmitting: false,
         localNewComment: '',
         submitComment() {
             if (!this.localNewComment.trim()) return;
             this.localSubmitting = true;
             axios.post('{{ route('comments.store', $photo) }}', { body: this.localNewComment })
                 .then(res => {
                     const commentData = {
                         id: res.data.comment.id,
                         body: res.data.comment.body,
                         created_at: 'Baru saja',
                         user: res.data.user
                     };
                     
                     window.dispatchEvent(new CustomEvent('new-comment-added', { detail: commentData }));
                     
                     this.localNewComment = '';
                     window.showToast('Komentar terkirim!');
                 })
                 .catch(err => window.showToast(err.response?.data?.message || 'Gagal mengirim komentar', 'error'))
                 .finally(() => this.localSubmitting = false);
         }
     }">
    <div class="px-4 py-2.5 bg-white/25 dark:bg-neutral-900/40 backdrop-blur-xl backdrop-saturate-150 border border-white/40 dark:border-white/10 shadow-lg shadow-black/10 rounded-3xl">
        @auth
            <div class="flex items-center gap-3">
                <img src="{{ auth()->user()->avatar_url }}" 
                     class="w-8 h-8 rounded-full object-cover shrink-0 ring-1 ring-gray-200 dark:ring-white/10">
                
                <div class="flex-1 relative flex items-center">
                    <input id="comment-input-field{{ $suffix ?? '' }}"
                           type="text" 
                           x-model="localNewComment"
                           @keydown.enter.prevent="submitComment()"
                           placeholder="Tambahkan komentar..." 
                           class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-full py-2 pl-4 pr-16 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-dark dark:text-white placeholder-gray-400">
                    
                    <button @click="submitComment()" 
                            :disabled="localSubmitting || !localNewComment.trim()"
                            class="absolute right-3 text-sm font-semibold text-blue-500 hover:text-blue-600 disabled:opacity-40 disabled:pointer-events-none transition-opacity">
                        Kirim
                    </button>
                </div>
            </div>
        @endauth
        @guest
            <div class="py-1 text-center">
                <p class="text-xs text-gray-500 mb-2">Ingin berdiskusi? Masuk untuk menulis komentar.</p>
                <a href="{{ route('login') }}" class="px-5 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-full text-xs font-bold inline-block transition-colors">Masuk</a>
            </div>
        @endguest
    </div>
</div>