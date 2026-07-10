@extends('layouts.app')

@section('content')
<div class="w-full px-2 sm:px-4 md:px-6 mx-auto pt-6" x-data="searchGallery()">
 
 <div class="mb-8 px-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
 <div>
 @if(!empty($query))
 <h1 class="text-2xl font-bold text-cocoa">Hasil untuk "{{ $query }}"</h1>
 @elseif($activeTag)
 <h1 class="text-2xl font-bold text-cocoa">Eksplorasi Tag: #{{ $activeTag->name }}</h1>
 @else
 <h1 class="text-2xl font-bold text-cocoa">Eksplorasi</h1>
 @endif
 </div>

 @if($popularTags && $popularTags->count() > 0)
 <div class="flex flex-wrap gap-2 md:max-w-2xl overflow-x-auto hide-scrollbar pb-2">
 @foreach($popularTags as $popTag)
 <a href="{{ route('search', ['tag' => $popTag->slug]) }}" 
 class="shrink-0 px-4 py-2 rounded-full text-sm font-semibold transition-colors  {{ $activeTag && $activeTag->id === $popTag->id ? 'bg-brown text-cream' : 'bg-soft-cream hover:bg-soft-cream text-cocoa' }}">
 {{ $popTag->name }}
 </a>
 @endforeach
 </div>
 @endif
 </div>

 <!-- Masonry Grid -->
 <div id="search-grid" class="w-full -ml-2 sm:-ml-4 relative">
 <!-- Grid Sizer -->
 <div class="w-1/2 sm:w-1/3 md:w-1/4 lg:w-1/5 xl:w-[16.666%] h-0"></div>
 
 @forelse($photos as $photo)
 <div class="grid-item w-1/2 sm:w-1/3 md:w-1/4 lg:w-1/5 xl:w-[16.666%] pl-2 sm:pl-4 mb-2 sm:mb-4">
 @include('components.photo-card', ['photo' => $photo])
 </div>
 @empty
 <div class="col-span-full py-20 flex flex-col items-center w-full text-center pl-4 absolute left-0 right-0">
 <div class="w-16 h-16 bg-soft-cream rounded-full flex items-center justify-center mb-4">
 <svg class="w-8 h-8 text-caramel" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
 </div>
 <h2 class="text-xl font-bold text-cocoa mb-2">Pencarian tidak ditemukan</h2>
 <p class="text-caramel max-w-md">Kami tidak dapat menemukan hasil untuk "{{ $query }}". Coba gunakan kata kunci lain atau telusuri tag populer.</p>
 </div>
 @endforelse
 </div>



 @if($photos->hasMorePages())
 <div x-intersect.margin.1000px="loadMore()" class="h-10 w-full"></div>
 @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
 Alpine.data('searchGallery', () => ({
 msnry: null,
 nextPageUrl: (() => {
 let url = '{{ $photos->nextPageUrl() }}';
 if (url && window.location.protocol === 'https:') {
 return url.replace(/^http:\/\//i, 'https://');
 }
 return url;
 })(),
 hasMore: {{ $photos->hasMorePages() ? 'true' : 'false' }},
 loading: false,

 init() {
 this.$nextTick(() => {
 const grid = document.querySelector('#search-grid');
 if(!grid.querySelector('.grid-item')) return;
 
 this.msnry = new window.Masonry(grid, {
 itemSelector: '.grid-item',
 columnWidth: '.w-1\\/2',
 percentPosition: true,
 transitionDuration: '0.2s'
 });

 this.msnry.layout();
 });
 },

 loadMore() {
 if (!this.hasMore || this.loading || !this.msnry) return;

 this.loading = true;

 axios.get(this.nextPageUrl)
 .then(response => {
 const data = response.data;
 const temp = document.createElement('div');
 temp.innerHTML = data.html;
 
 const items = Array.from(temp.children).map(child => {
 const wrapper = document.createElement('div');
 wrapper.className = 'grid-item w-1/2 sm:w-1/3 md:w-1/4 lg:w-1/5 xl:w-[16.666%] pl-2 sm:pl-4 mb-2 sm:mb-4';
 wrapper.appendChild(child);
 return wrapper;
 });

 const grid = document.querySelector('#search-grid');
 items.forEach(item => grid.appendChild(item));
 this.msnry.appended(items);
 
 requestAnimationFrame(() => {
 this.msnry.layout();
 });

 let nextUrl = data.next_page || '';
 if (nextUrl && window.location.protocol === 'https:') {
 nextUrl = nextUrl.replace(/^http:\/\//i, 'https://');
 }
 this.nextPageUrl = nextUrl;
 this.hasMore = data.has_more;
 })
 .finally(() => {
 this.loading = false;
 });
 }
 }));
});
</script>
@endpush
