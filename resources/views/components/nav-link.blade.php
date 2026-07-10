@props(['active'])

@php
$classes = ($active ?? false)
 ? 'inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5 text-cocoa focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
 : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-caramel hover:text-espresso hover:border-sand focus:outline-none focus:text-espresso focus:border-sand transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
 {{ $slot }}
</a>
