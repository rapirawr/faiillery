@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-espresso']) }}>
 {{ $value ?? $slot }}
</label>
