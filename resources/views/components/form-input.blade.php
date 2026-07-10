@props(['disabled' => false, 'error' => false])

@php
 $base = 'block w-full rounded-2xl py-3 px-4 text-sm transition-all duration-200 outline-none';
 $ring = $error
 ? 'ring-1 ring-red-400 focus:ring-2 focus:ring-red-400'
 : 'ring-1 ring-sand focus:ring-2 focus:ring-brown';
 $bg = 'bg-cream';
 $text = 'text-cocoa placeholder:text-sand';
 $dis = $disabled ? 'opacity-50 cursor-not-allowed' : '';
 $cls = implode(' ', [$base, $ring, $bg, $text, $dis]);
@endphp

<input
 {{ $disabled ? 'disabled' : '' }}
 {!! $attributes->merge(['class' => $cls, 'style' => 'background-color:#FFF8ED !important; color:#3B2417 !important;']) !!}
>
