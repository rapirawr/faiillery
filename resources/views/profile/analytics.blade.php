@extends('layouts.app')

@section('title', 'Analisis Portofolio - Faiillery')

@section('content')
<div class="w-full max-w-5xl mx-auto pt-8 px-4 mb-16">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-display font-bold text-cocoa leading-tight mb-2">Analisis Portofolio</h1>
            <p class="text-sm text-caramel">Pantau pertumbuhan, interaksi, dan performa foto-foto estetik Anda di Faiillery.</p>
        </div>
        <a href="{{ route('profile.show', auth()->user()) }}" class="btn-secondary self-start md:self-auto flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Kembali ke Profil</span>
        </a>
    </div>

    <!-- Stats Grid (Frosted Glass) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        
        <!-- Total Dilihat -->
        <div class="rounded-3xl bg-white/20 backdrop-blur-xl border border-white/20 shadow-lg shadow-black/5 p-5 text-cocoa">
            <div class="flex items-center justify-between mb-3 text-caramel">
                <span class="text-xs font-bold uppercase tracking-wider">Total Dilihat</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </div>
            <div class="text-3xl font-bold font-display leading-none mb-1">{{ number_format($stats['total_views']) }}</div>
            <p class="text-[11px] text-cocoa/75">Penonton dari semua foto</p>
        </div>

        <!-- Total Suka -->
        <div class="rounded-3xl bg-white/20 backdrop-blur-xl border border-white/20 shadow-lg shadow-black/5 p-5 text-cocoa">
            <div class="flex items-center justify-between mb-3 text-caramel">
                <span class="text-xs font-bold uppercase tracking-wider">Total Suka</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </div>
            <div class="text-3xl font-bold font-display leading-none mb-1">{{ number_format($stats['total_likes']) }}</div>
            <p class="text-[11px] text-cocoa/75">Apresiasi dari komunitas</p>
        </div>

        <!-- Komentar -->
        <div class="rounded-3xl bg-white/20 backdrop-blur-xl border border-white/20 shadow-lg shadow-black/5 p-5 text-cocoa">
            <div class="flex items-center justify-between mb-3 text-caramel">
                <span class="text-xs font-bold uppercase tracking-wider">Komentar</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <div class="text-3xl font-bold font-display leading-none mb-1">{{ number_format($stats['total_comments']) }}</div>
            <p class="text-[11px] text-cocoa/75">Umpan balik percakapan</p>
        </div>

        <!-- Total Foto -->
        <div class="rounded-3xl bg-white/20 backdrop-blur-xl border border-white/20 shadow-lg shadow-black/5 p-5 text-cocoa">
            <div class="flex items-center justify-between mb-3 text-caramel">
                <span class="text-xs font-bold uppercase tracking-wider">Total Karya</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h14a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div class="text-3xl font-bold font-display leading-none mb-1">{{ number_format($stats['photos_count']) }}</div>
            <p class="text-[11px] text-cocoa/75">Karya foto terpublikasi</p>
        </div>

    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        
        <!-- View Analytics Line Chart -->
        <div class="rounded-3xl bg-white/20 backdrop-blur-xl border border-white/20 shadow-lg shadow-black/5 p-6 text-cocoa">
            <h3 class="text-base font-bold text-cocoa mb-1">Perkiraan Kunjungan (7 Hari Terakhir)</h3>
            <p class="text-xs text-caramel mb-6">Visualisasi mingguan performa views karya Anda.</p>
            
            @php
                $maxViews = max($stats['views_trend']) ?: 10;
                $points = [];
                foreach ($stats['views_trend'] as $idx => $val) {
                    $x = $idx * 65 + 40;
                    $y = 120 - (($val / $maxViews) * 80);
                    $points[] = "$x,$y";
                }
                $path = "M " . implode(" L ", $points);
            @endphp
            <div class="w-full h-40">
                <svg viewBox="0 0 460 140" class="w-full h-full">
                    <!-- Grid Lines -->
                    <line x1="30" y1="20" x2="440" y2="20" stroke="rgba(91,58,33,0.1)" stroke-dasharray="3"/>
                    <line x1="30" y1="60" x2="440" y2="60" stroke="rgba(91,58,33,0.1)" stroke-dasharray="3"/>
                    <line x1="30" y1="100" x2="440" y2="100" stroke="rgba(91,58,33,0.1)" stroke-dasharray="3"/>
                    
                    <!-- Line Path -->
                    <path d="{{ $path }}" fill="none" stroke="#8B5E3C" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    
                    <!-- Bullet Nodes -->
                    @foreach($points as $idx => $pt)
                        @php list($px, $py) = explode(',', $pt); @endphp
                        <circle cx="{{ $px }}" cy="{{ $py }}" r="5" fill="#8B5E3C" stroke="#FFF8ED" stroke-width="2"/>
                        <text x="{{ $px }}" y="{{ $py - 10 }}" text-anchor="middle" font-size="9" fill="#3B2417" font-weight="bold">{{ $stats['views_trend'][$idx] }}</text>
                    @endforeach
                    
                    <!-- X labels -->
                    @foreach($stats['chart_labels'] as $idx => $lbl)
                        <text x="{{ $idx * 65 + 40 }}" y="135" text-anchor="middle" font-size="10" fill="#8B5E3C" font-weight="bold">{{ $lbl }}</text>
                    @endforeach
                </svg>
            </div>
        </div>

        <!-- Likes Analytics Bar Chart -->
        <div class="rounded-3xl bg-white/20 backdrop-blur-xl border border-white/20 shadow-lg shadow-black/5 p-6 text-cocoa">
            <h3 class="text-base font-bold text-cocoa mb-1">Perkiraan Apresiasi Suka (7 Hari Terakhir)</h3>
            <p class="text-xs text-caramel mb-6">Visualisasi interaksi suka yang diperoleh.</p>
            
            @php
                $maxLikes = max($stats['likes_trend']) ?: 10;
            @endphp
            <div class="w-full h-40">
                <svg viewBox="0 0 460 140" class="w-full h-full">
                    <!-- Grid Lines -->
                    <line x1="30" y1="20" x2="440" y2="20" stroke="rgba(91,58,33,0.1)" stroke-dasharray="3"/>
                    <line x1="30" y1="60" x2="440" y2="60" stroke="rgba(91,58,33,0.1)" stroke-dasharray="3"/>
                    <line x1="30" y1="100" x2="440" y2="100" stroke="rgba(91,58,33,0.1)" stroke-dasharray="3"/>
                    
                    <!-- Bars -->
                    @foreach($stats['likes_trend'] as $idx => $val)
                        @php
                            $barHeight = ($val / $maxLikes) * 80;
                            $bx = $idx * 65 + 22;
                            $by = 120 - $barHeight;
                        @endphp
                        <rect x="{{ $bx }}" y="{{ $by }}" width="36" height="{{ $barHeight }}" rx="6" fill="url(#barGrad)" filter="drop-shadow(0 2px 4px rgba(91,58,33,0.1))"/>
                        <text x="{{ $bx + 18 }}" y="{{ $by - 8 }}" text-anchor="middle" font-size="9" fill="#3B2417" font-weight="bold">{{ $val }}</text>
                    @endforeach
                    
                    <!-- X labels -->
                    @foreach($stats['chart_labels'] as $idx => $lbl)
                        <text x="{{ $idx * 65 + 40 }}" y="135" text-anchor="middle" font-size="10" fill="#8B5E3C" font-weight="bold">{{ $lbl }}</text>
                    @endforeach
                    
                    <defs>
                        <linearGradient id="barGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#C69C6D"/>
                            <stop offset="100%" stop-color="#8B5E3C"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        </div>

    </div>

    <!-- Top Performing Content List -->
    <div class="rounded-3xl bg-white/20 backdrop-blur-xl border border-white/20 shadow-lg shadow-black/5 p-6 text-cocoa">
        <h3 class="text-lg font-bold text-cocoa mb-2">Karya Berperforma Terbaik</h3>
        <p class="text-xs text-caramel mb-6">Foto-foto Anda yang memiliki interaksi dan tayangan tertinggi.</p>
        
        @if($stats['top_photos']->count() > 0)
        <div class="space-y-4">
            @foreach($stats['top_photos'] as $index => $photo)
            <div class="flex items-center justify-between p-3.5 bg-white/30 dark:bg-white/5 border border-white/20 rounded-2xl hover:bg-white/50 transition-all">
                <div class="flex items-center gap-4 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-sand/30 font-bold flex items-center justify-center text-brown text-sm shrink-0">
                        {{ $index + 1 }}
                    </div>
                    <div class="w-12 h-12 rounded-xl overflow-hidden shrink-0 bg-sand border border-white/30">
                        <img src="{{ $photo->thumbnail_url }}" alt="{{ $photo->title }}" class="w-full h-full object-cover">
                    </div>
                    <div class="min-w-0">
                        <h4 class="font-bold text-sm text-cocoa truncate">{{ $photo->title ?: 'Tanpa Judul' }}</h4>
                        <p class="text-xs text-caramel truncate">Unggah {{ $photo->created_at->format('d M Y') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-6 text-xs text-cocoa/80 shrink-0 font-semibold pl-4">
                    <span class="flex items-center gap-1.5" title="Likes">
                        <svg class="w-4 h-4 text-red-500 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                        <span>{{ number_format($photo->likes_count) }}</span>
                    </span>
                    <span class="flex items-center gap-1.5" title="Views">
                        <svg class="w-4 h-4 text-caramel" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>{{ number_format($photo->views_count) }}</span>
                    </span>
                    <a href="{{ route('photos.show', $photo) }}" class="btn-primary py-1 px-3 text-xs leading-5">Lihat</a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8 text-sm text-caramel">Unggah karya foto Anda terlebih dahulu untuk melihat analisis performa.</div>
        @endif
    </div>

</div>
@endsection
