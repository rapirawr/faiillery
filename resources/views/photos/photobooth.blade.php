@extends('layouts.app')

@section('content')

{{-- Google Fonts --}}
@push('head')
<link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&family=DM+Serif+Display:ital@0;1&family=Instrument+Sans:wght@400;500;600&display=swap" rel="stylesheet">
@endpush

<div x-data="photobooth()" x-init="init()" class="photobooth-root">

    {{-- FILM SPROCKET STRIPS --}}
    <div class="sprocket-strip sprocket-left" id="sl"></div>
    <div class="sprocket-strip sprocket-right" id="sr"></div>

    <div class="pb-main">

        {{-- ══ TOP BAR ══ --}}
        <div class="pb-topbar">
            <a href="{{ route('home') }}" class="pb-back-btn">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="pb-frame-counter">
                REC <span x-text="String(capturedImages.length).padStart(2,'0')">00</span>
            </div>
            <div class="pb-iso-badge">ISO 400</div>
        </div>

        {{-- ══ STUDIO GRID ══ --}}
        <div class="pb-studio">

            {{-- ── LEFT: CAMERA ── --}}
            <div class="pb-camera-section">

                {{-- Viewport --}}
                <div class="pb-viewport"
                     x-ref="cameraBox"
                     :class="shakeCam ? 'cam-shake' : ''">

                    <video x-ref="video" autoplay playsinline muted
                           class="pb-video"
                           :style="(facingMode === 'user' ? 'transform:scaleX(-1);' : '') + (isCameraOff ? 'opacity:0;' : 'opacity:1;')"
                           style="transition: opacity 0.25s ease;"></video>
                    <canvas x-ref="canvas" style="display:none"></canvas>

                    {{-- Overlays --}}
                    <div class="pb-overlay">
                        <div class="pb-vignette"></div>
                        <div class="pb-corners">
                            <div class="pb-corner tl"></div>
                            <div class="pb-corner tr"></div>
                            <div class="pb-corner bl"></div>
                            <div class="pb-corner br"></div>
                        </div>
                        <div class="pb-hud">
                            <span x-text="clockStr">--:--</span>
                            <div class="pb-hud-dots">
                                <template x-for="i in maxCaptures" :key="i">
                                    <div class="pb-hud-dot" :class="capturedImages.length >= i ? 'filled' : ''"></div>
                                </template>
                            </div>
                            <span x-text="activeLayout.toUpperCase()">STRIP</span>
                        </div>
                    </div>

                    {{-- Flash --}}
                    <div class="pb-flash" :class="flash ? 'active' : ''"></div>

                    {{-- Camera Loading --}}
                    <div class="pb-cam-loading" x-show="!isStreaming && !cameraError" style="display:none">
                        <div class="pb-loading-spin"></div>
                        <span>SWITCHING...</span>
                    </div>

                    {{-- Camera Error --}}
                    <div class="pb-cam-error" x-show="cameraError" style="display:none">
                        <div class="pb-error-icon">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <circle cx="12" cy="13" r="3" stroke-width="2"/>
                            </svg>
                        </div>
                        <h3>Akses kamera ditolak</h3>
                        <p>Izinkan akses kamera di browser untuk menggunakan photobooth.</p>
                        <button @click="startStream()">Coba lagi</button>
                    </div>

                    {{-- Flip camera --}}
                    <button @click="switchCamera()"
                            :disabled="!isStreaming || isProcessing || isSwitching"
                            class="pb-flip-btn lg:hidden"
                            :class="isSwitching ? 'switching' : ''">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             :style="isSwitching ? 'animation: pb-spin 0.6s linear infinite' : ''">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>

                    {{-- Torch Toggle (Hanya muncul kalau back camera) --}}
                    <button @click="toggleTorch()"
                            x-show="facingMode === 'environment'"
                            class="pb-torch-btn lg:hidden"
                            :class="torchActive ? 'active' : ''"
                            style="display:none">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </button>
                </div>

                {{-- Capture controls (Mobile & Desktop) --}}
                <div class="pb-capture-row">
                    <button @click="resetSession()" 
                            class="pb-reset-btn"
                            :style="capturedImages.length === 0 ? 'opacity:0;pointer-events:none' : ''">
                        RETAKE
                    </button>
                    
                    <button @click="takeSnap()"
                            :disabled="!isStreaming || isProcessing || capturedImages.length >= maxCaptures"
                            class="pb-shutter-btn">
                        <div class="pb-shutter-inner"></div>
                    </button>
                    
                    <div class="pb-shot-label" x-text="shotLabel">READY</div>
                </div>

                {{-- ── RECENT CLIPS / MINI GALLERY ── --}}
                <div class="pb-mini-gallery" x-show="capturedImages.length > 0" x-transition>
                    <div class="pb-gallery-label">RECENT CLIPS</div>
                    <div class="pb-gallery-scroll">
                        <template x-for="(img, index) in capturedImages" :key="index">
                            <div class="pb-gallery-item">
                                <img :src="img" :style="filterCSS[activeFilter] ? `filter:${filterCSS[activeFilter]}` : ''" />
                                <button @click="removeImage(index)" class="pb-item-del">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

            </div>{{-- /camera-section --}}

            {{-- ── MOBILE PREVIEW SECTION ── --}}
            <div class="pb-mobile-preview-area lg:hidden" x-show="capturedImages.length > 0" x-transition>
                <div class="pb-strip-wrap mobile-preview-container"></div>
            </div>

            {{-- ── RIGHT: SIDEBAR (Desktop Only) ── --}}
            <div class="pb-sidebar hidden lg:flex">

                {{-- LAYOUT --}}
                <div class="pb-panel-section">
                    <div class="pb-panel-label">Layout</div>
                    <div class="pb-layout-grid">
                        <template x-for="l in layoutOptions" :key="l.id">
                            <button @click="setLayout(l.id)"
                                    :class="activeLayout === l.id ? 'active' : ''"
                                    class="pb-layout-btn"
                                    :title="l.label">
                                <div class="pb-dynamic-icon" x-html="l.iconHtml"></div>
                                <span class="pb-layout-name" x-text="l.label"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- FILM STOCK / FILTER --}}
                <div class="pb-panel-section">
                    <div class="pb-panel-label">Film Stock</div>
                    <div class="pb-filter-scroll">
                        <template x-for="f in filters" :key="f.id">
                            <button @click="setFilter(f.id)"
                                    :class="activeFilter === f.id ? 'active' : ''"
                                    class="pb-filter-btn">
                                <div class="pb-filter-swatch" :style="f.swatchStyle"></div>
                                <span class="pb-filter-label" x-text="f.name"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- FRAME --}}
                <div class="pb-panel-section">
                    <div class="pb-panel-label">Frame</div>
                    <div class="pb-frame-scroll">
                        <template x-for="fr in frames" :key="fr.id">
                            <button @click="setFrame(fr.id)"
                                    :class="activeFrame === fr.id ? 'active' : ''"
                                    class="pb-frame-btn">
                                <div class="pb-frame-thumb" :style="fr.thumbStyle"></div>
                                <span class="pb-frame-label" x-text="fr.name"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- STRIP PREVIEW --}}
                <div class="pb-strip-section">
                    <div class="pb-panel-label">Preview</div>
                    <div class="pb-strip-wrap desktop-preview-container"></div>
                </div>

                {{-- ACTIONS --}}
                <div class="pb-action-panel">
                    <div class="pb-action-row">
                        <button @click="saveToFailerry"
                                :disabled="capturedImages.length !== maxCaptures || isSaving"
                                class="pb-btn-primary">
                            <template x-if="isSaving">
                                <svg class="pb-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </template>
                            <span x-text="isSaving ? 'PUBLISHING...' : 'PUBLISH'"></span>
                        </button>
                        <button @click="downloadStrip()"
                                :disabled="capturedImages.length === 0"
                                class="pb-btn-secondary">
                            SAVE
                        </button>
                    </div>
                </div>

            </div>{{-- /sidebar --}}

        </div>{{-- /studio --}}

    </div>{{-- /pb-main --}}

    {{-- ══ MOBILE BOTTOM PANEL ══ --}}
    <div class="pb-mobile-panel lg:hidden">

        {{-- Tab bar --}}
        <div class="pb-mobile-tabs">
            <button @click="mobileTab = 'layout'"
                    :class="mobileTab === 'layout' ? 'active' : ''"
                    class="pb-mobile-tab">LAYOUT</button>
            <button @click="mobileTab = 'filter'"
                    :class="mobileTab === 'filter' ? 'active' : ''"
                    class="pb-mobile-tab">FILTER</button>
            <button @click="mobileTab = 'frame'"
                    :class="mobileTab === 'frame' ? 'active' : ''"
                    class="pb-mobile-tab">FRAME</button>
        </div>

        {{-- Layout tab --}}
        <div x-show="mobileTab === 'layout'" class="pb-mobile-scroll">
            <template x-for="l in layoutOptions" :key="l.id">
                <button @click="setLayout(l.id)"
                        :class="activeLayout === l.id ? 'active' : ''"
                        class="pb-mobile-layout-btn">
                    <div class="pb-mobile-layout-icon" x-html="l.iconHtml"></div>
                    <span class="pb-filter-label" x-text="l.label"></span>
                </button>
            </template>
        </div>

        {{-- Filter tab --}}
        <div x-show="mobileTab === 'filter'" style="display:none" class="pb-mobile-scroll">
            <template x-for="f in filters" :key="f.id">
                <button @click="setFilter(f.id)"
                        :class="activeFilter === f.id ? 'active' : ''"
                        class="pb-filter-btn">
                    <div class="pb-filter-swatch" :style="f.swatchStyle"></div>
                    <span class="pb-filter-label" x-text="f.name"></span>
                </button>
            </template>
        </div>

        {{-- Frame tab --}}
        <div x-show="mobileTab === 'frame'" style="display:none" class="pb-mobile-scroll">
            <template x-for="fr in frames" :key="fr.id">
                <button @click="setFrame(fr.id)"
                        :class="activeFrame === fr.id ? 'active' : ''"
                        class="pb-frame-btn">
                    <div class="pb-frame-thumb" :style="fr.thumbStyle"></div>
                    <span class="pb-frame-label" x-text="fr.name"></span>
                </button>
            </template>
        </div>

        {{-- Mobile submit row --}}
        <div class="pb-mobile-submit" x-show="capturedImages.length > 0"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">
            <button @click="saveToFailerry"
                    :disabled="capturedImages.length !== maxCaptures || isSaving"
                    class="pb-btn-primary" style="flex:1">
                <span x-text="isSaving ? 'PUBLISHING...' : 'PUBLISH'"></span>
            </button>
            <button @click="downloadStrip()"
                    :disabled="capturedImages.length === 0"
                    class="pb-btn-secondary">SAVE</button>
            <button @click="resetSession()" class="pb-btn-icon">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
        </div>

    </div>{{-- /mobile-panel --}}

</div>{{-- /photobooth-root --}}


{{-- ══════════════════════════════════════
     SCRIPT
══════════════════════════════════════ --}}
<script>
function photobooth() {
    return {

        // ── State
        isStreaming:     false,
        cameraError:     false,
        isSwitching:     false,
        isCameraOff:     false,   // ← BARU: flag khusus switch kamera
        capturedImages:  [],
        flash:           false,
        shakeCam:        false,
        isProcessing:    false,
        isSaving:        false,
        facingMode:      'user',
        activeLayout:    'strip',
        activeFilter:    'none',
        activeFrame:     'classic',
        mobileTab:       'layout',
        shotLabel:       'READY',
        clockStr:        '--:--',
        clockInterval:   null,
        torchActive:     false, // ← BARU: state flash/senter

        // ── Layout map
        get maxCaptures() {
            return {
                single:1, double:2, trio:3, strip:4, grid:4,
                scattered:4, overlap:3, collage:3, diagonal:4, zine:3, stack:4
            }[this.activeLayout] ?? 4;
        },

        // ── Data
        filters: [
            { id:'none',     name:'Normal',   swatchStyle:'background:linear-gradient(135deg,#2a2724,#4a4540)' },
            { id:'vintage',  name:'Kodak',    swatchStyle:'background:linear-gradient(135deg,#8b6a40,#c4955a)' },
            { id:'bw',       name:'Ilford',   swatchStyle:'background:linear-gradient(135deg,#3a3a3a,#888888)' },
            { id:'sepia',    name:'Sepia',    swatchStyle:'background:linear-gradient(135deg,#704214,#b07030)' },
            { id:'dreamy',   name:'Dreamy',   swatchStyle:'background:linear-gradient(135deg,#c8a0e8,#80c0f0)' },
            { id:'faded',    name:'Faded',    swatchStyle:'background:linear-gradient(135deg,#b8c8c0,#d8e8e0)' },
            { id:'lomo',     name:'Lomo',     swatchStyle:'background:linear-gradient(135deg,#1a0030,#c020c0)' },
            { id:'golden',   name:'Golden',   swatchStyle:'background:linear-gradient(135deg,#7a4a00,#f0c060)' },
            { id:'cool',     name:'Cool',     swatchStyle:'background:linear-gradient(135deg,#002060,#4080e0)' },
            { id:'fade35',   name:'35mm',     swatchStyle:'background:linear-gradient(135deg,#604020,#d0a878)' },
            { id:'mist',     name:'Mist',     swatchStyle:'background:linear-gradient(135deg,#8090a0,#c8d8e8)' },
            { id:'velvia',   name:'Velvia',   swatchStyle:'background:linear-gradient(135deg,#402000,#e06020)' },
            { id:'portra',   name:'Portra',   swatchStyle:'background:linear-gradient(135deg,#806040,#e8c8a0)' },
            { id:'cross',    name:'Cross',    swatchStyle:'background:linear-gradient(135deg,#403000,#a0c020)' },
        ],

        frames: [
            { id:'classic',  name:'Classic',  thumbStyle:'background:#f5f0e8;border-color:rgba(200,169,110,0.5)',                        bg:'#f5f0e8',                              color:'#2a2018', accent:'#c8a96e' },
            { id:'dark',     name:'Dark',     thumbStyle:'background:#111;border-color:rgba(255,255,255,0.15)',                          bg:'#111010',                              color:'#ffffff', accent:'#888888' },
            { id:'blush',    name:'Blush',    thumbStyle:'background:linear-gradient(160deg,#ffe4ef,#ffd4e4);border-color:rgba(255,150,180,0.4)', bg:'linear-gradient(160deg,#ffe4ef,#ffd4e4)', color:'#7a3050', accent:'#ff90b8' },
            { id:'forest',   name:'Forest',   thumbStyle:'background:#1a2e1a;border-color:rgba(100,180,80,0.3)',                         bg:'#1a2e1a',                              color:'#c8e8a8', accent:'#6ab050' },
            { id:'polaroid', name:'Polaroid', thumbStyle:'background:#fafaf5;border-color:#ddd',                                        bg:'#fafaf5',                              color:'#333333', accent:'#999999' },
            { id:'cinema',   name:'Cinema',   thumbStyle:'background:#1a1209;border-color:rgba(200,168,75,0.4)',                         bg:'#1a1209',                              color:'#c8a84b', accent:'#c8a84b' },
            { id:'diaryfm',  name:'Diary',    thumbStyle:'background:#f0e8d8;border-color:rgba(160,120,70,0.5)',                         bg:'#f0e8d8',                              color:'#5c3d1e', accent:'#a0783c' },
            { id:'y2k',      name:'Y2K',      thumbStyle:'background:linear-gradient(135deg,#e0c8ff,#c8e8ff);border-color:rgba(180,120,255,0.5)', bg:'linear-gradient(135deg,#e8d8ff,#d0ecff)', color:'#5020a0', accent:'#9060e0' },
            { id:'matcha',   name:'Matcha',   thumbStyle:'background:#d4e8c8;border-color:rgba(80,140,60,0.4)',                          bg:'#d4e8c8',                              color:'#2a4a1a', accent:'#5a8c40' },
            { id:'midnight', name:'Night',    thumbStyle:'background:linear-gradient(160deg,#0a0a1e,#101030);border-color:rgba(100,120,255,0.4)', bg:'linear-gradient(160deg,#0a0a1e,#101030)', color:'#a0b0ff', accent:'#6070e0' },
            { id:'washi',    name:'Washi',    thumbStyle:'background:#fdf6ec;border-color:rgba(220,100,80,0.4)',                         bg:'#fdf6ec',                              color:'#8c3020', accent:'#dc6450' },
            { id:'lomo',     name:'Lomo',     thumbStyle:'background:#120c18;border-color:rgba(200,50,200,0.4)',                         bg:'#120c18',                              color:'#e060e0', accent:'#c030c0' },
        ],

        layoutOptions: [
            { id:'single',   label:'1',       iconHtml:'<div style="display:flex;flex-direction:column;gap:2px;width:18px;"><div style="height:4px;border-radius:1px;background:currentColor;opacity:0.6;"></div></div>' },
            { id:'double',   label:'2',       iconHtml:'<div style="display:flex;flex-direction:column;gap:2px;width:18px;"><div style="height:4px;border-radius:1px;background:currentColor;opacity:0.6;"></div><div style="height:4px;border-radius:1px;background:currentColor;opacity:0.6;"></div></div>' },
            { id:'strip',    label:'Strip',   iconHtml:'<div style="display:flex;flex-direction:column;gap:1.5px;width:18px;"><div style="height:3px;border-radius:1px;background:currentColor;opacity:0.6;"></div><div style="height:3px;border-radius:1px;background:currentColor;opacity:0.6;"></div><div style="height:3px;border-radius:1px;background:currentColor;opacity:0.6;"></div><div style="height:3px;border-radius:1px;background:currentColor;opacity:0.6;"></div></div>' },
            { id:'trio',     label:'3',       iconHtml:'<div style="display:flex;flex-direction:column;gap:2px;width:18px;"><div style="height:4px;border-radius:1px;background:currentColor;opacity:0.6;"></div><div style="height:4px;border-radius:1px;background:currentColor;opacity:0.6;"></div><div style="height:4px;border-radius:1px;background:currentColor;opacity:0.6;"></div></div>' },
            { id:'grid',     label:'Grid',    iconHtml:'<div style="display:grid;grid-template-columns:1fr 1fr;gap:2px;width:18px;"><div style="height:8px;border-radius:1px;background:currentColor;opacity:0.6;"></div><div style="height:8px;border-radius:1px;background:currentColor;opacity:0.6;"></div><div style="height:8px;border-radius:1px;background:currentColor;opacity:0.6;"></div><div style="height:8px;border-radius:1px;background:currentColor;opacity:0.6;"></div></div>' },
            { id:'scattered',label:'Scatter', iconHtml:'<div style="position:relative;width:22px;height:22px;"><div style="position:absolute;top:0;left:2px;width:14px;height:10px;border-radius:1px;background:currentColor;opacity:0.6;transform:rotate(-8deg);"></div><div style="position:absolute;bottom:0;right:0;width:14px;height:10px;border-radius:1px;background:currentColor;opacity:0.6;transform:rotate(6deg);"></div></div>' },
            { id:'overlap',  label:'Overlap', iconHtml:'<div style="position:relative;width:22px;height:18px;"><div style="position:absolute;top:0;left:0;width:16px;height:12px;border-radius:1px;background:currentColor;opacity:0.4;"></div><div style="position:absolute;top:4px;left:4px;width:16px;height:12px;border-radius:1px;background:currentColor;opacity:0.6;"></div></div>' },
            { id:'collage',  label:'Collage', iconHtml:'<div style="display:grid;grid-template-columns:1.5fr 1fr;grid-template-rows:1fr 1fr;gap:2px;width:22px;height:18px;"><div style="grid-row:span 2;border-radius:1px;background:currentColor;opacity:0.6;"></div><div style="border-radius:1px;background:currentColor;opacity:0.5;"></div><div style="border-radius:1px;background:currentColor;opacity:0.4;"></div></div>' },
            { id:'diagonal', label:'Diag',    iconHtml:'<div style="display:flex;flex-direction:column;gap:2px;width:22px;">' + [0,1,2,3].map(i=>`<div style="height:3px;border-radius:1px;background:currentColor;opacity:0.6;width:${10+i*4}px;margin-left:${i*2}px;"></div>`).join('') + '</div>' },
            { id:'zine',     label:'Zine',    iconHtml:'<div style="display:grid;grid-template-columns:1fr 1fr;grid-template-rows:1fr 1.4fr;gap:2px;width:22px;height:20px;"><div style="border-radius:1px;background:currentColor;opacity:0.6;"></div><div style="grid-row:span 2;border-radius:1px;background:currentColor;opacity:0.5;"></div><div style="border-radius:1px;background:currentColor;opacity:0.4;"></div></div>' },
            { id:'stack',    label:'Stack',   iconHtml:'<div style="position:relative;width:22px;height:20px;">' + [3,2,1,0].map(i=>`<div style="position:absolute;top:${i*3}px;left:${i*2}px;width:18px;height:13px;border-radius:1px;background:currentColor;opacity:${0.3+i*0.15};"></div>`).join('') + '</div>' },
        ],

        filterCSS: {
            none:    '',
            vintage: 'sepia(0.25) contrast(1.1) brightness(0.92) saturate(0.85)',
            bw:      'grayscale(1) contrast(1.15)',
            sepia:   'sepia(0.9) brightness(0.95)',
            dreamy:  'brightness(1.08) saturate(1.3) contrast(0.95)',
            faded:   'saturate(0.5) brightness(1.05) contrast(0.9)',
            lomo:    'contrast(1.5) saturate(1.6) brightness(0.85)',
            golden:  'sepia(0.4) saturate(1.4) brightness(1.05) hue-rotate(-10deg)',
            cool:    'saturate(0.9) brightness(1.02) hue-rotate(190deg) contrast(1.05)',
            fade35:  'sepia(0.3) saturate(0.75) brightness(1.1) contrast(0.88)',
            mist:    'saturate(0.4) brightness(1.12) contrast(0.85) blur(0.3px)',
            velvia:  'saturate(1.8) contrast(1.2) brightness(0.9)',
            portra:  'sepia(0.15) saturate(1.1) brightness(1.05) contrast(0.95) hue-rotate(5deg)',
            cross:   'saturate(1.4) hue-rotate(30deg) contrast(1.15) brightness(0.95)',
        },

        // ── Init
        async init() {
            this.buildSprockets();
            this.startClock();
            window.addEventListener('resize', () => this.buildSprockets());
            await this.startStream();
            this.renderStrip();
        },

        // ── Sprocket holes
        buildSprockets() {
            const count = Math.floor(window.innerHeight / 30) + 2;
            ['sl','sr'].forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;
                el.innerHTML = '';
                for (let i = 0; i < count; i++) {
                    const d = document.createElement('div');
                    d.className = 'pb-sprocket-hole';
                    el.appendChild(d);
                }
            });
        },

        // ── Clock
        startClock() {
            const update = () => {
                const now = new Date();
                const h = String(now.getHours()).padStart(2,'0');
                const m = String(now.getMinutes()).padStart(2,'0');
                this.clockStr = `${h}:${m}`;
            };
            update();
            this.clockInterval = setInterval(update, 10000);
        },

        _videoDevices: [],
        _currentDeviceId: null,
        _deviceFacingMap: {},       
        _attachTimeoutId: null,     
        _lastSwitchTime: 0,     

        // ── Safely detach stream from video element and release hardware
        // CRITICAL ORDER: pause → detach → stop
        // If we stop tracks while video is still rendering, Android's camera HAL
        // holds the hardware lock for 60-90s before releasing.
        _detachAndStop() {
            const video = this.$refs.video;
            if (!video) return;

            // Cancel pending attach timeout
            if (this._attachTimeoutId) {
                clearTimeout(this._attachTimeoutId);
                this._attachTimeoutId = null;
            }

            // Remove stale event handler
            video.onloadedmetadata = null;

            // 1. Pause the video element FIRST — tell rendering pipeline to stop
            video.pause();

            // 2. Grab the stream reference before detaching
            const stream = video.srcObject;

            // 3. Detach — breaks the rendering pipeline's connection to the stream
            video.srcObject = null;

            // 4. NOW stop tracks — hardware can release immediately
            //    because no renderer is holding a reference
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
            }
        },

        // ── Attach a stream to video element with proper loadedmetadata waiting
        _attachStream(stream) {
            return new Promise((resolve, reject) => {
                const video = this.$refs.video;
                if (!video) return reject(new Error('No video element'));

                // Cancel any pending timeout from previous attach
                if (this._attachTimeoutId) {
                    clearTimeout(this._attachTimeoutId);
                    this._attachTimeoutId = null;
                }

                // Remove old listener to avoid stale callbacks
                video.onloadedmetadata = null;

                const onReady = () => {
                    this._attachTimeoutId = null;
                    video.onloadedmetadata = null;
                    video.play().then(resolve).catch(resolve);
                };

                // Safety timeout — don't hang forever
                this._attachTimeoutId = setTimeout(onReady, 2000);

                video.onloadedmetadata = () => {
                    if (this._attachTimeoutId) {
                        clearTimeout(this._attachTimeoutId);
                    }
                    onReady();
                };

                video.srcObject = stream;
            });
        },

        // ── Build a device → facingMode map by analyzing labels & track settings
        async _buildDeviceMap() {
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                this._videoDevices = devices.filter(d => d.kind === 'videoinput');
            } catch (_) {
                return;
            }

            // Rebuild map — preserve existing known mappings for current devices only
            const newMap = {};
            for (const dev of this._videoDevices) {
                // Preserve existing known mappings (from track.getSettings)
                if (this._deviceFacingMap[dev.deviceId]) {
                    newMap[dev.deviceId] = this._deviceFacingMap[dev.deviceId];
                    continue;
                }

                const lbl = (dev.label || '').toLowerCase();

                // Conservative keyword matching with word boundaries
                const isBack = /\b(back|rear|belakang|environment)\b/.test(lbl);
                const isFront = /\b(front|depan|user|selfie|facetime)\b/.test(lbl);

                if (isBack) newMap[dev.deviceId] = 'environment';
                else if (isFront) newMap[dev.deviceId] = 'user';
            }

            // ── Heuristic for 2-camera devices
            if (this._videoDevices.length === 2) {
                const [a, b] = this._videoDevices;
                if (newMap[a.deviceId] && !newMap[b.deviceId]) {
                    newMap[b.deviceId] = newMap[a.deviceId] === 'user' ? 'environment' : 'user';
                } else if (!newMap[a.deviceId] && newMap[b.deviceId]) {
                    newMap[a.deviceId] = newMap[b.deviceId] === 'user' ? 'environment' : 'user';
                }
            }

            // ── Heuristic for 3+ camera devices (Samsung, Xiaomi, Pixel, etc)
            // If we know which device is 'user' but no device is mapped as 'environment',
            // the SECOND device in the list is almost always the main back camera.
            // (Android orders: front, back-main, back-ultrawide, back-macro)
            if (this._videoDevices.length >= 3) {
                const hasEnv = Object.values(newMap).includes('environment');
                const knownUserId = Object.entries(newMap).find(([, f]) => f === 'user')?.[0];

                if (knownUserId && !hasEnv) {
                    // Find the first device that isn't the front camera
                    const backCandidate = this._videoDevices.find(d => d.deviceId !== knownUserId);
                    if (backCandidate) {
                        newMap[backCandidate.deviceId] = 'environment';
                        console.log('[Camera] 3+ cam heuristic: assigned environment to', backCandidate.deviceId.slice(0, 8));
                    }
                }
            }

            this._deviceFacingMap = newMap;
            console.log('[Camera] Device map:', JSON.stringify(this._deviceFacingMap));
        },

        // ── Camera stream — INIT only (first load)
        async startStream() {
            this.cameraError = false;

            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: { ideal: 'user' },
                        width: { ideal: 1080 },
                        height: { ideal: 1440 }
                    },
                    audio: false
                });

                await this._attachStream(stream);
                this.isStreaming = true;
                this.facingMode = 'user';

                const track = stream.getVideoTracks()[0];
                if (track) {
                    const s = track.getSettings();
                    this._currentDeviceId = s.deviceId || null;
                    if (s.facingMode) this.facingMode = s.facingMode;
                    // Register in facing map
                    if (s.deviceId) {
                        this._deviceFacingMap[s.deviceId] = s.facingMode || 'user';
                    }
                    console.log('[Camera] Init OK — device:', s.deviceId?.slice(0, 8), 'facing:', s.facingMode);
                }

                // Enumerate after permission granted (labels now available)
                await this._buildDeviceMap();

            } catch (e) {
                console.error('[Camera] Init error:', e.name, e.message);
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                    await this._attachStream(stream);
                    this.isStreaming = true;
                    const track = stream.getVideoTracks()[0];
                    if (track) {
                        const s = track.getSettings();
                        this._currentDeviceId = s.deviceId || null;
                        if (s.facingMode) this.facingMode = s.facingMode;
                        // Register in facing map (was missing in fallback path)
                        if (s.deviceId) {
                            this._deviceFacingMap[s.deviceId] = s.facingMode || 'user';
                        }
                    }
                    await this._buildDeviceMap();
                } catch (e2) {
                    console.error('[Camera] Fallback also failed:', e2.name);
                    this.cameraError = true;
                    this.isStreaming = false;
                }
            }
        },

        // ── Helper: request a stream with retry for hardware-busy errors
        // NotReadableError = camera HAL busy (Samsung, Xiaomi, most Android)
        // AbortError       = camera released mid-request (iOS Safari, some Android)
        async _tryGetStream(constraints, maxRetries = 3) {
            const RETRYABLE = ['NotReadableError', 'AbortError'];
            for (let attempt = 0; attempt <= maxRetries; attempt++) {
                try {
                    return await navigator.mediaDevices.getUserMedia(constraints);
                } catch (e) {
                    if (RETRYABLE.includes(e.name) && attempt < maxRetries) {
                        const delay = 500 * (attempt + 1); // 500, 1000, 1500ms
                        console.warn(`[Switch] ${e.name} — retry ${attempt + 1}/${maxRetries} in ${delay}ms`);
                        await new Promise(r => setTimeout(r, delay));
                        continue;
                    }
                    console.warn('[Switch] Constraint failed:', e.name, attempt > 0 ? `(attempt ${attempt + 1})` : '');
                    return null;
                }
            }
            return null;
        },

        // ── Switch kamera (front ↔ rear) — BULLETPROOF HARDWARE SWITCH
        async switchCamera() {
            // Hard debounce — prevent rapid double-tap race condition
            const now = Date.now();
            if (now - this._lastSwitchTime < 1200) return;
            if (this.isSwitching || this.isProcessing) return;
            this._lastSwitchTime = now;
            this.isSwitching = true;
            this.isCameraOff = true;

            const oldDeviceId = this._currentDeviceId;
            const oldFacing = this.facingMode;
            const wantFacing = oldFacing === 'user' ? 'environment' : 'user';

            console.log('[Switch] From:', oldFacing, '(', oldDeviceId?.slice(0, 8), ') → Want:', wantFacing);

            // ── 1. STOP old stream completely BEFORE requesting new one
            this._detachAndStop();
            this.isStreaming = false;

            // Wait for hardware release — 500ms covers most Android devices
            // (with correct detach order, hardware releases much faster)
            await new Promise(r => setTimeout(r, 500));

            // ── 2. Refresh device list
            await this._buildDeviceMap();

            let newStream = null;

            // ── STRATEGY A: Find the exact target device by ID from our map
            // This is the most reliable way — we know which deviceId maps to which facing
            const targetDeviceId = Object.entries(this._deviceFacingMap)
                .find(([id, facing]) => facing === wantFacing && id !== oldDeviceId)?.[0];

            if (targetDeviceId) {
                console.log('[Switch] Strategy A: deviceId exact →', targetDeviceId.slice(0, 8));
                newStream = await this._tryGetStream({
                    video: {
                        deviceId: { exact: targetDeviceId },
                        width: { ideal: 1080 },
                        height: { ideal: 1440 }
                    },
                    audio: false
                });
            }

            // ── STRATEGY B: facingMode exact (browser-native switching)
            if (!newStream) {
                console.log('[Switch] Strategy B: facingMode exact →', wantFacing);
                newStream = await this._tryGetStream({
                    video: {
                        facingMode: { exact: wantFacing },
                        width: { ideal: 1080 },
                        height: { ideal: 1440 }
                    },
                    audio: false
                });
            }

            // ── STRATEGY C: Try any OTHER device that isn't the current one
            if (!newStream && this._videoDevices.length >= 2) {
                // Pick the device that is NOT the old one
                const candidates = this._videoDevices.filter(d => d.deviceId !== oldDeviceId);
                for (const dev of candidates) {
                    console.log('[Switch] Strategy C: trying other device →', dev.deviceId.slice(0, 8));
                    newStream = await this._tryGetStream({
                        video: { deviceId: { exact: dev.deviceId } },
                        audio: false
                    }, 1); // Fewer retries for candidates
                    if (newStream) break;
                }
            }

            // ── STRATEGY D: Bare minimum fallback
            if (!newStream) {
                console.log('[Switch] Strategy D: bare fallback');
                newStream = await this._tryGetStream({
                    video: { facingMode: wantFacing },
                    audio: false
                }, 1);
            }
            if (!newStream) {
                newStream = await this._tryGetStream({ video: true, audio: false }, 1);
            }

            // ── 3. VERIFY & APPLY
            if (newStream) {
                const track = newStream.getVideoTracks()[0];
                const settings = track?.getSettings() || {};
                const newDeviceId = settings.deviceId || null;
                const reportedFacing = settings.facingMode || null;

                console.log('[Switch] Got device:', newDeviceId?.slice(0, 8),
                            'reported facing:', reportedFacing,
                            'changed:', newDeviceId !== oldDeviceId);

                // Attach to video
                await this._attachStream(newStream);
                this.isStreaming = true;
                this._currentDeviceId = newDeviceId;

                // Update our device map with fresh info from the track
                if (newDeviceId && reportedFacing) {
                    this._deviceFacingMap[newDeviceId] = reportedFacing;
                }

                // ── CRITICAL: Determine the real facingMode for mirroring
                if (reportedFacing) {
                    // Browser explicitly told us — trust it
                    this.facingMode = reportedFacing;
                } else if (newDeviceId && newDeviceId !== oldDeviceId) {
                    // Device ID changed → hardware actually switched
                    // Check our map first
                    if (this._deviceFacingMap[newDeviceId]) {
                        this.facingMode = this._deviceFacingMap[newDeviceId];
                    } else {
                        // No map entry, assume the direction we asked for
                        this.facingMode = wantFacing;
                        this._deviceFacingMap[newDeviceId] = wantFacing;
                    }
                } else {
                    // ╔═══════════════════════════════════════════════════════╗
                    // ║  Device ID did NOT change — camera is the SAME!      ║
                    // ║  Do NOT flip facingMode, or we'll get false invert.  ║
                    // ╚═══════════════════════════════════════════════════════╝
                    console.warn('[Switch] Device ID unchanged — keeping facingMode as', oldFacing);
                }
            } else {
                // All strategies failed — try to restart the old camera
                console.error('[Switch] ALL strategies failed, restarting old camera');
                try {
                    const recovery = oldDeviceId
                        ? await this._tryGetStream({ video: { deviceId: { exact: oldDeviceId } }, audio: false })
                        : await this._tryGetStream({ video: true, audio: false });
                    if (recovery) {
                        await this._attachStream(recovery);
                        this.isStreaming = true;
                        // facingMode stays the same
                    } else {
                        this.cameraError = true;
                    }
                } catch (_) {
                    this.cameraError = true;
                }
            }

            this.isCameraOff = false;
            this.isSwitching = false;
            this.torchActive = false;
        },

        // ── Toggle Torch (Senter Kamera Belakang)
        async toggleTorch() {
            const stream = this.$refs.video?.srcObject;
            if (!stream) return;

            const track = stream.getVideoTracks()[0];
            if (!track) return;

            const capabilities = track.getCapabilities?.() || {};
            if (!capabilities.torch) {
                window.showToast?.('Flash tidak tersedia di kamera ini.', 'info');
                return;
            }

            try {
                this.torchActive = !this.torchActive;
                await track.applyConstraints({
                    advanced: [{ torch: this.torchActive }]
                });
            } catch (e) {
                console.error('[Camera] Torch error:', e);
                this.torchActive = false;
                window.showToast?.('Gagal mengaktifkan flash.', 'error');
            }
        },

        // ── Hapus satu gambar
        removeImage(index) {
            this.capturedImages.splice(index, 1);
            
            if (this.capturedImages.length === 0) {
                this.shotLabel = 'READY';
            } else {
                this.shotLabel = `${this.capturedImages.length}/${this.maxCaptures} — SHOOT`;
            }
            
            this.$nextTick(() => this.renderStrip());
        },

        // ── Manual single-shot snap
        takeSnap() {
            if (!this.isStreaming || this.isProcessing) return;
            if (this.capturedImages.length >= this.maxCaptures) return;

            this.isProcessing = true;

            this.flash = true;
            setTimeout(() => this.flash = false, 140);

            this.shakeCam = true;
            setTimeout(() => this.shakeCam = false, 400);

            const video  = this.$refs.video;
            const canvas = this.$refs.canvas;

            let tW = video.videoWidth;
            let tH = video.videoWidth * (4 / 3);
            if (tH > video.videoHeight) {
                tH = video.videoHeight;
                tW = video.videoHeight * (3 / 4);
            }

            canvas.width  = tW;
            canvas.height = tH;
            const ctx = canvas.getContext('2d');

            if (this.facingMode === 'user') {
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
            }

            const ox = (video.videoWidth  - tW) / 2;
            const oy = (video.videoHeight - tH) / 2;
            ctx.drawImage(video, ox, oy, tW, tH, 0, 0, tW, tH);

            this.capturedImages.push(canvas.toDataURL('image/jpeg', 0.92));

            const taken = this.capturedImages.length;
            const total = this.maxCaptures;

            if (taken < total) {
                this.shotLabel    = `${taken}/${total} — SHOOT`;
                this.isProcessing = false;
            } else {
                this.shotLabel    = 'DONE ✓';
                this.isProcessing = false;
            }

            this.$nextTick(() => this.renderStrip());
        },

        // ── Setters
        setLayout(l) {
            this.activeLayout = l;
            this.resetSession();
        },

        setFilter(f) {
            this.activeFilter = f;
            this.$nextTick(() => this.renderStrip());
        },

        setFrame(f) {
            this.activeFrame = f;
            this.$nextTick(() => this.renderStrip());
        },

        // ── Reset
        resetSession() {
            this.capturedImages = [];
            this.shotLabel      = 'READY';
            this.$nextTick(() => this.renderStrip());
        },

        // ── Render strip preview
        renderStrip() {
            const wraps = document.querySelectorAll('.pb-strip-wrap');
            if (!wraps.length) return;

            const fr      = this.frames.find(f => f.id === this.activeFrame) ?? this.frames[0];
            const max     = this.maxCaptures;
            const filterV = this.filterCSS[this.activeFilter] ?? '';
            const filterStyle = filterV ? `filter:${filterV};` : '';
            const dateStr = new Date().toLocaleDateString('en-GB', { day:'2-digit', month:'2-digit', year:'2-digit' });
            const bgStyle = fr.bg.startsWith('linear') ? `background:${fr.bg};` : `background-color:${fr.bg};`;

            const imgOrSlot = (i, w, h) => {
                if (this.capturedImages[i]) {
                    return `<img src="${this.capturedImages[i]}" style="width:100%;height:100%;object-fit:cover;display:block;${filterStyle}" />`;
                }
                return `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.1);"><span style="font-family:'Space Mono',monospace;font-size:11px;color:${fr.color}40;">${i+1}</span></div>`;
            };

            const footer = `
                <div style="display:flex;flex-direction:column;align-items:center;gap:3px;padding:8px 12px 12px;">
                    <div style="width:100%;height:1px;background:${fr.color}20;margin-bottom:4px;"></div>
                    <span style="font-family:'DM Serif Display',serif;font-size:11px;letter-spacing:0.2em;text-transform:uppercase;color:${fr.color};opacity:0.8;">failerrybooth</span>
                    <span style="font-family:'Space Mono',monospace;font-size:9px;letter-spacing:0.08em;color:${fr.color};opacity:0.4;">${dateStr}</span>
                </div>`;

            let innerHTML = '';
            let containerW = 0;

            if (this.activeLayout === 'scattered') {
                const rotations = [-8, 5, -4, 7];
                const offsets   = [{x:4,y:0},{x:-4,y:6},{x:6,y:4},{x:-2,y:8}];
                const iW = 90, iH = 120;
                containerW = 200;
                const scatH = 200;
                let slots = '';
                for (let i = max-1; i >= 0; i--) {
                    const r = rotations[i] ?? 0;
                    const o = offsets[i] ?? {x:0,y:0};
                    const left = 20 + i * 22 + o.x;
                    const top  = 20 + o.y;
                    slots += `<div style="position:absolute;left:${left}px;top:${top}px;width:${iW}px;height:${iH}px;border-radius:2px;overflow:hidden;transform:rotate(${r}deg);box-shadow:0 4px 16px rgba(0,0,0,0.35),0 0 0 1px rgba(255,255,255,0.08);transition:all 0.4s;">${imgOrSlot(i,iW,iH)}</div>`;
                }
                innerHTML = `<div style="position:relative;width:${containerW}px;height:${scatH}px;">${slots}</div><div style="width:${containerW}px;">${footer}</div>`;
                const finalHTML = `<div style="width:${containerW}px;${bgStyle}border-radius:4px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,0.5);">${innerHTML}</div>`;
                wraps.forEach(w => w.innerHTML = finalHTML);
                return;

            } else if (this.activeLayout === 'overlap') {
                const iW = 100, iH = 133;
                containerW = 140;
                let slots = '';
                for (let i = 0; i < max; i++) {
                    const topOffset = i * 38;
                    slots += `<div style="position:absolute;left:10px;top:${topOffset}px;width:${iW}px;height:${iH}px;border-radius:2px;overflow:hidden;box-shadow:0 6px 20px rgba(0,0,0,0.4),0 0 0 1px rgba(255,255,255,0.07);z-index:${i};">${imgOrSlot(i,iW,iH)}</div>`;
                }
                const totalH = iH + (max-1)*38;
                innerHTML = `<div style="position:relative;width:${containerW}px;height:${totalH+16}px;">${slots}</div>`;
                const finalHTML = `<div style="width:${containerW}px;${bgStyle}border-radius:4px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,0.5);">${innerHTML}${footer}</div>`;
                wraps.forEach(w => w.innerHTML = finalHTML);
                return;

            } else if (this.activeLayout === 'collage') {
                const bigW=110, bigH=160, smallW=72, smallH=77, gap=4, pad=10;
                containerW = pad*2 + bigW + gap + smallW;
                innerHTML = `<div style="display:flex;gap:${gap}px;padding:${pad}px ${pad}px 6px;"><div style="width:${bigW}px;height:${bigH}px;border-radius:2px;overflow:hidden;flex-shrink:0;box-shadow:0 4px 16px rgba(0,0,0,0.3);">${imgOrSlot(0,bigW,bigH)}</div><div style="display:flex;flex-direction:column;gap:${gap}px;flex:1;"><div style="width:${smallW}px;height:${smallH}px;border-radius:2px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.3);">${imgOrSlot(1,smallW,smallH)}</div><div style="width:${smallW}px;height:${smallH}px;border-radius:2px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.3);">${imgOrSlot(2,smallW,smallH)}</div></div></div>`;
                const finalHTML = `<div style="width:${containerW}px;${bgStyle}border-radius:4px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,0.5);">${innerHTML}${footer}</div>`;
                wraps.forEach(w => w.innerHTML = finalHTML);
                return;

            } else if (this.activeLayout === 'diagonal') {
                const iW=100, iH=133, offsetX=18, offsetY=14, pad=12;
                containerW = pad*2 + iW + offsetX*(max-1);
                const totalH = pad + iH + offsetY*(max-1) + 16;
                let slots = '';
                for (let i = 0; i < max; i++) {
                    slots += `<div style="position:absolute;left:${pad + i*offsetX}px;top:${pad/2 + i*offsetY}px;width:${iW}px;height:${iH}px;border-radius:2px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.35);z-index:${i};">${imgOrSlot(i,iW,iH)}</div>`;
                }
                innerHTML = `<div style="position:relative;width:${containerW}px;height:${totalH}px;">${slots}</div>`;
                const finalHTML = `<div style="width:${containerW}px;${bgStyle}border-radius:4px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,0.5);">${innerHTML}${footer}</div>`;
                wraps.forEach(w => w.innerHTML = finalHTML);
                return;

            } else if (this.activeLayout === 'zine') {
                const topW=176, topH=110, botW=84, botH=90, gap=4, pad=10;
                containerW = pad*2 + topW;
                innerHTML = `<div style="padding:${pad}px ${pad}px 6px;display:flex;flex-direction:column;gap:${gap}px;"><div style="width:${topW}px;height:${topH}px;border-radius:2px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.3);">${imgOrSlot(0,topW,topH)}</div><div style="display:flex;gap:${gap}px;"><div style="width:${botW}px;height:${botH}px;border-radius:2px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.3);">${imgOrSlot(1,botW,botH)}</div><div style="width:${botW}px;height:${botH}px;border-radius:2px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.3);">${imgOrSlot(2,botW,botH)}</div></div></div>`;
                const finalHTML = `<div style="width:${containerW}px;${bgStyle}border-radius:4px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,0.5);">${innerHTML}${footer}</div>`;
                wraps.forEach(w => w.innerHTML = finalHTML);
                return;

            } else if (this.activeLayout === 'stack') {
                const rotations = [3, -2, 1, -4];
                const iW=100, iH=133, pad=20;
                containerW = 160;
                let slots = '';
                for (let i = max-1; i >= 0; i--) {
                    const r = rotations[i] ?? 0;
                    slots += `<div style="position:absolute;left:${pad + i*2}px;top:${pad + i*2}px;width:${iW}px;height:${iH}px;border-radius:2px;overflow:hidden;transform:rotate(${r}deg);box-shadow:0 4px 16px rgba(0,0,0,0.4);z-index:${i};">${imgOrSlot(i,iW,iH)}</div>`;
                }
                innerHTML = `<div style="position:relative;width:${containerW}px;height:${iH+pad*2+10}px;">${slots}</div>`;
                const finalHTML = `<div style="width:${containerW}px;${bgStyle}border-radius:4px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,0.5);">${innerHTML}${footer}</div>`;
                wraps.forEach(w => w.innerHTML = finalHTML);
                return;
            }

            // ── fallback: single, double, trio, strip, grid
            const isGrid  = this.activeLayout === 'grid';
            const photoW  = isGrid ? 110 : 100;
            const photoH  = Math.round(photoW * (4/3));
            const pad     = 12, gap = 6;
            containerW = isGrid ? pad*2 + photoW*2 + gap : pad*2 + photoW;

            let slotsHTML = '';
            for (let i = 0; i < max; i++) {
                if (this.capturedImages[i]) {
                    slotsHTML += `<div style="width:${photoW}px;height:${photoH}px;border-radius:2px;overflow:hidden;flex-shrink:0;"><img src="${this.capturedImages[i]}" style="width:100%;height:100%;object-fit:cover;display:block;${filterStyle}" /></div>`;
                } else {
                    slotsHTML += `<div style="width:${photoW}px;height:${photoH}px;border-radius:2px;background:rgba(0,0,0,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><span style="font-family:'Space Mono',monospace;font-size:14px;letter-spacing:0.05em;color:${fr.color}30;">${i+1}</span></div>`;
                }
            }

            const gridOrFlex = isGrid
                ? `display:grid;grid-template-columns:1fr 1fr;gap:${gap}px;padding:${pad}px;`
                : `display:flex;flex-direction:column;gap:${gap}px;padding:${pad}px;`;

            const finalHTML = `<div style="width:${containerW}px;${bgStyle}border-radius:3px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,0.5),0 0 0 1px rgba(255,255,255,0.06);transition:all 0.4s;"><div style="${gridOrFlex}">${slotsHTML}</div>${footer}</div>`;
            wraps.forEach(wrap => { wrap.innerHTML = finalHTML; });
        },

        // ── Download
        async downloadStrip() {
            if (!this.capturedImages.length) return;
            const blob = await this.generateStripBlob();
            const url = URL.createObjectURL(blob);
            const a   = document.createElement('a');
            a.href    = url;
            a.download = `failerrybooth-${this.activeFrame}-${Date.now()}.jpg`;
            a.click();
        },

        // ── Save to backend
        async saveToFailerry() {
            @if(!Auth::check())
                window.location.href = "{{ route('login') }}";
                return;
            @endif

            if (this.capturedImages.length !== this.maxCaptures) return;
            this.isSaving = true;

            try {
                const blob     = await this.generateStripBlob();
                const formData = new FormData();
                formData.append('image[]', blob, 'photobooth.jpg');
                formData.append('title', 'Photobooth Moment');
                formData.append('description', 'Captured with Failerry Photobooth #photobooth');

                const response = await axios.post("{{ route('photos.store') }}", formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });

                window.showToast?.('Berhasil diproses!');
                const photoUid = response.data.photos[0].uid;
                window.location.href = `/photo/${photoUid}/edit`;
            } catch (err) {
                console.error(err);
                window.showToast?.('Gagal menyimpan foto.', 'error');
            } finally {
                this.isSaving = false;
            }
        },

        // ── Generate canvas blob for upload/download with ALL layouts support
        async generateStripBlob() {
            const c   = document.createElement('canvas');
            const ctx = c.getContext('2d');
            const fr  = this.frames.find(f => f.id === this.activeFrame) ?? this.frames[0];
            const max = this.maxCaptures;
            const S   = 4; // Scale factor for hi-res

            // Load images
            const imgs = [];
            for (let i = 0; i < max; i++) {
                if (!this.capturedImages[i]) continue;
                const img = new Image();
                img.src = this.capturedImages[i];
                await new Promise(r => img.onload = r);
                imgs.push(img);
            }

            let cW = 0, cH = 0;
            const topDec = 40 * S, btmDec = 60 * S;
            
            // Map layouts to their dimensions
            let drawOps = []; // { i, x, y, w, h, r }

            if (this.activeLayout === 'scatter') {
                const iW = 100 * S, iH = 133 * S;
                cW = 160 * S;
                cH = 200 * S;
                const rotations = [3, -2, 1, -4];
                const offsets = [{x:5,y:5}, {x:-8,y:10}, {x:4,y:-5}, {x:-6,y:12}];
                for (let i = 0; i < max; i++) {
                    const o = offsets[i] ?? {x:0,y:0};
                    drawOps.push({ i, x: (20 + i*22 + o.x)*S, y: (20 + o.y)*S, w: iW, h: iH, r: rotations[i]||0 });
                }
            } else if (this.activeLayout === 'overlap') {
                const iW = 100 * S, iH = 133 * S;
                cW = 140 * S;
                cH = iH + (max-1)*38*S + 16*S;
                for (let i = 0; i < max; i++) {
                    drawOps.push({ i, x: 10*S, y: i*38*S, w: iW, h: iH, r: 0 });
                }
            } else if (this.activeLayout === 'collage') {
                const bigW = 110*S, bigH = 160*S, smallW = 72*S, smallH = 77*S, gap = 4*S, pad = 10*S;
                cW = pad*2 + bigW + gap + smallW;
                cH = pad*2 + bigH;
                drawOps.push({ i: 0, x: pad, y: pad, w: bigW, h: bigH, r: 0 });
                drawOps.push({ i: 1, x: pad+bigW+gap, y: pad, w: smallW, h: smallH, r: 0 });
                drawOps.push({ i: 2, x: pad+bigW+gap, y: pad+smallH+gap, w: smallW, h: smallH, r: 0 });
            } else if (this.activeLayout === 'diagonal') {
                const iW = 100*S, iH = 133*S, offsetX = 18*S, offsetY = 14*S, pad = 12*S;
                cW = pad*2 + iW + offsetX*(max-1);
                cH = pad + iH + offsetY*(max-1) + 16*S;
                for (let i = 0; i < max; i++) {
                    drawOps.push({ i, x: pad + i*offsetX, y: pad/2 + i*offsetY, w: iW, h: iH, r: 0 });
                }
            } else if (this.activeLayout === 'zine') {
                const topW = 176*S, topH = 110*S, botW = 84*S, botH = 90*S, gap = 4*S, pad = 10*S;
                cW = pad*2 + topW;
                cH = pad*2 + topH + gap + botH;
                drawOps.push({ i: 0, x: pad, y: pad, w: topW, h: topH, r: 0 });
                drawOps.push({ i: 1, x: pad, y: pad+topH+gap, w: botW, h: botH, r: 0 });
                drawOps.push({ i: 2, x: pad+botW+gap, y: pad+topH+gap, w: botW, h: botH, r: 0 });
            } else if (this.activeLayout === 'stack') {
                const rotations = [3, -2, 1, -4];
                const iW = 100*S, iH = 133*S, pad = 20*S;
                cW = 160*S;
                cH = iH + pad*2 + 10*S;
                for (let i = 0; i < max; i++) {
                    drawOps.push({ i, x: pad + i*2*S, y: pad + i*2*S, w: iW, h: iH, r: rotations[i]||0 });
                }
            } else {
                // fallback: single, double, trio, strip, grid
                const isGrid = this.activeLayout === 'grid';
                const photoW = (isGrid ? 110 : 100) * S;
                const photoH = Math.round(photoW * (4/3));
                const pad = 12*S, gap = 6*S;
                cW = isGrid ? pad*2 + photoW*2 + gap : pad*2 + photoW;
                cH = isGrid ? pad*2 + photoH*2 + gap : pad*2 + photoH*max + gap*(max-1);
                
                for (let i = 0; i < max; i++) {
                    let x = pad, y = pad;
                    if (isGrid) {
                        x = pad + (i % 2) * (photoW + gap);
                        y = pad + Math.floor(i / 2) * (photoH + gap);
                    } else {
                        y = pad + i * (photoH + gap);
                    }
                    drawOps.push({ i, x, y, w: photoW, h: photoH, r: 0 });
                }
            }

            // Apply decorations
            c.width  = cW;
            c.height = cH + topDec + btmDec;

            // Background
            if (fr.bg.startsWith('linear')) {
                const g = ctx.createLinearGradient(0, 0, 0, c.height);
                g.addColorStop(0, '#ffe4ef'); g.addColorStop(1, '#ffd4e4');
                ctx.fillStyle = g;
            } else {
                ctx.fillStyle = fr.bg;
            }
            ctx.fillRect(0, 0, c.width, c.height);

            const filterMap = {
                vintage: 'sepia(0.25) contrast(1.1) brightness(0.92)',
                bw:      'grayscale(1) contrast(1.15)',
                sepia:   'sepia(0.9)',
                dreamy:  'brightness(1.08) saturate(1.3)',
                faded:   'saturate(0.5) brightness(1.05)',
                lomo:    'contrast(1.5) saturate(1.6) brightness(0.85)',
                golden:  'sepia(0.4) saturate(1.4) brightness(1.05) hue-rotate(-10deg)',
                cool:    'saturate(0.9) brightness(1.02) hue-rotate(190deg) contrast(1.05)',
                fade35:  'sepia(0.3) saturate(0.75) brightness(1.1) contrast(0.88)',
                mist:    'saturate(0.4) brightness(1.12) contrast(0.85)',
                velvia:  'saturate(1.8) contrast(1.2) brightness(0.9)',
                portra:  'sepia(0.15) saturate(1.1) brightness(1.05) contrast(0.95) hue-rotate(5deg)',
                cross:   'saturate(1.4) hue-rotate(30deg) contrast(1.15) brightness(0.95)',
            };

            // Draw images
            for (const op of drawOps) {
                if (!imgs[op.i]) continue;
                ctx.save();
                // Move context to the center of the image to rotate it properly
                ctx.translate(op.x + op.w/2, op.y + topDec + op.h/2);
                ctx.rotate(op.r * Math.PI / 180);
                
                if (filterMap[this.activeFilter]) {
                    ctx.filter = filterMap[this.activeFilter];
                }
                
                // Draw drop shadow if it's a scatter/stack/overlap etc
                if (['scatter', 'overlap', 'diagonal', 'stack', 'collage', 'zine'].includes(this.activeLayout)) {
                    ctx.shadowColor = 'rgba(0,0,0,0.3)';
                    ctx.shadowBlur = 16 * (S/2);
                    ctx.shadowOffsetY = 4 * (S/2);
                }

                // Draw image offset by half width/height because of translate
                ctx.drawImage(imgs[op.i], -op.w/2, -op.h/2, op.w, op.h);
                ctx.restore();
            }

            // Draw branding text
            ctx.fillStyle = fr.color ?? '#2a2018';
            ctx.font      = `italic ${18*S}px Georgia, serif`;
            ctx.textAlign = 'center';
            ctx.fillText('failerrybooth', c.width / 2, c.height - 25*S);
            ctx.font      = `${11*S}px monospace`;
            ctx.globalAlpha = 0.5;
            ctx.fillText(new Date().toLocaleDateString(), c.width / 2, c.height - 10*S);
            ctx.globalAlpha = 1;

            return new Promise(resolve => c.toBlob(resolve, 'image/jpeg', 0.92));
        },
    };
}
</script>


{{-- ══════════════════════════════════════
     STYLES
══════════════════════════════════════ --}}
<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&family=DM+Serif+Display:ital@0;1&family=Instrument+Sans:wght@400;500;600&display=swap');

/* ── Root & Reset ──────────────────────────── */
.photobooth-root {
    position: relative;
    min-height: 100vh;
    background: #1a1714;
    color: #f5f0e8;
    font-family: 'Instrument Sans', sans-serif;
    overflow-x: hidden;
}

/* ── Sprocket film strips ──────────────────── */
.sprocket-strip {
    position: fixed;
    top: 0; bottom: 0;
    width: 32px;
    background: #0f0d0b;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding: 10px 0;
    gap: 16px;
    z-index: 50;
}
@media (max-width: 1023px) {
    .sprocket-strip { display: none !important; }
}
.sprocket-left  { left: 0;  border-right: 1px solid rgba(255,255,255,0.05); }
.sprocket-right { right: 0; border-left:  1px solid rgba(255,255,255,0.05); }
.pb-sprocket-hole {
    width: 15px; height: 11px;
    border-radius: 2px;
    background: #242018;
    border: 1px solid rgba(255,255,255,0.07);
    flex-shrink: 0;
}

/* ── Main wrapper ──────────────────────────── */
.pb-main {
    margin: 0 32px;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
@media (max-width: 1023px) {
    .pb-main { margin: 0; }
}

/* ── Top bar ───────────────────────────────── */
.pb-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px 14px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.pb-back-btn {
    color: #8a8278;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    width: 32px;
    height: 32px;
    margin-left: -8px;
}
.pb-back-btn:hover {
    color: #f5f0e8;
    transform: translateX(-2px);
}
.pb-frame-counter {
    font-family: 'Space Mono', monospace;
    font-size: 14px;
    color: #8a8278;
    letter-spacing: 0.12em;
    display: flex;
    align-items: center;
    gap: 8px;
}
.pb-frame-counter::before {
    content: '';
    display: inline-block;
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #c9363a;
    animation: pb-blink 1.8s ease-in-out infinite;
}
@keyframes pb-blink { 0%,100%{opacity:1} 50%{opacity:0.15} }

.pb-iso-badge {
    font-family: 'Space Mono', monospace;
    font-size: 12px;
    color: #c8a96e;
    background: rgba(200,169,110,0.1);
    border: 1px solid rgba(200,169,110,0.2);
    padding: 4px 10px;
    border-radius: 3px;
    letter-spacing: 0.15em;
}

/* ── Studio ────────────────────────────────── */
.pb-studio {
    display: grid;
    grid-template-columns: 1fr 380px;
    flex: 1;
}

/* ── Camera section ────────────────────────── */
.pb-camera-section {
    padding: 24px;
    border-right: 1px solid rgba(255,255,255,0.06);
    display: flex;
    flex-direction: column;
    gap: 32px;
    align-items: center;
}
.pb-viewport {
    position: relative;
    width: 100%;
    max-width: 480px;
    aspect-ratio: 3/4;
    background: #0d0b09;
    border-radius: 4px;
    overflow: hidden;
    box-shadow:
        0 0 0 1px rgba(255,255,255,0.07),
        0 20px 60px rgba(0,0,0,0.6),
        inset 0 0 40px rgba(0,0,0,0.4);
}
.pb-video {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
}

/* Overlay elements */
.pb-overlay   { position: absolute; inset: 0; pointer-events: none; }
.pb-vignette  {
    position: absolute; inset: 0;
    background: radial-gradient(ellipse at center, transparent 35%, rgba(0,0,0,0.75) 100%);
}
.pb-corners   { position: absolute; inset: 12px; }
.pb-corner    {
    position: absolute;
    width: 18px; height: 18px;
    border-color: rgba(200,169,110,0.55);
    border-style: solid;
}
.pb-corner.tl { top:0; left:0;  border-width: 1.5px 0 0 1.5px; }
.pb-corner.tr { top:0; right:0; border-width: 1.5px 1.5px 0 0; }
.pb-corner.bl { bottom:0; left:0;  border-width: 0 0 1.5px 1.5px; }
.pb-corner.br { bottom:0; right:0; border-width: 0 1.5px 1.5px 0; }

.pb-hud {
    position: absolute;
    bottom: 14px; left: 0; right: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 16px;
    font-family: 'Space Mono', monospace;
    font-size: 12px;
    color: rgba(200,169,110,0.65);
    letter-spacing: 0.1em;
}
.pb-hud-dots { display: flex; gap: 5px; }
.pb-hud-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    border: 1px solid rgba(200,169,110,0.35);
    transition: all 0.3s;
}
.pb-hud-dot.filled {
    background: #c8a96e;
    border-color: #c8a96e;
    box-shadow: 0 0 8px rgba(200,169,110,0.5);
}

/* Flash */
.pb-flash {
    position: absolute; inset: 0;
    background: white;
    opacity: 0;
    pointer-events: none;
    z-index: 10;
    transition: opacity 0.06s;
}
.pb-flash.active { opacity: 1; }

/* Camera shake */
@keyframes pb-cam-shake {
    0%,100% { transform: translate(0,0) rotate(0deg); }
    15%     { transform: translate(-5px,3px) rotate(-1.5deg); }
    30%     { transform: translate(5px,-3px) rotate(1.5deg); }
    55%     { transform: translate(-3px,3px) rotate(-0.6deg); }
    75%     { transform: translate(3px,-1px) rotate(0.4deg); }
}
.cam-shake { animation: pb-cam-shake 0.38s ease-out; }

/* Camera error */
.pb-cam-error {
    position: absolute; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    background: rgba(0,0,0,0.85);
    gap: 10px; text-align: center; padding: 20px;
    z-index: 20;
}
.pb-error-icon {
    width: 48px; height: 48px;
    border-radius: 50%;
    background: rgba(201,54,58,0.15);
    border: 1px solid rgba(201,54,58,0.4);
    display: flex; align-items: center; justify-content: center;
    color: #c9363a;
}
.pb-cam-error h3 {
    font-family: 'DM Serif Display', serif;
    font-size: 15px; color: #f5f0e8;
}
.pb-cam-error p {
    font-size: 14px; color: #8a8278; line-height: 1.6; max-width: 280px;
}
.pb-cam-error button {
    background: #f5f0e8; color: #1a1714; border: none;
    padding: 8px 20px; border-radius: 3px;
    font-family: 'Space Mono', monospace;
    font-size: 12px; letter-spacing: 0.12em; text-transform: uppercase;
    cursor: pointer; margin-top: 4px;
    transition: background 0.2s;
}
.pb-cam-error button:hover { background: white; }

/* Camera loading */
.pb-cam-loading {
    position: absolute; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    background: #0d0b09;
    gap: 12px; z-index: 25;
}
.pb-loading-spin {
    width: 24px; height: 24px;
    border: 2px solid rgba(200,169,110,0.15);
    border-top-color: #c8a96e;
    border-radius: 50%;
    animation: pb-spin 0.8s linear infinite;
}
.pb-cam-loading span {
    font-family: 'Space Mono', monospace;
    font-size: 10px; color: #8a8278;
    letter-spacing: 0.2em;
}
@keyframes pb-spin { to { transform: rotate(360deg); } }

/* Flip button */
.pb-flip-btn {
    position: absolute; top: 12px; right: 12px;
    width: 36px; height: 36px;
    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,0.8);
    cursor: pointer; z-index: 15;
    transition: all 0.2s;
}
.pb-flip-btn:hover  { background: rgba(0,0,0,0.65); color: white; }
.pb-flip-btn:active { transform: scale(0.9); }
.pb-flip-btn:disabled { opacity: 0.3; cursor: not-allowed; }
.pb-flip-btn.switching { opacity: 0.6; cursor: wait; }

/* Torch button */
.pb-torch-btn {
    position: absolute; top: 12px; left: 12px;
    width: 36px; height: 36px;
    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,0.8);
    cursor: pointer; z-index: 15;
    transition: all 0.2s;
}
.pb-torch-btn.active {
    background: #c8a96e;
    color: #1a1714;
    border-color: #f0d090;
    box-shadow: 0 0 15px rgba(200,169,110,0.5);
}

/* Mini Gallery (Captured Images) */
.pb-mini-gallery {
    padding: 0 24px 20px;
    width: 100%;
    max-width: 520px;
}
.pb-gallery-label {
    font-family: 'Space Mono', monospace;
    font-size: 10px; color: #8a8278;
    letter-spacing: 0.15em; margin-bottom: 10px;
}
.pb-gallery-scroll {
    display: flex; gap: 12px;
    overflow-x: auto; padding-bottom: 8px;
    scrollbar-width: none;
}
.pb-gallery-scroll::-webkit-scrollbar { display: none; }
.pb-gallery-item {
    position: relative;
    flex-shrink: 0;
    width: 70px; aspect-ratio: 3/4;
    border-radius: 4px; overflow: hidden;
    background: #0d0b09;
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    border: 1px solid rgba(255,255,255,0.1);
}
.pb-gallery-item img {
    width: 100%; height: 100%; object-fit: cover;
}
.pb-item-del {
    position: absolute; top: 4px; right: 4px;
    width: 20px; height: 20px;
    background: rgba(201,54,58,0.85);
    color: white; border: none; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; opacity: 0; transition: opacity 0.2s;
}
.pb-gallery-item:hover .pb-item-del { opacity: 1; }

/* ── Desktop shutter row ───────────────────── */
.pb-capture-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 24px;
    width: 100%;
    max-width: 520px;
}
.pb-reset-btn {
    background: none;
    border: 1px solid rgba(255,255,255,0.14);
    color: #8a8278;
    font-family: 'Space Mono', monospace;
    font-size: 12px; letter-spacing: 0.14em;
    padding: 8px 14px; border-radius: 3px;
    cursor: pointer; text-transform: uppercase;
    transition: all 0.2s;
}
.pb-reset-btn:hover { border-color: rgba(255,255,255,0.3); color: #f5f0e8; }

.pb-shutter-btn {
    width: 90px; height: 90px;
    border-radius: 50%;
    background: none;
    border: 2px solid rgba(255,255,255,0.18);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.18s;
}
.pb-shutter-btn:hover  { border-color: rgba(255,255,255,0.45); transform: scale(1.04); }
.pb-shutter-btn:active { transform: scale(0.93); }
.pb-shutter-btn:disabled { opacity: 0.3; cursor: not-allowed; transform: none; }
.pb-shutter-inner {
    width: 64px; height: 64px;
    border-radius: 50%;
    background: #f5f0e8;
    transition: all 0.12s;
}
.pb-shutter-btn:hover .pb-shutter-inner { background: white; }
.pb-shutter-btn:active .pb-shutter-inner { transform: scale(0.9); }

.pb-shot-label {
    font-family: 'Space Mono', monospace;
    font-size: 13px; color: #8a8278;
    letter-spacing: 0.1em;
    min-width: 60px; text-align: center;
}

/* ── Sidebar ───────────────────────────────── */
.pb-sidebar {
    display: flex;
    flex-direction: column;
    border-left: 1px solid rgba(255,255,255,0.06);
    min-height: 100%;
}
.pb-panel-section {
    padding: 18px 18px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.pb-panel-label {
    font-family: 'Space Mono', monospace;
    font-size: 12px; letter-spacing: 0.2em;
    color: #8a8278; text-transform: uppercase;
    margin-bottom: 12px;
    display: flex; align-items: center; gap: 8px;
}
.pb-panel-label::after {
    content: ''; flex: 1; height: 1px;
    background: rgba(255,255,255,0.05);
}

/* Layout grid */
.pb-layout-grid {
    display: grid;
    grid-template-columns: repeat(5,1fr);
    gap: 5px;
}
.pb-layout-btn {
    aspect-ratio: 1;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 4px;
    cursor: pointer;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 2px; padding: 6px 5px 7px;
    transition: all 0.18s;
}
.pb-layout-btn:hover { background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.18); }
.pb-layout-btn.active {
    background: rgba(201,54,58,0.12);
    border-color: rgba(201,54,58,0.5);
}
.pb-layout-btn.active .pb-dynamic-icon { color: #c9363a; }
.pb-layout-name {
    font-family: 'Space Mono', monospace;
    font-size: 9px; color: #8a8278;
    letter-spacing: 0.08em; text-transform: uppercase; margin-top: 1px;
}
.pb-layout-btn.active .pb-layout-name { color: #c9363a; }

/* Filter strip */
.pb-filter-scroll {
    display: flex; gap: 7px;
    overflow-x: auto; padding-bottom: 2px;
    scrollbar-width: none;
}
.pb-filter-scroll::-webkit-scrollbar { display: none; }
.pb-filter-btn {
    flex-shrink: 0; width: 52px;
    display: flex; flex-direction: column;
    align-items: center; gap: 5px;
    background: none; border: none; cursor: pointer; padding: 0;
}
.pb-filter-swatch {
    width: 52px; height: 38px;
    border-radius: 3px;
    border: 1.5px solid transparent;
    transition: all 0.2s;
}
.pb-filter-btn.active .pb-filter-swatch {
    border-color: #c8a96e;
    box-shadow: 0 0 10px rgba(200,169,110,0.3);
}
.pb-filter-label {
    font-family: 'Space Mono', monospace;
    font-size: 10px; color: #8a8278;
    letter-spacing: 0.08em; text-transform: uppercase;
}
.pb-filter-btn.active .pb-filter-label { color: #c8a96e; }

/* Frame strip */
.pb-frame-scroll {
    display: flex; gap: 7px;
    overflow-x: auto; padding-bottom: 2px;
    scrollbar-width: none;
}
.pb-frame-scroll::-webkit-scrollbar { display: none; }
.pb-frame-btn {
    flex-shrink: 0; width: 42px;
    display: flex; flex-direction: column;
    align-items: center; gap: 5px;
    background: none; border: none; cursor: pointer; padding: 0;
}
.pb-frame-thumb {
    width: 42px; height: 50px;
    border-radius: 3px;
    border: 1.5px solid rgba(255,255,255,0.1);
    transition: all 0.2s;
}
.pb-frame-btn.active .pb-frame-thumb {
    border-color: #c8a96e;
    box-shadow: 0 0 10px rgba(200,169,110,0.22);
}
.pb-frame-label {
    font-family: 'Space Mono', monospace;
    font-size: 10px; color: #8a8278;
    letter-spacing: 0.06em; text-transform: uppercase;
}
.pb-frame-btn.active .pb-frame-label { color: #c8a96e; }

/* Strip section */
.pb-strip-section {
    flex: 1; padding: 18px;
    display: flex; flex-direction: column; align-items: center;
    overflow: hidden;
}
.pb-strip-wrap { transition: all 0.4s; }

/* Actions */
.pb-action-panel {
    padding: 14px 18px;
    border-top: 1px solid rgba(255,255,255,0.06);
}
.pb-action-row { display: flex; gap: 8px; }

.pb-btn-primary {
    flex: 1;
    padding: 12px 16px;
    background: #c9363a; color: white; border: none;
    border-radius: 3px;
    font-family: 'Space Mono', monospace;
    font-size: 13px; letter-spacing: 0.15em; text-transform: uppercase;
    cursor: pointer; transition: all 0.18s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.pb-btn-primary:hover   { background: #d94044; }
.pb-btn-primary:active  { transform: scale(0.97); }
.pb-btn-primary:disabled { opacity: 0.3; cursor: not-allowed; }

.pb-btn-secondary {
    padding: 12px 14px;
    background: rgba(255,255,255,0.05);
    color: #8a8278; border: 1px solid rgba(255,255,255,0.1);
    border-radius: 3px;
    font-family: 'Space Mono', monospace;
    font-size: 13px; letter-spacing: 0.1em; text-transform: uppercase;
    cursor: pointer; transition: all 0.18s;
}
.pb-btn-secondary:hover   { background: rgba(255,255,255,0.1); color: #f5f0e8; }
.pb-btn-secondary:disabled { opacity: 0.3; cursor: not-allowed; }

.pb-btn-icon {
    width: 44px; height: 44px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 3px;
    display: flex; align-items: center; justify-content: center;
    color: #8a8278; cursor: pointer; transition: all 0.18s; flex-shrink: 0;
}
.pb-btn-icon:hover { background: rgba(255,255,255,0.1); color: #f5f0e8; }

.pb-spin { animation: pb-spin 1s linear infinite; }

/* ── Mobile bottom panel ───────────────────── */
.pb-mobile-panel {
    position: fixed; bottom: 0; left: 0; right: 0;
    background: rgba(20,18,16,0.98);
    backdrop-filter: blur(20px);
    border-top: 1px solid rgba(255,255,255,0.08);
    padding: 0 0 max(12px, env(safe-area-inset-bottom)) 0;
    z-index: 40;
}
.pb-mobile-tabs {
    display: flex;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    padding: 0 4px;
}
.pb-mobile-tab {
    flex: 1; padding: 12px 8px;
    background: none; border: none;
    font-family: 'Space Mono', monospace;
    font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase;
    color: rgba(255,255,255,0.3); cursor: pointer;
    position: relative; transition: color 0.2s;
}
.pb-mobile-tab.active { color: #f5f0e8; }
.pb-mobile-tab.active::after {
    content: ''; position: absolute;
    bottom: -1px; left: 50%; transform: translateX(-50%);
    width: 28px; height: 1.5px;
    background: #c9363a; border-radius: 1px;
}
.pb-mobile-scroll {
    display: flex; gap: 10px; overflow-x: auto;
    padding: 12px 16px 8px; scrollbar-width: none;
}
.pb-mobile-scroll::-webkit-scrollbar { display: none; }
.pb-mobile-layout-btn {
    flex-shrink: 0;
    display: flex; flex-direction: column;
    align-items: center; gap: 6px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    padding: 10px 12px 8px;
    color: rgba(255,255,255,0.5);
    cursor: pointer; transition: all 0.18s; min-width: 52px;
}
.pb-mobile-layout-btn:hover { background: rgba(255,255,255,0.08); }
.pb-mobile-layout-btn.active {
    background: rgba(201,54,58,0.12);
    border-color: rgba(201,54,58,0.45);
    color: #c9363a;
}
.pb-mobile-layout-icon { width: 22px; }
.pb-mobile-submit {
    display: flex; gap: 8px;
    padding: 10px 16px 4px;
    border-top: 1px solid rgba(255,255,255,0.06);
}

/* ── Mobile overrides ──────────────────────── */
@media (max-width: 1023px) {
    .pb-studio   { display: flex; flex-direction: column; }
    .pb-main     { margin: 0; padding-bottom: 240px; }
    .pb-camera-section {
        padding: 12px 14px;
        border-right: none;
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .pb-viewport { max-width: 100%; border-radius: 12px; }
    .pb-sidebar { display: none !important; }
    .pb-mobile-preview-area {
        padding: 20px 14px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
}

/* ── Utility ───────────────────────────────── */
[x-cloak] { display: none !important; }
nav { display: none !important; }
#main-navbar { display: none !important; }
body {
    background-color: #1a1714 !important;
    margin: 0 !important;
    padding: 0 !important;
}
main {
    padding-top: 0 !important;
    margin-top: 0 !important;
}
#app {
    margin-top: 0 !important;
    padding-top: 0 !important;
}
</style>

@endsection