@extends('layouts.admin')

@section('page-title', 'Reports')
@section('page-subtitle', 'Antrian konten yang dilaporkan')

@section('content')

<div class="admin-card rounded-2xl overflow-hidden">
    <table class="w-full admin-table">
        <thead>
            <tr>
                <th>Foto</th>
                <th>Reporter</th>
                <th>Alasan</th>
                <th>Status</th>
                <th>Waktu</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
            <tr>
                <!-- Photo -->
                <td>
                    <div class="flex items-center gap-3">
                        @if($report->photo)
                            <img src="{{ $report->photo->thumbnail_url }}" class="w-10 h-10 rounded-lg object-cover flex-shrink-0" style="border:1px solid #E3C79A;">
                            <div class="min-w-0">
                                <div class="font-semibold text-sm truncate" style="color:#3B2417;max-width:160px;">{{ $report->photo->title }}</div>
                                <div class="text-xs" style="color:#C69C6D;">by {{ $report->photo->user->name }}</div>
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#F5E6CE;">
                                <svg class="w-4 h-4" style="color:#E3C79A;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </div>
                            <span class="text-xs" style="color:#C69C6D;">[Foto dihapus]</span>
                        @endif
                    </div>
                </td>

                <!-- Reporter -->
                <td>
                    <div class="flex items-center gap-2">
                        <img src="{{ $report->user->avatar_url }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0" style="border:1px solid #E3C79A;">
                        <span class="text-sm font-medium" style="color:#3B2417;">{{ $report->user->name }}</span>
                    </div>
                </td>

                <!-- Reason -->
                <td>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold" style="background:#FFF8ED;color:#374151;border:1px solid #E3C79A;">
                        {{ $report->reason }}
                    </span>
                </td>

                <!-- Status -->
                <td>
                    @if($report->status === 'pending')
                        <span class="badge badge-pending">● Pending</span>
                    @elseif($report->status === 'resolved')
                        <span class="badge badge-resolved">✓ Resolved</span>
                    @else
                        <span class="badge badge-dismissed">Dismissed</span>
                    @endif
                </td>

                <!-- Time -->
                <td>
                    <span class="text-xs" style="color:#C69C6D;">{{ $report->created_at->diffForHumans() }}</span>
                </td>

                <!-- Actions -->
                <td class="text-right">
                    <div class="inline-flex items-center gap-2">
                        @if($report->status === 'pending')
                            <form id="resolve-report-{{ $report->id }}" action="{{ route('admin.reports.resolve', $report) }}" method="POST" class="inline"
                                @submit.prevent="window.appConfirm('Resolve Report', 'Tandai laporan ini sebagai resolved?', () => $el.submit(), 'Resolve', 'primary')">
                                @csrf
                                <input type="hidden" name="status" value="resolved">
                                <button type="submit" class="text-xs font-semibold px-3 py-1.5 rounded-lg transition-all" style="background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;" onmouseover="this.style.background='#dcfce7'" onmouseout="this.style.background='#f0fdf4'">
                                    Resolve
                                </button>
                            </form>
                            <form id="dismiss-report-{{ $report->id }}" action="{{ route('admin.reports.resolve', $report) }}" method="POST" class="inline"
                                @submit.prevent="window.appConfirm('Dismiss Report', 'Abaikan laporan ini?', () => document.getElementById('dismiss-report-{{ $report->id }}').submit(), 'Dismiss')">
                                @csrf
                                <input type="hidden" name="status" value="dismissed">
                                <button type="submit" class="btn-ghost text-xs py-1.5">
                                    Dismiss
                                </button>
                            </form>
                        @endif

                        @if($report->photo)
                            <a href="{{ route('photos.show', $report->photo->uid) }}" target="_blank"
                               class="icon-btn" title="Lihat foto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-16">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:#FFF8ED;">
                            <svg class="w-6 h-6" style="color:#E3C79A;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-sm font-medium" style="color:#C69C6D;">Tidak ada laporan ditemukan</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $reports->links() }}
</div>
@endsection
