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


</div>