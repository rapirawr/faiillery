@extends('layouts.admin')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan aktivitas platform')

@section('content')

<!-- Stats Grid — Row 1 -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-5">
    <div class="admin-card stat-card-accent p-6 rounded-2xl" style="box-shadow:0 4px 20px rgba(139,94,60,0.25);">
        <div class="flex items-center justify-between mb-4">
            <div class="text-xs font-bold uppercase tracking-widest" style="color:rgba(255,248,237,0.75);">Total Users</div>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:rgba(255,248,237,0.15);">
                <svg class="w-4 h-4" style="color:#FFF8ED;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
        </div>
        <div class="text-3xl font-bold" style="color:#FFF8ED;">{{ number_format($stats['users_count']) }}</div>
        <div class="text-xs mt-2" style="color:rgba(255,248,237,0.6);">Registered accounts</div>
    </div>

    <div class="admin-card p-6 rounded-2xl">
        <div class="flex items-center justify-between mb-4">
            <div class="text-xs font-bold uppercase tracking-widest" style="color:#C69C6D;">Total Photos</div>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:#F5E6CE;">
                <svg class="w-4 h-4" style="color:#8B5E3C;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h14a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
        </div>
        <div class="text-3xl font-bold" style="color:#3B2417;">{{ number_format($stats['photos_count']) }}</div>
        <div class="text-xs mt-2" style="color:#C69C6D;">Uploaded content</div>
    </div>

    <div class="admin-card p-6 rounded-2xl">
        <div class="flex items-center justify-between mb-4">
            <div class="text-xs font-bold uppercase tracking-widest" style="color:#C69C6D;">Total Boards</div>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:#F5E6CE;">
                <svg class="w-4 h-4" style="color:#8B5E3C;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>
            </div>
        </div>
        <div class="text-3xl font-bold" style="color:#3B2417;">{{ number_format($stats['boards_count']) }}</div>
        <div class="text-xs mt-2" style="color:#C69C6D;">Collections created</div>
    </div>

    <div class="admin-card p-6 rounded-2xl">
        <div class="flex items-center justify-between mb-4">
            <div class="text-xs font-bold uppercase tracking-widest" style="color:#C69C6D;">Comments</div>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:#F5E6CE;">
                <svg class="w-4 h-4" style="color:#8B5E3C;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
            </div>
        </div>
        <div class="text-3xl font-bold" style="color:#3B2417;">{{ number_format($stats['comments_count']) }}</div>
        <div class="text-xs mt-2" style="color:#C69C6D;">User interactions</div>
    </div>
</div>

<!-- Fitur 3: Stats Row 2 — Likes, Follows, Views + Fitur 5: Online Users -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-5 mb-8">
    <!-- Likes -->
    <div class="admin-card p-5 rounded-2xl">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs font-bold uppercase tracking-widest" style="color:#C69C6D;">Total Likes</div>
            <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:#fef2f2;">
                <svg class="w-4 h-4" style="color:#e11d48;" fill="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </div>
        </div>
        <div class="text-2xl font-bold" style="color:#3B2417;">{{ number_format($stats['likes_count']) }}</div>
        <div class="text-xs mt-1" style="color:#C69C6D;">Engagement</div>
    </div>

    <!-- Follows -->
    <div class="admin-card p-5 rounded-2xl">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs font-bold uppercase tracking-widest" style="color:#C69C6D;">Follows</div>
            <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:#eff6ff;">
                <svg class="w-4 h-4" style="color:#3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
        </div>
        <div class="text-2xl font-bold" style="color:#3B2417;">{{ number_format($stats['follows_count']) }}</div>
        <div class="text-xs mt-1" style="color:#C69C6D;">Connections</div>
    </div>

    <!-- Views -->
    <div class="admin-card p-5 rounded-2xl">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs font-bold uppercase tracking-widest" style="color:#C69C6D;">Total Views</div>
            <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:#f0fdf4;">
                <svg class="w-4 h-4" style="color:#16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </div>
        </div>
        <div class="text-2xl font-bold" style="color:#3B2417;">{{ number_format($stats['views_count']) }}</div>
        <div class="text-xs mt-1" style="color:#C69C6D;">Photo impressions</div>
    </div>

    <!-- Fitur 5: Online Users Live Counter -->
    <div class="admin-card p-5 rounded-2xl"
         x-data="{ count: {{ $stats['online_count'] }}, loading: false }"
         x-init="setInterval(async () => {
             loading = true;
             try {
                 const res = await fetch('{{ route('admin.online-users') }}');
                 const data = await res.json();
                 count = data.count;
             } catch(e) {}
             loading = false;
         }, 30000)">
        <div class="flex items-center justify-between mb-3">
            <div class="text-xs font-bold uppercase tracking-widest" style="color:#C69C6D;">Online Now</div>
            <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:#f0fdf4;">
                <span class="w-3 h-3 rounded-full bg-green-500 animate-pulse inline-block"></span>
            </div>
        </div>
        <div class="text-2xl font-bold" style="color:#3B2417;" x-text="count">{{ $stats['online_count'] }}</div>
        <div class="text-xs mt-1 flex items-center gap-1" style="color:#C69C6D;">
            <span>Active last 5 min</span>
            <svg x-show="loading" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-8">
    <div class="admin-card p-6 rounded-2xl lg:col-span-3">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="font-bold text-sm" style="color:#3B2417;">Growth Trend</h3>
                <p class="text-xs mt-0.5" style="color:#C69C6D;">7 hari terakhir</p>
            </div>
            <div class="flex items-center gap-4 text-xs font-semibold">
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 rounded-full inline-block" style="background:#8B5E3C;"></span><span style="color:#8B5E3C;">Photos</span></span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 rounded-full inline-block" style="background:#E3C79A;"></span><span style="color:#C69C6D;">Users</span></span>
            </div>
        </div>
        <div style="height:220px;"><canvas id="growthChart"></canvas></div>
    </div>

    <div class="admin-card p-6 rounded-2xl lg:col-span-2">
        <div class="mb-4">
            <h3 class="font-bold text-sm" style="color:#3B2417;">Distribution</h3>
            <p class="text-xs mt-0.5" style="color:#C69C6D;">Proporsi konten</p>
        </div>
        <div style="height:220px;" class="flex items-center justify-center">
            <canvas id="distributionChart"></canvas>
        </div>
    </div>
</div>

<!-- Recent Activity Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="admin-card rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid #E3C79A;">
            <h3 class="font-bold text-sm" style="color:#3B2417;">Recent Users</h3>
            <a href="{{ route('admin.users') }}" class="text-xs font-semibold" style="color:#8B5E3C;">View all →</a>
        </div>
        <div>
            @foreach($stats['latest_users'] as $user)
            <div class="flex items-center justify-between px-6 py-3.5 transition-colors" style="border-bottom:1px solid #FAF3E8;" onmouseover="this.style.background='#FDF5E8'" onmouseout="this.style.background=''">
                <div class="flex items-center gap-3">
                    <img src="{{ $user->avatar_url }}" class="w-9 h-9 rounded-full object-cover" style="border:1px solid #E3C79A;">
                    <div>
                        <div class="text-sm font-semibold" style="color:#3B2417;">{{ $user->name }}</div>
                        <div class="text-xs" style="color:#C69C6D;">@ {{ $user->username }}</div>
                    </div>
                </div>
                <span class="text-xs" style="color:#E3C79A;">{{ $user->created_at->diffForHumans() }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="admin-card rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid #E3C79A;">
            <h3 class="font-bold text-sm" style="color:#3B2417;">Recent Photos</h3>
            <a href="{{ route('admin.photos') }}" class="text-xs font-semibold" style="color:#8B5E3C;">View all →</a>
        </div>
        <div>
            @foreach($stats['latest_photos'] as $photo)
            <div class="flex items-center gap-3 px-6 py-3.5 transition-colors" style="border-bottom:1px solid #FAF3E8;" onmouseover="this.style.background='#FDF5E8'" onmouseout="this.style.background=''">
                <img src="{{ $photo->thumbnail_url }}" class="w-10 h-10 rounded-lg object-cover flex-shrink-0" style="border:1px solid #E3C79A;">
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold truncate" style="color:#3B2417;">{{ $photo->title }}</div>
                    <div class="text-xs" style="color:#C69C6D;">by {{ $photo->user->name }}</div>
                </div>
                <span class="text-xs flex-shrink-0" style="color:#E3C79A;">{{ $photo->created_at->diffForHumans() }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Database Monitor -->
<div class="admin-card rounded-2xl overflow-hidden">
    @php
        $supabaseUrl = env('SUPABASE_URL', '');
        $supabaseBucket = env('SUPABASE_BUCKET', env('AWS_BUCKET', ''));
        $projectId = '';
        if ($supabaseUrl) {
            preg_match('/https:\/\/(.*?)\.supabase\.co/', $supabaseUrl, $matches);
            $projectId = $matches[1] ?? '';
        }
        $dbDriver = ucfirst(\Illuminate\Support\Facades\DB::getDriverName());
        $dbName   = \Illuminate\Support\Facades\DB::getDatabaseName();
    @endphp
    <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid #E3C79A;">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:#ecfdf5;">
                <svg class="w-5 h-5" style="color:#16a34a;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-sm" style="color:#3B2417;">Infrastructure</h3>
                <p class="text-xs" style="color:#C69C6D;">{{ $dbDriver }} Database · Supabase Storage</p>
            </div>
        </div>
        @if($projectId)
        <a href="https://supabase.com/dashboard/project/{{ $projectId }}/storage/buckets" target="_blank" class="btn-ghost flex items-center gap-2">
            <span>Supabase Dashboard</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
        </a>
        @endif
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3">
        <!-- DB Size -->
        <div class="px-6 py-5">
            <div class="text-xs font-bold uppercase tracking-widest mb-2" style="color:#C69C6D;">Database Size</div>
            <div class="text-2xl font-bold" style="color:#3B2417;">{{ $stats['db_size'] ?? 'N/A' }}</div>
            <div class="flex items-center gap-2 mt-3">
                <span class="status-dot green" style="animation:pulse 2s infinite;display:inline-block;"></span>
                <span class="text-xs font-medium" style="color:#16a34a;">{{ $dbDriver }} · Connected</span>
            </div>
        </div>
        <!-- Storage -->
        <div class="px-6 py-5" style="border-left:1px solid #E3C79A;">
            <div class="text-xs font-bold uppercase tracking-widest mb-2" style="color:#C69C6D;">Storage</div>
            <div class="text-2xl font-bold" style="color:#3B2417;">Supabase</div>
            @php
                function fmtBytes(int $bytes): string {
                    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
                    if ($bytes >= 1048576)    return round($bytes / 1048576, 2) . ' MB';
                    if ($bytes >= 1024)       return round($bytes / 1024, 2) . ' KB';
                    return $bytes . ' B';
                }
                $usedLabel  = fmtBytes($stats['storage_used_bytes']);
                $quotaLabel = fmtBytes($stats['storage_quota_bytes']);
                $pct        = $stats['storage_percent'];
                $barColor   = $pct >= 90 ? '#5C3A21' : ($pct >= 70 ? '#f59e0b' : '#8B5E3C');
            @endphp
            <div class="mt-3">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-semibold" style="color:#3B2417;">{{ $usedLabel }}</span>
                    <span class="text-xs" style="color:#C69C6D;">/ {{ $quotaLabel }}</span>
                </div>
                <div class="w-full rounded-full overflow-hidden" style="height:6px;background:#F5E6CE;">
                    <div class="h-full rounded-full transition-all duration-500"
                         style="width:{{ min($pct, 100) }}%;background:{{ $barColor }};"></div>
                </div>
                <div class="flex items-center justify-between mt-1.5">
                    <span class="text-xs font-medium" style="color:{{ $barColor }};">{{ $pct }}% used</span>
                    <span class="text-xs" style="color:#C69C6D;">Bucket: {{ $supabaseBucket ?: '—' }}</span>
                </div>
            </div>
        </div>
        <!-- Project -->
        <div class="px-6 py-5" style="border-left:1px solid #E3C79A;">
            <div class="text-xs font-bold uppercase tracking-widest mb-2" style="color:#C69C6D;">Project ID</div>
            <div class="text-base font-bold font-mono truncate" style="color:#3B2417;">{{ $projectId ?: '—' }}</div>
            <div class="text-xs mt-3 font-medium" style="color:#8B5E3C;">
                DB: {{ $dbName }}
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    Chart.defaults.font.family = "'Outfit', sans-serif";
    Chart.defaults.color = '#C69C6D';

    // Growth Chart
    new Chart(document.getElementById('growthChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: {!! json_encode($stats['chart']['labels']) !!},
            datasets: [
                {
                    label: 'Photos',
                    data: {!! json_encode($stats['chart']['photos']) !!},
                    borderColor: '#8B5E3C',
                    backgroundColor: 'rgba(139,94,60,0.08)',
                    fill: true, tension: 0.4, borderWidth: 2.5,
                    pointRadius: 0, pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#8B5E3C',
                },
                {
                    label: 'Users',
                    data: {!! json_encode($stats['chart']['users']) !!},
                    borderColor: '#E3C79A',
                    backgroundColor: 'rgba(227,199,154,0.06)',
                    fill: true, tension: 0.4, borderWidth: 2,
                    pointRadius: 0, pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#C69C6D',
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#F5E6CE' }, ticks: { stepSize: 1, font: { size: 11 }, color: '#C69C6D' } },
                x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#C69C6D' } }
            }
        }
    });

    // Distribution Chart
    new Chart(document.getElementById('distributionChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Photos', 'Users', 'Boards', 'Comments'],
            datasets: [{
                data: [{{ $stats['photos_count'] }}, {{ $stats['users_count'] }}, {{ $stats['boards_count'] }}, {{ $stats['comments_count'] }}],
                backgroundColor: ['#8B5E3C', '#C69C6D', '#E3C79A', '#F5E6CE'],
                borderColor: '#FFF8ED',
                borderWidth: 3,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 16, font: { size: 11, weight: '600' }, usePointStyle: true, pointStyleWidth: 8, color: '#8B5E3C' }
                }
            }
        }
    });
});
</script>
@endsection
