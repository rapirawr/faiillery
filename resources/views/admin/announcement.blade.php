@extends('layouts.admin')

@section('page-title', 'Announcements')
@section('page-subtitle', 'Siaran pesan ke seluruh pengguna')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Left: Form + History -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Compose Card -->
        <div class="admin-card rounded-2xl p-6">
            <h3 class="font-bold text-sm mb-5" style="color:#3B2417;">Buat Pengumuman Baru</h3>
            <form action="{{ route('admin.announce.send') }}" method="POST">
                @csrf
                <!-- Message -->
                <div class="mb-4">
                    <label class="block text-xs font-bold mb-2" style="color:#8B5E3C;text-transform:uppercase;letter-spacing:0.07em;">Pesan</label>
                    <textarea
                        name="message"
                        rows="4"
                        class="admin-input"
                        style="resize:none;"
                        placeholder="Tulis pesan pengumuman di sini...">{{ $current ? $current->message : '' }}</textarea>
                </div>

                <!-- Duration -->
                <div class="mb-6">
                    <label class="block text-xs font-bold mb-2" style="color:#8B5E3C;text-transform:uppercase;letter-spacing:0.07em;">Durasi</label>
                    <select name="duration" class="admin-select">
                        <option value="1h">1 Jam</option>
                        <option value="1d">1 Hari</option>
                        <option value="1w">1 Minggu</option>
                        <option value="permanent" selected>Permanen</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button type="submit" name="action" value="send" class="btn-primary flex-1 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                        Broadcast
                    </button>
                    @if($current)
                    <button type="submit" name="action" value="clear"
                        @click.prevent="window.appConfirm('Nonaktifkan', 'Nonaktifkan semua pengumuman aktif?', () => { document.querySelector('button[value=clear]').type='submit'; $el.closest('form').submit(); })"
                        class="btn-ghost px-5">
                        Nonaktifkan
                    </button>
                    @endif
                </div>
            </form>
        </div>

        <!-- History Table -->
        <div class="admin-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4" style="border-bottom:1px solid #F5E6CE;">
                <h3 class="font-bold text-sm" style="color:#3B2417;">Riwayat Pengumuman</h3>
            </div>
            <table class="w-full admin-table">
                <thead>
                    <tr>
                        <th>Pesan</th>
                        <th>Status</th>
                        <th>Berakhir</th>
                        <th class="text-right">Hapus</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $item)
                    <tr>
                        <td>
                            <p class="text-sm font-normal truncate" style="max-width:280px;color:#374151;">{{ $item->message }}</p>
                            <span class="text-xs" style="color:#C69C6D;">{{ $item->created_at->format('d M Y, H:i') }}</span>
                        </td>
                        <td>
                            @if($item->is_active && (!$item->ends_at || $item->ends_at->isFuture()))
                                <span class="badge badge-live flex items-center gap-1.5 w-fit">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white" style="animation:pulse 1.5s infinite;"></span>
                                    Live
                                </span>
                            @else
                                <span class="badge badge-ended">Berakhir</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-xs" style="color:#C69C6D;">{{ $item->ends_at ? $item->ends_at->diffForHumans() : 'Permanen' }}</span>
                        </td>
                        <td class="text-right">
                            <form action="{{ route('admin.announce.delete', $item) }}" method="POST"
                                @submit.prevent="window.appConfirm('Hapus Riwayat', 'Hapus entri riwayat ini?', () => $el.submit(), 'Hapus')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn mx-auto" title="Hapus riwayat">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-12">
                            <p class="text-sm" style="color:#C69C6D;">Belum ada riwayat pengumuman.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($history->hasPages())
            <div class="px-6 py-4" style="border-top:1px solid #F5E6CE;">
                {{ $history->links() }}
            </div>
            @endif
        </div>

    </div>

    <!-- Right: Sidebar Info -->
    <div class="space-y-5">

        <!-- Live Preview -->
        <div class="admin-card rounded-2xl p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#FAF3E8;">
                    <svg class="w-3.5 h-3.5" style="color:#8B5E3C;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                </div>
                <h4 class="text-xs font-bold uppercase tracking-widest" style="color:#8B5E3C;">Siaran Aktif</h4>
            </div>

            @if($current)
                <div class="p-4 rounded-xl mb-3" style="background:#FAF3E8;border:1px solid #F5E6CE;">
                    <p class="text-sm leading-relaxed" style="color:#3B2417;">{{ $current->message }}</p>
                </div>
                <div class="flex items-center gap-2 text-xs font-medium" style="color:#8B5E3C;">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500" style="animation:pulse 1.5s infinite;display:inline-block;"></span>
                    Berakhir: {{ $current->ends_at ? $current->ends_at->diffForHumans() : 'Permanen' }}
                </div>
            @else
                <div class="flex flex-col items-center py-6 gap-2">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#FFF8ED;">
                        <svg class="w-5 h-5" style="color:#E3C79A;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728M8.464 15.536a5 5 0 010-7.072m7.072 0a5 5 0 010 7.072"></path></svg>
                    </div>
                    <p class="text-xs text-center" style="color:#C69C6D;">Tidak ada siaran aktif</p>
                </div>
            @endif
        </div>

        <!-- Info Box -->
        <div class="glass-panel p-5">
            <h4 class="text-xs font-bold uppercase tracking-widest mb-3" style="color:#8B5E3C;">Catatan</h4>
            <ul class="space-y-2">
                <li class="flex items-start gap-2 text-xs" style="color:#8B5E3C;">
                    <span class="w-1.5 h-1.5 rounded-full mt-1.5 flex-shrink-0" style="background:#8B5E3C;"></span>
                    Pengumuman baru otomatis menonaktifkan yang sebelumnya.
                </li>
                <li class="flex items-start gap-2 text-xs" style="color:#8B5E3C;">
                    <span class="w-1.5 h-1.5 rounded-full mt-1.5 flex-shrink-0" style="background:#8B5E3C;"></span>
                    Siaran permanen aktif sampai dinonaktifkan secara manual.
                </li>
                <li class="flex items-start gap-2 text-xs" style="color:#8B5E3C;">
                    <span class="w-1.5 h-1.5 rounded-full mt-1.5 flex-shrink-0" style="background:#8B5E3C;"></span>
                    Pesan tampil di seluruh halaman untuk semua user.
                </li>
            </ul>
        </div>

    </div>
</div>
@endsection
