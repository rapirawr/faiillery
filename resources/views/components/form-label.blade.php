@props(['value'])
<label {{ $attributes->merge(['class' => 'block text-xs font-bold mb-2 uppercase tracking-wider']) }} style="color:#8B5E3C;letter-spacing:0.08em;">
 {{ $value ?? $slot }}
</label>
