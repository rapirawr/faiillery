<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Embed {{ $photo->title }} - Failerry</title>
 @vite(['resources/css/app.css'])
 <style>
 body, html {
 margin: 0;
 padding: 0;
 height: 100%;
 width: 100%;
 overflow: hidden;
 background: #000;
 }
 .embed-container {
 position: relative;
 width: 100%;
 height: 100%;
 display: flex;
 align-items: center;
 justify-content: center;
 background-color: {{ $photo->dominant_color ?? '#1a1a1a' }};
 }
 .main-img {
 max-width: 100%;
 max-height: 100%;
 object-fit: contain;
 transition: transform 0.5s ease;
 }
 .overlay {
 position: absolute;
 bottom: 0;
 left: 0;
 right: 0;
 padding: 16px 20px;
 background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 70%, transparent 100%);
 color: white;
 display: flex;
 align-items: center;
 justify-content: space-between;
 backdrop-blur: 4px;
 }
 .info {
 display: flex;
 flex-direction: column;
 gap: 2px;
 overflow: hidden;
 }
 .title {
 font-weight: 700;
 font-size: 14px;
 margin: 0;
 white-space: nowrap;
 overflow: hidden;
 text-overflow: ellipsis;
 }
 .author {
 font-size: 11px;
 opacity: 0.8;
 display: flex;
 items-center: center;
 gap: 4px;
 }
 .failerry-btn {
 background: white;
 color: black;
 padding: 6px 12px;
 border-radius: 20px;
 font-size: 12px;
 font-weight: 700;
 text-decoration: none;
 transition: all 0.2s;
 white-space: nowrap;
 }
 .failerry-btn:hover {
 transform: scale(1.05);
 background: #f0f0f0;
 }
 .logo-small {
 height: 14px;
 width: auto;
 margin-right: 8px;
 }
 </style>
</head>
<body>
 <div class="embed-container">
 <img src="{{ $photo->image_url }}" alt="{{ $photo->title }}" class="main-img">
 
 <div class="overlay">
 <div class="info">
 <h1 class="title">{{ $photo->title }}</h1>
 <div class="author">
 <span>by @ {{ $photo->user->username }}</span>
 @if($photo->user->is_verified)
 <svg class="w-3 h-3 text-blue-400 fill-current" viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zM10 17l-5-5 1.4-1.4 3.6 3.6 7.6-7.6L19 8l-9 9z"/></svg>
 @endif
 </div>
 </div>
 <a href="{{ route('photos.show', $photo->uid) }}" target="_blank" class="failerry-btn">
 Lihat di Failerry
 </a>
 </div>
 </div>
</body>
</html>
