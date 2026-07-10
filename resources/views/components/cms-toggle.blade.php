@props([
    'name',
    'label',
    'checked' => false,
    'description' => null,
    'danger' => false,
    'inline' => false,
])

@if($inline)
    {{-- Compact inline version --}}
    <label class="flex items-center gap-2 cursor-pointer select-none">
        <div class="relative flex-shrink-0">
            <input type="checkbox" name="{{ $name }}" value="1" {{ $checked ? 'checked' : '' }} class="sr-only peer">
            <div class="w-9 h-5 rounded-full transition-colors duration-200 peer-checked:bg-cocoa"
                 style="background:#E3C79A;"></div>
            <div class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform duration-200 peer-checked:translate-x-4"></div>
        </div>
        <span class="text-xs font-semibold" style="color:#8B5E3C;">{{ $label }}</span>
    </label>
@else
    {{-- Full row version --}}
    <div class="flex items-start justify-between gap-4 py-3 border-b last:border-0" style="border-color:#FAF3E8;">
        <div class="flex-1 min-w-0">
            <div class="text-sm font-semibold {{ $danger ? '' : '' }}" style="color:{{ $danger ? '#8B5E3C' : '#3B2417' }};">
                {{ $label }}
            </div>
            @if($description)
                <div class="text-xs mt-0.5" style="color:#C69C6D;">{{ $description }}</div>
            @endif
        </div>
        <label class="relative flex-shrink-0 cursor-pointer mt-0.5">
            <input type="checkbox" name="{{ $name }}" value="1" {{ $checked ? 'checked' : '' }} class="sr-only peer">
            <div class="w-10 h-5 rounded-full transition-colors duration-200 {{ $danger ? 'peer-checked:bg-amber-500' : 'peer-checked:bg-cocoa' }}"
                 style="background:#E3C79A;"></div>
            <div class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform duration-200 peer-checked:translate-x-5"></div>
        </label>
    </div>
@endif
