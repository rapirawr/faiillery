@extends('layouts.admin')

@section('page-title', 'Photos')
@section('page-subtitle', 'Moderasi konten yang diunggah')

@section('content')

<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
    @foreach($photos as $photo)
    <div class="admin-card rounded-2xl overflow-hidden group transition-all duration-200 hover:shadow-md" style="padding:0;">
        <!-- Thumbnail -->
        <div class="relative" style="aspect-ratio:3/4;">
            <img src="{{ $photo->thumbnail_url }}" class="w-full h-full object-cover">

            <!-- Hover overlay -->
            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-all duration-200" style="background:rgba(26,26,46,0.55);backdrop-filter:blur(2px);">
                <a href="{{ route('photos.show', $photo->uid) }}" target="_blank"
                   class="flex items-center gap-1.5 text-white text-xs font-semibold px-3 py-2 rounded-lg w-4/5 justify-center transition-all"
                   style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);"
                   onmouseover="this.style.background='rgba(255,255,255,0.25)'"
                   onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    View
                </a>
                <form id="delete-photo-{{ $photo->id }}" action="{{ route('admin.photos.delete', $photo) }}" method="POST" class="w-4/5"
                    @submit.prevent="window.appConfirm('Hapus Foto', 'Hapus &quot;{{ addslashes($photo->title) }}&quot; secara permanen?', () => $el.submit(), 'Hapus')">
                    @csrf
                    @method('DELETE')
                    <button class="w-full flex items-center gap-1.5 justify-center text-white text-xs font-semibold px-3 py-2 rounded-lg transition-all"
                        style="background:rgba(233,44,30,0.7);border:1px solid rgba(233,44,30,0.5);"
                        onmouseover="this.style.background='rgba(233,44,30,0.9)'"
                        onmouseout="this.style.background='rgba(233,44,30,0.7)'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <!-- Info -->
        <div class="px-3 py-2.5" style="border-top:1px solid #F5E6CE;">
            <div class="font-semibold text-xs truncate" style="color:#3B2417;">{{ $photo->title ?: 'Untitled' }}</div>
            <div class="text-xs truncate mt-0.5" style="color:#C69C6D;">{{ $photo->user->name }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-8">
    {{ $photos->links() }}
</div>
@endsection
