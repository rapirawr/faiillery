@extends('layouts.admin')

@section('page-title', 'SQL Console')
@section('page-subtitle', 'Akses langsung ke database — gunakan dengan hati-hati')

@section('content')

<div class="grid grid-cols-1 gap-5"
    x-data="{
        query: '',
        results: null,
        loading: false,
        error: null,
        history: [],

        execute() {
            if (!this.query.trim() || this.loading) return;
            const isDestructive = /\b(delete|drop|truncate|update)\b/i.test(this.query);
            if (isDestructive) {
                window.appConfirm('Peringatan SQL', 'Query ini berpotensi mengubah atau menghapus data secara permanen. Lanjutkan?', () => this.runQuery(), 'Jalankan');
                return;
            }
            this.runQuery();
        },

        runQuery() {
            this.loading = true;
            this.error = null;
            this.results = null;
            axios.post('{{ route('admin.sql.execute') }}', { query: this.query }, {
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(res => {
                this.results = res.data;
                if (!this.history.includes(this.query)) {
                    this.history.unshift(this.query);
                    if (this.history.length > 10) this.history.pop();
                }
            })
            .catch(err => {
                this.error = err.response?.data?.message || 'Unknown error occurred';
            })
            .finally(() => { this.loading = false; });
        },

        setQuery(q) {
            this.query = q;
            this.$nextTick(() => this.$refs.textarea.focus());
        }
    }">

    <!-- Terminal Card -->
    <div class="rounded-2xl overflow-hidden" style="box-shadow:0 4px 24px rgba(0,0,0,0.1);">
        <!-- Terminal bar -->
        <div class="flex items-center justify-between px-5 py-3" style="background:#3B2417;">
            <div class="flex items-center gap-2">
                <div class="flex gap-1.5">
                    <div class="w-3 h-3 rounded-full" style="background:#ff5f56;"></div>
                    <div class="w-3 h-3 rounded-full" style="background:#ffbd2e;"></div>
                    <div class="w-3 h-3 rounded-full" style="background:#27c93f;"></div>
                </div>
                <span class="text-xs font-bold ml-3" style="color:rgba(255,255,255,0.3);letter-spacing:0.1em;text-transform:uppercase;font-family:monospace;">PostgreSQL Console</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full" style="background:#27c93f;animation:pulse 2s infinite;"></span>
                <span class="text-xs" style="color:rgba(255,255,255,0.3);font-family:monospace;">connected</span>
            </div>
        </div>

        <!-- Editor area -->
        <div class="p-6" style="background:#111827;">
            <div class="flex gap-3">
                <span class="font-mono text-base font-bold pt-1 select-none flex-shrink-0" style="color:#8B5E3C;">failerry=></span>
                <textarea
                    x-ref="textarea"
                    x-model="query"
                    @keydown.ctrl.enter="execute()"
                    class="flex-1 border-none outline-none font-mono text-sm resize-none"
                    style="background:transparent;color:#e2e8f0;min-height:130px;line-height:1.7;caret-color:#8B5E3C;"
                    placeholder="-- Enter your SQL query here... (Ctrl + Enter to run)"
                    spellcheck="false"
                ></textarea>
            </div>

            <!-- Toolbar -->
            <div class="flex items-center justify-between pt-4 mt-4" style="border-top:1px solid rgba(255,255,255,0.06);">
                <div class="flex items-center gap-1">
                    <button @click="setQuery('SELECT * FROM users LIMIT 10')"
                        class="text-xs font-mono px-3 py-1.5 rounded-lg transition-all"
                        style="color:rgba(255,255,255,0.35);background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);"
                        onmouseover="this.style.color='rgba(255,255,255,0.7)';this.style.background='rgba(255,255,255,0.08)'"
                        onmouseout="this.style.color='rgba(255,255,255,0.35)';this.style.background='rgba(255,255,255,0.04)'">
                        Users
                    </button>
                    <button @click="setQuery('SELECT * FROM photos ORDER BY created_at DESC LIMIT 10')"
                        class="text-xs font-mono px-3 py-1.5 rounded-lg transition-all"
                        style="color:rgba(255,255,255,0.35);background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);"
                        onmouseover="this.style.color='rgba(255,255,255,0.7)';this.style.background='rgba(255,255,255,0.08)'"
                        onmouseout="this.style.color='rgba(255,255,255,0.35)';this.style.background='rgba(255,255,255,0.04)'">
                        Latest Photos
                    </button>
                    <button @click="query = ''"
                        class="text-xs font-mono px-3 py-1.5 rounded-lg transition-all"
                        style="color:rgba(255,255,255,0.25);background:transparent;border:1px solid transparent;"
                        onmouseover="this.style.color='rgba(255,100,100,0.8)'"
                        onmouseout="this.style.color='rgba(255,255,255,0.25)'">
                        Clear
                    </button>
                </div>

                <button
                    @click="execute()"
                    :disabled="loading || !query.trim()"
                    class="flex items-center gap-2 px-5 py-2 rounded-xl text-xs font-bold transition-all active:scale-95 disabled:opacity-40 disabled:pointer-events-none"
                    style="background:#8B5E3C;color:#fff;">
                    <template x-if="loading">
                        <svg class="animate-spin w-3.5 h-3.5" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </template>
                    <template x-if="!loading">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </template>
                    <span x-text="loading ? 'Running...' : 'Run  (Ctrl+Enter)'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Error Output -->
    <template x-if="error">
        <div class="rounded-2xl p-5 font-mono text-sm" style="background:#fff5f5;border:1px solid #F5E6CE;">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-4 h-4 flex-shrink-0" style="color:#8B5E3C;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-xs font-bold uppercase tracking-widest" style="color:#8B5E3C;">Error</span>
            </div>
            <pre x-text="error" class="whitespace-pre-wrap text-xs" style="color:#991b1b;"></pre>
        </div>
    </template>

    <!-- Results Output -->
    <template x-if="results">
        <div>
            <!-- Select results: table -->
            <template x-if="results.type === 'select'">
                <div class="admin-card rounded-2xl overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-3.5" style="border-bottom:1px solid #F5E6CE;">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full" style="background:#22c55e;"></span>
                            <span class="text-xs font-bold" style="color:#8B5E3C;">
                                Result — <span x-text="results.count"></span> row(s)
                            </span>
                        </div>
                    </div>
                    <div class="overflow-x-auto" style="max-height:460px;">
                        <table class="w-full text-left border-collapse text-xs font-mono">
                            <thead style="position:sticky;top:0;z-index:1;">
                                <tr style="background:#FAF3E8;">
                                    <template x-for="column in results.columns">
                                        <th class="px-5 py-3 font-bold whitespace-nowrap" style="color:#8B5E3C;border-bottom:1px solid #E3C79A;" x-text="column"></th>
                                    </template>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, index) in results.rows" :key="index">
                                    <tr style="border-bottom:1px solid #FAF3E8;" onmouseover="this.style.background='#FDF5E8'" onmouseout="this.style.background=''">
                                        <template x-for="column in results.columns">
                                            <td class="px-5 py-3 whitespace-nowrap" style="color:#374151;max-width:220px;overflow:hidden;text-overflow:ellipsis;"
                                                :title="row[column]"
                                                x-text="row[column] === null ? 'NULL' : String(row[column])">
                                            </td>
                                        </template>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>

            <!-- Statement/empty result -->
            <template x-if="results.type === 'statement' || results.type === 'empty'">
                <div class="rounded-2xl p-5 flex items-center gap-3" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                    <svg class="w-5 h-5 flex-shrink-0" style="color:#22c55e;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    <span class="text-sm font-medium font-mono" style="color:#15803d;" x-text="results.message || 'Query executed successfully'"></span>
                </div>
            </template>
        </div>
    </template>

    <!-- Query history -->
    <template x-if="history.length > 0">
        <div class="admin-card rounded-2xl overflow-hidden">
            <div class="px-6 py-3.5" style="border-bottom:1px solid #F5E6CE;">
                <span class="text-xs font-bold uppercase tracking-widest" style="color:#C69C6D;">Riwayat Query</span>
            </div>
            <div class="divide-y" style="border-color:#FAF3E8;">
                <template x-for="(q, i) in history" :key="i">
                    <button @click="setQuery(q)"
                        class="w-full text-left px-6 py-3 font-mono text-xs transition-colors"
                        style="color:#8B5E3C;"
                        onmouseover="this.style.background='#FDF5E8';this.style.color='#3B2417'"
                        onmouseout="this.style.background='';this.style.color='#8B5E3C'"
                        x-text="q">
                    </button>
                </template>
            </div>
        </div>
    </template>
</div>

<style>
    textarea::-webkit-scrollbar { width: 6px; }
    textarea::-webkit-scrollbar-track { background: transparent; }
    textarea::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }
    textarea::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
</style>
@endsection
