@props(['title' => null, 'class' => ''])
<div {{ $attributes->merge(['class' => 'bg-soft-cream rounded-lg border border-border p-4 ' . $class]) }}>
 @if($title)
 <div class="mb-3 text-sm font-semibold text-gray-200">{{ $title }}</div>
 @endif
 <div class="text-sm text-sand">{{ $slot }}</div>
</div>
