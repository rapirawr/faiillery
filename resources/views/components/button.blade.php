@props(['variant' => 'primary', 'size' => 'md'])

@php
 $base = 'inline-flex items-center justify-center font-bold transition-all duration-200 active:scale-[0.97] disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer';

 $variants = [
 'primary' => 'text-cream shadow-warm',
 'secondary' => 'border text-espresso hover:bg-cream',
 'outline' => 'border-2 border-sand text-espresso hover:bg-brown hover:text-cream',
 'danger' => 'bg-red-600 text-white hover:bg-red-700',
 'ghost' => 'bg-transparent text-espresso hover:bg-cream',
 'glass' => 'backdrop-blur-md border border-sand/40 text-espresso hover:bg-cream/60',
 ];

 $variantInline = [
 'primary' => 'background:#8B5E3C;color:#FFF8ED;',
 'secondary' => 'background:transparent;color:#5C3A21;border:1px solid #E3C79A;',
 'outline' => 'background:transparent;color:#8B5E3C;border:2px solid #8B5E3C;',
 'danger' => '',
 'ghost' => 'background:transparent;color:#5C3A21;',
 'glass' => 'background:rgba(245,230,206,0.5);color:#5C3A21;border:1px solid #E3C79A;',
 ];

 $sizes = [
 'sm' => 'px-3 py-1.5 text-xs rounded-lg',
 'md' => 'px-5 py-2.5 text-sm rounded-xl',
 'lg' => 'px-8 py-4 text-base rounded-2xl',
 'icon' => 'p-2 rounded-lg',
 ];

 $classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
 $inlineStyle = $variantInline[$variant] ?? '';
@endphp

@if($attributes->has('href'))
 <a {{ $attributes->merge(['class' => $classes, 'style' => $inlineStyle]) }}>{{ $slot }}</a>
@else
 <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes, 'style' => $inlineStyle]) }}>{{ $slot }}</button>
@endif
