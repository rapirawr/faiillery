{{-- =====================================================================
     PARTIAL: photos/partials/photo-comments.blade.php
     Comments list with preview + "see all" + realtime
     ===================================================================== --}}
<div x-data="{
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

         init() {
             /* Listen for new comments from local inputs */
             window.addEventListener('new-comment-added', (e) => {
                 this.comments.unshift(e.detail);
             });

             /* Realtime new comments via Supabase */
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
                             /* Payload handling if needed */
                         }
                     })
                     .subscribe();
             }
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

    <div class="space-y-3">
        <h3 class="text-xs font-bold uppercase tracking-wider text-caramel dark:text-gray-400 mb-2">Komentar</h3>

        {{-- Toggle see all --}}
        <template x-if="comments.length > 3 && !showAll">
            <button @click="showAll = true"
                    class="text-xs text-brown dark:text-caramel font-semibold hover:underline transition-colors block mb-3">
                Lihat semua <span x-text="comments.length"></span> komentar
            </button>
        </template>

        {{-- List --}}
        <div class="space-y-3">
            <template x-for="(comment, idx) in (showAll ? comments : comments.slice(0, 3))" :key="comment.id">
                <div class="flex gap-2.5 group items-start">
                    <a :href="'/user/' + comment.user.username" class="shrink-0 pt-0.5">
                        <img :src="comment.user.avatar_url"
                             class="w-7 h-7 rounded-full object-cover ring-1 ring-sand">
                    </a>
                    <div class="flex-1 min-w-0 bg-white/70 dark:bg-white/5 rounded-2xl p-2.5 border border-sand/20">
                        <div class="flex items-center justify-between gap-2">
                            <a :href="'/user/' + comment.user.username" class="font-bold text-xs text-cocoa dark:text-white hover:underline truncate" x-text="comment.user.name"></a>
                            <span class="text-[10px] text-caramel dark:text-gray-400 shrink-0" x-text="comment.created_at"></span>
                        </div>
                        <p class="text-xs text-cocoa/90 dark:text-gray-200 mt-1 leading-relaxed break-words" x-text="comment.body"></p>

                        @auth
                        <template x-if="comment.user.username === '{{ auth()->user()->username }}'">
                            <div class="mt-1 text-right">
                                <button @click="deleteComment(comment.id)"
                                        class="text-[10px] text-red-500 hover:text-red-700 font-medium opacity-0 group-hover:opacity-100 transition-opacity">Hapus</button>
                            </div>
                        </template>
                        @endauth
                    </div>
                </div>
            </template>
        </div>

        {{-- Empty state --}}
        <template x-if="comments.length === 0">
            <div class="text-center py-6 px-4 bg-white/40 dark:bg-white/5 rounded-2xl border border-dashed border-sand/50">
                <svg class="w-8 h-8 mx-auto text-caramel/60 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p class="text-xs font-medium text-cocoa/70 dark:text-gray-400">Belum ada komentar.</p>
                <p class="text-[11px] text-caramel dark:text-gray-500 mt-0.5">Jadilah yang pertama memberikan respon!</p>
            </div>
        </template>
    </div>
</div>