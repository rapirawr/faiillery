@extends('errors.layout')

@section('title', "I'm a Teapot")
@section('code', '418')
@section('message', "I'm a teapot. The server refuses to brew coffee because it is, permanently, a teapot.")

@push('head')
<style>
 .teapot-container {
 position: relative;
 font-size: 5rem;
 margin-bottom: 2rem;
 display: inline-block;
 animation: pour 3s ease-in-out infinite;
 }
 
 .steam {
 position: absolute;
 top: -20px;
 left: 50%;
 transform: translateX(-50%);
 font-size: 2rem;
 opacity: 0;
 animation: steam 2s ease-out infinite;
 }

 @keyframes pour {
 0%, 100% { transform: rotate(0deg); }
 50% { transform: rotate(-25deg); }
 }

 @keyframes steam {
 0% { transform: translate(-50%, 0) scale(1); opacity: 0; }
 50% { opacity: 0.6; }
 100% { transform: translate(-30%, -50px) scale(1.5); opacity: 0; }
 }
</style>
@endpush

@section('extra_content')
<div class="mb-10">
 <div class="teapot-container">
 <div class="steam">💨</div>
 <div class="steam" style="animation-delay: 0.5s">💨</div>
 🫖
 </div>
</div>
@endsection
