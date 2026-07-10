@props(['active'])

@php
$classes = ($active ?? false)
 ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-indigo-400 text-start text-base font-medium text-indigo-700 bg-indigo-50 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700 transition duration-150 ease-in-out'
 : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-espresso hover:text-cocoa hover:bg-cream hover:border-sand focus:outline-none focus:text-cocoa focus:bg-cream focus:border-sand transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
 {{ $slot }}
</a>
