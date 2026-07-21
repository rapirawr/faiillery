@extends('layouts.app')

@section('title', '3D Dome Galeri - Failerry')

@section('content')
<style>
    /* Remove padding and prevent scrollbars specifically for the immersive 3D page */
    body {
        padding-bottom: 0 !important;
        overflow: hidden !important;
    }
    
    /* Make the page take exactly the height minus top navbar */
    .dome-container {
        position: relative;
        width: 100%;
        height: calc(100vh - 80px);
        overflow: hidden;
        background: #FFF8ED; /* Match body bg */
    }

    /* Premium styled control buttons */
    .dome-floating-controls {
        position: absolute;
        top: 1.5rem;
        left: 1.5rem;
        z-index: 50;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .dome-floating-title {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        z-index: 50;
        text-align: right;
        pointer-events: none;
    }

    .btn-back-dome {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        border-radius: 9999px;
        background: rgba(255, 248, 237, 0.4);
        border: 1px border-solid rgba(59, 36, 23, 0.15);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        font-size: 0.8125rem;
        font-weight: 700;
        color: #3B2417;
        box-shadow: 0 4px 12px rgba(59, 36, 23, 0.05);
        transition: all 200ms ease;
    }

    .btn-back-dome:hover {
        background: rgba(255, 248, 237, 0.85);
        transform: scale(1.03);
    }

    .btn-back-dome:active {
        transform: scale(0.97);
    }
</style>

<div class="dome-container">
    <!-- Floating Back Button -->
    <div class="dome-floating-controls">
        <a href="{{ route('home') }}" class="btn-back-dome">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Floating Title & Info -->
    <div class="dome-floating-title">
        <h1 class="text-xl md:text-2xl font-black tracking-tight" style="color: #3B2417;">3D Dome Galeri</h1>
        <p class="text-[10px] md:text-xs font-semibold uppercase tracking-wider mt-1" style="color: #8B5E3C;">
            Geser untuk rotasi &bull; Klik foto untuk memperbesar
        </p>
    </div>

    <!-- React Root Mount Node -->
    <div id="dome-gallery-root" class="w-full h-full" data-photos="{{ json_encode($photos) }}"></div>
</div>
@endsection

@push('scripts')
    @viteReactRefresh
    @vite('resources/js/dome-gallery-entry.jsx')
@endpush
