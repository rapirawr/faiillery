<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>@yield('title') — Failerry</title>
 <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
 @vite(['resources/css/app.css', 'resources/js/app.js'])
 <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
 <style>
 body { 
 font-family: 'Outfit', sans-serif; 
 background-color: #3B2417; 
 color: #3B2417; 
 animation: fadeIn 1s ease-out;
 }

 @keyframes fadeIn {
 from { opacity: 0; }
 to { opacity: 1; }
 }

 @keyframes slideUp {
 from { transform: translateY(30px); opacity: 0; }
 to { transform: translateY(0); opacity: 1; }
 }

 @keyframes float {
 0% { transform: translateY(0) rotate(12deg); }
 50% { transform: translateY(-15px) rotate(15deg); }
 100% { transform: translateY(0) rotate(12deg); }
 }

 @keyframes glow {
 0% { text-shadow: 0 0 10px rgba(255,255,255,0.05); }
 50% { text-shadow: 0 0 30px rgba(255,255,255,0.15); }
 100% { text-shadow: 0 0 10px rgba(255,255,255,0.05); }
 }

 .animate-slide-up { animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
 .animate-float { animation: float 4s ease-in-out infinite; }
 .animate-glow { animation: glow 4s ease-in-out infinite; }
 </style>
 @stack('head')
</head>
<body class="min-h-screen flex items-center justify-center p-6 overflow-hidden">
 
 <div class="max-w-md w-full text-center relative">
 <!-- Big Ghosty Error Code -->
 <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-[15rem] md:text-[20rem] font-black text-cream/[0.03] select-none z-0 animate-glow">
 @yield('code')
 </div>

 <div class="relative z-10 animate-slide-up">
 @yield('extra_content')

 <div class="w-20 h-20 bg-cream text-black rounded-3xl flex items-center justify-center mx-auto mb-10 shadow-2xl animate-float">
 <span class="text-4xl font-black italic">B</span>
 </div>
 
 <h1 class="text-5xl font-black tracking-tighter mb-4 uppercase">@yield('title')</h1>
 <p class="text-caramel font-medium text-lg mb-10 leading-relaxed">
 @yield('message')
 </p>

 <a href="/" class="inline-block bg-cream text-black font-black text-xs uppercase tracking-[0.2em] px-10 py-5 rounded-2xl hover:scale-110 hover:shadow-white/20 transition-all active:scale-95 shadow-xl">
 Back to Home
 </a>
 </div>
 </div>

</body>
</html>
