{{-- =====================================================================
     PARTIAL: photos/partials/photo-comment-input.blade.php
     Clean comment input bar
     ===================================================================== --}}
<div class="px-4 pb-3 pt-1"
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
    @auth
        <div class="flex items-center gap-2.5">
            <img src="{{ auth()->user()->avatar_url }}"
                 class="w-7 h-7 rounded-full object-cover shrink-0 ring-1 ring-sand">

            <div class="flex-1 relative flex items-center">
                <input id="comment-input-field"
                       type="text"
                       x-model="localNewComment"
                       @keydown.enter.prevent="submitComment()"
                       placeholder="Tambahkan komentar..."
                       class="w-full bg-[#FFF8ED] dark:bg-white/5 border border-sand dark:border-white/10 rounded-full py-2 pl-4 pr-20 text-xs text-cocoa dark:text-white placeholder-caramel/70 focus:outline-none focus:ring-2 focus:ring-brown focus:border-transparent">

                <button @click="submitComment()"
                        :disabled="localSubmitting || !localNewComment.trim()"
                        class="absolute right-1 px-3 py-1 bg-brown hover:bg-espresso text-white text-[11px] font-bold rounded-full transition-all active:scale-95 disabled:opacity-40 disabled:pointer-events-none">
                    Kirim
                </button>
            </div>
        </div>
    @endauth
    @guest
        <div class="py-1 text-center bg-sand/10 rounded-2xl p-2 border border-sand/20">
            <p class="text-xs text-cocoa/80 dark:text-gray-400 mb-1.5">Ingin berdiskusi? Masuk untuk menulis komentar.</p>
            <a href="{{ route('login') }}" class="px-4 py-1.5 bg-brown hover:bg-espresso text-white rounded-full text-xs font-bold inline-block transition-colors shadow-sm">Masuk</a>
        </div>
    @endguest
</div>