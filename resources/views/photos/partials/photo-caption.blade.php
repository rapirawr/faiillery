<div class="space-y-3">
    @if($photo->description || $photo->title)
    <div class="space-y-1.5">
        @if($photo->title)
        <h1 class="text-base font-bold text-cocoa dark:text-white leading-snug tracking-tight">
            {{ $photo->title }}
        </h1>
        @endif

        @if($photo->description)
        <div x-data="{ expanded: false, desc: {{ json_encode($photo->description) }}, limit: 140 }}">
            <p class="text-sm text-cocoa/80 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">
                <span x-show="!expanded && desc.length > limit" x-text="desc.substring(0, limit) + '...'"></span>
                <span x-show="expanded || desc.length <= limit" x-text="desc"></span>
                <button x-show="!expanded && desc.length > limit"
                        @click.stop="expanded = true"
                        class="text-brown dark:text-caramel font-semibold text-xs ml-1 hover:underline">selengkapnya</button>
            </p>
        </div>
        @endif
    </div>
    @endif

    {{-- Tags --}}
    @if($photo->tags->count() > 0)
    <div class="flex flex-wrap gap-1.5 pt-1">
        @foreach($photo->tags as $tag)
            <a href="{{ route('search', ['tag' => $tag->slug]) }}"
               class="px-2.5 py-1 bg-sand/30 dark:bg-white/10 hover:bg-brown hover:text-white text-brown dark:text-caramel text-xs font-medium rounded-full transition-colors">
                #{{ $tag->name }}
            </a>
        @endforeach
    </div>
    @endif

    {{-- Timestamp & Views --}}
    <div class="flex items-center gap-3 text-xs text-caramel dark:text-gray-400 pt-0.5 font-medium mb-3">
        <time class="uppercase tracking-wider text-[11px]">
            {{ $photo->created_at->diffForHumans() }}
        </time>
        <span>•</span>
        <div class="flex items-center gap-1">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <span>{{ number_format($photo->views_count) }} kali dilihat</span>
        </div>
    </div>

    {{-- Interactive Color Palette Extractor --}}
    <div x-data="{
        colors: [],
        extractColors() {
            @if(!$photo->isVideo())
            const img = this.$parent.$refs.mainImage;
            if (!img) return;
            const runExtraction = () => {
                try {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    canvas.width = 10;
                    canvas.height = 10;
                    ctx.drawImage(img, 0, 0, 10, 10);
                    const data = ctx.getImageData(0, 0, 10, 10).data;
                    
                    const colorMap = {};
                    for (let i = 0; i < data.length; i += 4) {
                        const r = data[i];
                        const g = data[i+1];
                        const b = data[i+2];
                        // Convert to hex
                        const hex = '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase();
                        colorMap[hex] = (colorMap[hex] || 0) + 1;
                    }
                    
                    this.colors = Object.keys(colorMap)
                        .sort((a, b) => colorMap[b] - colorMap[a])
                        .slice(0, 5);
                } catch (e) {
                    this.colors = ['{{ $photo->dominant_color ?? "#3B2417" }}'];
                }
            };
            if (img.complete) {
                runExtraction();
            } else {
                img.addEventListener('load', runExtraction);
            }
            @else
            this.colors = ['{{ $photo->dominant_color ?? "#3B2417" }}'];
            @endif
        }
    }" x-init="extractColors()" class="pt-3 border-t border-sand/40 dark:border-white/10">
        <span class="text-xs font-bold text-cocoa/80 dark:text-white/80 block mb-2">Palette Warna</span>
        <div class="flex items-center gap-2">
            <template x-for="color in colors" :key="color">
                <div class="group relative flex-1 aspect-[4/3] rounded-lg border border-white/20 shadow-sm cursor-pointer transition-transform hover:scale-105 active:scale-95"
                     :style="`background-color: ${color}`"
                     @click="
                        navigator.clipboard.writeText(color).then(() => {
                            window.showToast('Salin ' + color + ' ke papan klip');
                        });
                     "
                     :title="`Salin: ${color}`">
                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                        </svg>
                    </div>
                </div>
            </template>
            <template x-if="colors.length > 0">
                <a :href="`{{ route('search') }}?q=` + colors[0].replace('#', '')"
                   class="w-8 h-8 rounded-lg bg-sand/30 dark:bg-white/10 hover:bg-brown hover:text-white text-brown dark:text-caramel flex items-center justify-center shadow-sm border border-white/20 transition-all active:scale-95 shrink-0"
                   title="Cari foto serupa">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </a>
            </template>
        </div>
    </div>
</div>