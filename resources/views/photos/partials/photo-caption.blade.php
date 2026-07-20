{{-- =====================================================================
     PARTIAL: photos/partials/photo-caption.blade.php
     Caption + collapsible "more", tags, timestamp
     ===================================================================== --}}
<div class="px-3 pb-2"
     x-data="{
         captionExpanded: false,
         titleExpanded: false
     }">
  <div class="px-4 pt-3 pb-3 bg-white/25 dark:bg-neutral-900/40 backdrop-blur-xl backdrop-saturate-150 border border-white/40 dark:border-white/10 shadow-lg shadow-black/10 rounded-2xl">

    {{-- Caption (username bold + text) --}}
    @if($photo->description || $photo->title)
    <div class="mb-2">
        <p class="text-[13.5px] text-dark dark:text-white leading-snug">
            @if($photo->user)
            <a href="{{ route('profile.show', $photo->user) }}"
               class="font-semibold text-white hover:underline mr-1">{{ $photo->user->name }}</a>
            @endif
            {{-- Title --}}
            <span x-data="{ expanded: false, text: {{ json_encode($photo->title) }}, limit: 80 }">
                <span x-show="!expanded && text.length > limit" class="text-white" x-text="text.substring(0, limit) + '...'"></span>
                <span x-show="expanded || text.length <= limit" class="text-white" x-text="text"></span>
                <button x-show="!expanded && text.length > limit"
                        @click.stop="expanded = true"
                        class="text-gray-400 dark:text-gray-500 text-[13px] ml-1 hover:text-gray-600 transition-colors">lainnya</button>
            </span>
        </p>

        @if($photo->description)
        <div class="mt-1.5" x-data="{ expanded: false, desc: {{ json_encode($photo->description) }}, limit: 120 }">
            <p class="text-[13px] text-gray-600 dark:text-gray-400 leading-snug whitespace-pre-wrap">
                <span x-show="!expanded && desc.length > limit" x-text="desc.substring(0, limit) + '...'"></span>
                <span x-show="expanded || desc.length <= limit" x-text="desc"></span>
                <button x-show="!expanded && desc.length > limit"
                        @click.stop="expanded = true"
                        class="text-gray-400 dark:text-gray-500 text-[12px] ml-1 hover:text-gray-600 transition-colors">lainnya</button>
            </p>
        </div>
        @endif
    </div>
    @endif

    {{-- Tags --}}
    @if($photo->tags->count() > 0)
    <div class="flex flex-wrap gap-1.5 mb-2">
        @foreach($photo->tags as $tag)
            <a href="{{ route('search', ['tag' => $tag->slug]) }}"
               class="text-blue-500 text-[12px] font-medium hover:underline">#{{ $tag->name }}</a>
        @endforeach
    </div>
    @endif

    {{-- Timestamp --}}
    <time class="text-[11px] text-gray-400 dark:text-gray-500 uppercase tracking-wider">
        {{ $photo->created_at->diffForHumans() }}
    </time>

    {{-- Views --}}
    <div class="flex items-center gap-1 mt-0.5">
        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        <span class="text-[11px] text-gray-400">{{ number_format($photo->views_count) }} kali dilihat</span>
    </div>
  </div>
</div>