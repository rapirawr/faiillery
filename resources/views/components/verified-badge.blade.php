@props(['size' => 'w-6 h-6 md:w-8 md:h-8', 'checkSize' => 'w-3.5 h-3.5 md:w-4 md:h-4'])

<div class="flex-shrink-0 relative {{ $size }}" title="Akun Terverifikasi">
 <!-- Rotating Background -->
 <div class="absolute inset-0 text-black/80 animate-[spin_10s_linear_infinite]">
 <svg viewBox="0 0 24 24" fill="currentColor" class="w-full h-full">
 <path d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81c-.67-1.31-1.91-2.19-3.34-2.19s-2.67.88-3.33 2.19c-1.4-.46-2.91-.2-3.92.81s-1.27 2.52-.81 3.91c-1.31.67-2.19 1.91-2.19 3.34s.88 2.67 2.19 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.27 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.67-.88 3.34-2.19c1.39.46 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34z"/>
 </svg>
 </div>
 <!-- Static Checkmark -->
 <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
 <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="{{ $checkSize }} drop-shadow-sm">
 <polyline points="20 6 9 17 4 12"></polyline>
 </svg>
 </div>
</div>
