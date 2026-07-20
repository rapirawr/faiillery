{{-- =====================================================================
     PARTIAL: photos/partials/photo-comments.blade.php
     Comments list with preview + "see all" + realtime
     ===================================================================== --}}
<div class="px-3 pb-2"
     x-data="{
         comments: {{ $photo->comments->map(fn($c) => [
             'id'         => $c->id,
             'body'       => $c->body,
             'created_at' => $c->created_at->diffForHumans(),
             'user'       => [
                 'name'       => $c->user->name,
                 'username'   => $c->user->username,
                 'avatar_url' => $c->user->avatar_url,
                 'is_verified'=> $c->user->is_verified ?? false,
             ]
         ])->toJson() }},
         showAll: false,
         submitting: false,
         newComment: '',

         init() {
             /* Listen for new comments from local inputs */
             window.addEventListener('new-comment-added', (e) => {
                 this.comments.unshift(e.detail);
             });

             /* realtime new comments via Supabase */
             if (window.supabase) {
                 window.supabase
                     .channel('public:comments:{{ $photo->id }}')
                     .on('postgres_changes', {
                         event: 'INSERT',
                         schema: 'public',
                         table: 'comments',
                         filter: 'photo_id=eq.{{ $photo->id }}'
                     }, payload => {
                         if (!this.comments.find(c => c.id === payload.new.id)) {
                             /* lightweight: just mark as new – full fetch not needed */
                         }
                     })
                     .subscribe();
             }
         },

         postComment() {
             if (!this.newComment.trim()) return;
             this.submitting = true;
             axios.post('{{ route('comments.store', $photo) }}', { body: this.newComment })
                 .then(res => {
                     this.comments.unshift({
                         id: res.data.comment.id,
                         body: res.data.comment.body,
                         created_at: 'Baru saja',
                         user: res.data.user
                     });
                     this.newComment = '';
                     window.showToast('Komentar terkirim!');
                 })
                 .catch(err => window.showToast(err.response?.data?.message || 'Gagal mengirim komentar', 'error'))
                 .finally(() => this.submitting = false);
         },

         deleteComment(id) {
             window.appConfirm('Hapus Komentar', 'Hapus komentar ini?', () => {
                 axios.delete('/comments/' + id)
                     .then(() => {
                         this.comments = this.comments.filter(c => c.id !== id);
                         window.showToast('Komentar dihapus!');
                     })
                     .catch(err => window.showToast(err.response?.data?.message || 'Gagal menghapus', 'error'));
             }, 'Hapus');
         }
     }">

  <div class="bg-white/25 dark:bg-neutral-900/40 backdrop-blur-xl backdrop-saturate-150 border border-white/40 dark:border-white/10 shadow-lg shadow-black/10 rounded-2xl">
    {{-- Preview: show 2 or all --}}
    <div class="px-4 py-3 space-y-3">

        {{-- "Lihat semua" link --}}
        <template x-if="comments.length > 2 && !showAll">
            <button @click="showAll = true"
                    class="text-[13px] text-gray-400 dark:text-gray-500 hover:text-gray-600 transition-colors font-medium">
                Lihat semua <span x-text="comments.length"></span> komentar
            </button>
        </template>

        <template x-for="(comment, idx) in (showAll ? comments : comments.slice(0, 2))" :key="comment.id">
            <div class="flex gap-3 group">
                <a :href="'/user/' + comment.user.username" class="shrink-0">
                    <img :src="comment.user.avatar_url"
                         class="w-7 h-7 rounded-full object-cover ring-1 ring-gray-200 dark:ring-white/10">
                </a>
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] text-dark dark:text-white leading-snug">
                        <a :href="'/user/' + comment.user.username" class="font-semibold hover:underline mr-1" x-text="comment.user.name"></a>
                        <span x-text="comment.body"></span>
                    </p>
                    <div class="flex items-center gap-3 mt-1">
                        <span class="text-[11px] text-gray-400" x-text="comment.created_at"></span>
                        @auth
                        <template x-if="comment.user.username === '{{ auth()->user()->username }}'">
                            <button @click="deleteComment(comment.id)"
                                    class="text-[11px] text-red-400 opacity-0 group-hover:opacity-100 transition-opacity hover:text-red-600">Hapus</button>
                        </template>
                        @endauth
                    </div>
                </div>
            </div>
        </template>

        <template x-if="comments.length === 0">
            <p class="text-[13px] text-gray-400 dark:text-gray-500 text-center py-2">Belum ada komentar. Jadilah yang pertama!</p>
        </template>
    </div>

    {{-- ── Comment input, inside the same card ── --}}
    <div class="px-4 py-3 border-t border-gray-100 dark:border-white/10">
        @auth
            <div class="flex items-center gap-3">
                <img src="{{ auth()->user()->avatar_url }}"
                     class="w-8 h-8 rounded-full object-cover shrink-0 ring-1 ring-gray-200 dark:ring-white/10">

                <div class="flex-1 relative flex items-center">
                    <input id="comment-input-field"
                           type="text"
                           x-model="newComment"
                           @keydown.enter.prevent="postComment()"
                           placeholder="Tambahkan komentar..."
                           class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-full py-2 pl-4 pr-16 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-dark dark:text-white placeholder-gray-400">

                    <button @click="postComment()"
                            :disabled="submitting || !newComment.trim()"
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
</div>