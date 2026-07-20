<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="view-transition" content="same-origin">
    @auth
        <meta name="user-id" content="{{ auth()->id() }}">
        <script>
            window.FaiilleryBoards = {!! auth()->user()->boards()->select('id', 'title')->get()->toJson() !!};
        </script>
    @endauth

    <meta name="google-site-verification" content="hNO97B-C80-QOjJzN3G53tLYwdRqd6j93fG5n5pELqE" />

    <title>@yield('title', config('app.name', 'Faiillery'))</title>
    <meta name="description" content="@yield('meta_description', 'Faiillery Photobooth - Capture, style, and share your favorite moments with our aesthetic digital photobooth gallery.')">
    <link rel="icon" type="image/jpeg" href="{{ asset('favicon.jpg') }}">
    
    <!-- PWA Setup -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#FFF8ED">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-512.png') }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="Faiillery">
    <meta property="og:title" content="@yield('title', config('app.name', 'Faiillery'))">
    <meta property="og:description" content="@yield('meta_description', 'Faiillery Photobooth - Capture, style, and share your favorite moments with our aesthetic digital photobooth gallery.')">
    <meta property="og:image" content="@yield('meta_image', asset('og-default.png'))">
    <meta property="og:image:secure_url" content="@yield('meta_image', asset('og-default.png'))">
    <meta property="og:image:width" content="@yield('meta_image_width', '1200')">
    <meta property="og:image:height" content="@yield('meta_image_height', '630')">
    <meta property="og:image:type" content="image/jpeg">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('title', config('app.name', 'Faiillery'))">
    <meta property="twitter:description" content="@yield('meta_description', 'Discover and share your moments on Faiillery.')">
    <meta property="twitter:image" content="@yield('meta_image', asset('og-default.png'))">

    <!-- WhatsApp Extra -->
    <meta itemprop="name" content="@yield('title', config('app.name', 'Faiillery'))">
    <meta itemprop="description" content="@yield('meta_description', 'Discover and share your moments on Faiillery.')">
    <meta itemprop="image" content="@yield('meta_image', asset('og-default.png'))">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script>
        // Theme — force cream/warm (no dark mode)
        (function() {
            const compact = localStorage.getItem('compact-mode') === 'true';
            if (compact) document.documentElement.classList.add('compact-mode');
            // Remove any leftover dark class
            document.documentElement.classList.remove('dark');
        })();

        // Suppress View Transition AbortErrors (harmless)
        window.addEventListener('unhandledrejection', (event) => {
            const reason = event.reason;
            const message = typeof reason === 'string'
                ? reason
                : reason && typeof reason.message === 'string'
                    ? reason.message
                    : '';

            if (message.toLowerCase().includes('transition') && message.toLowerCase().includes('skipped')) {
                event.preventDefault();
            }

            if (reason && reason.name === 'AbortError' && message.toLowerCase().includes('transition')) {
                event.preventDefault();
            }
        });
    </script>

    <style>
        .compact-mode .max-w-7xl, .compact-mode .max-w-5xl { max-width: 1400px !important; padding-left: 2rem; padding-right: 2rem; }
        .compact-mode .py-8, .compact-mode .py-12 { padding-top: 1.5rem; padding-bottom: 1.5rem; }
        .compact-mode .mb-8, .compact-mode .mb-12 { margin-bottom: 1.5rem; }
        .compact-mode .gap-8, .compact-mode .gap-12 { gap: 1.5rem; }
    </style>

    @stack('head')
</head>
<body class="font-sans antialiased bg-cream text-cocoa pb-24 md:pb-0 pt-[80px] min-h-screen flex flex-col" style="background-color:#FFF8ED;color:#3B2417;">
    
    <!-- Top Navbar -->
    @include('components.navbar')

    <!-- Global Announcement Banner (Disabled due to production stability issues) -->


    <!-- Main Content -->
    <main class="w-full flex-1">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    <!-- Bottom Mobile Nav -->
    @if(!request()->routeIs('photos.photobooth'))
        @include('components.bottom-nav')
    @endif

    <!-- Toast Notifications Container -->
    <div id="toast-container" class="fixed bottom-20 md:bottom-6 left-1/2 transform -translate-x-1/2 z-50 flex flex-col gap-2 pointer-events-none">
    </div>

    <!-- Global Modals Component -->
    @include('components.modals')

    @stack('scripts')
    
    <script>
        // Global Modal Helpers
        window.appConfirm = (title, message, onConfirm, confirmText = 'Ya, Lanjutkan', type = 'default') => {
            window.dispatchEvent(new CustomEvent('app-confirm', { detail: { title, message, onConfirm, confirmText, type } }));
        };

        window.appAlert = (title, message, confirmText = 'OK') => {
            window.dispatchEvent(new CustomEvent('app-confirm', { detail: { title, message, confirmText, type: 'success' } }));
        };

        window.appPrompt = (title, message, onConfirm, defaultValue = '', placeholder = '', confirmText = 'Simpan') => {
            window.dispatchEvent(new CustomEvent('app-prompt', { detail: { title, message, onConfirm, defaultValue, placeholder, confirmText } }));
        };

        // Alpine Modal Controller
        document.addEventListener('alpine:init', () => {
            Alpine.data('appModals', () => ({
                confirmData: { show: false, title: '', message: '', confirmText: '', onConfirm: null },
                promptData: { show: false, title: '', message: '', input: '', confirmText: '', placeholder: '', onConfirm: null },

                openConfirm(detail) {
                    this.confirmData = { show: true, ...detail };
                },
                openPrompt(detail) {
                    this.promptData = { 
                        show: true, 
                        title: detail.title, 
                        message: detail.message, 
                        input: detail.defaultValue, 
                        onConfirm: detail.onConfirm, 
                        confirmText: detail.confirmText, 
                        placeholder: detail.placeholder 
                    };
                    setTimeout(() => this.$refs.promptInput?.focus(), 100);
                },
                closeModal() {
                    this.confirmData.show = false;
                    this.promptData.show = false;
                },
                confirmAction() {
                    if (this.confirmData.onConfirm) this.confirmData.onConfirm();
                    this.closeModal();
                },
                promptAction() {
                    if (this.promptData.onConfirm) this.promptData.onConfirm(this.promptData.input);
                    this.closeModal();
                }
            }));
        });

        window.showToast = function(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');

            toast.className = `px-5 py-3 rounded-xl font-medium text-sm transition-all duration-300 transform translate-y-4 opacity-0 pointer-events-auto flex items-center gap-2.5`;
            toast.style.cssText = 'background:#FFF8ED;color:#3B2417;border:1px solid #E3C79A;box-shadow:0 6px 20px rgba(91,58,33,0.14);';
            
            let iconColor = '#8B5E3C';
            let iconPath  = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>`;

            if (type === 'error') {
                iconColor = '#b91c1c';
                iconPath = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>`;
            } else if (type === 'warning') {
                iconColor = '#c2410c';
                iconPath = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>`;
            }
                
            toast.innerHTML = `<svg style="width:18px;height:18px;flex-shrink:0;color:${iconColor};" fill="none" stroke="currentColor" viewBox="0 0 24 24">${iconPath}</svg><span>${message}</span>`;
            container.appendChild(toast);
            
            requestAnimationFrame(() => { toast.classList.remove('translate-y-4','opacity-0'); toast.classList.add('translate-y-0','opacity-100'); });
            setTimeout(() => { toast.classList.remove('translate-y-0','opacity-100'); toast.classList.add('translate-y-4','opacity-0'); setTimeout(() => toast.remove(), 300); }, 3200);
        };
        
        @if(session('success'))
            setTimeout(() => window.showToast("{{ session('success') }}", 'success'), 500);
        @endif
        @if(session('warning'))
            setTimeout(() => window.showToast("{{ session('warning') }}", 'warning'), 500);
        @endif
        @if(session('error'))
            setTimeout(() => window.showToast("{{ session('error') }}", 'error'), 500);
        @endif

        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(registration => {
                    console.log('SW registered: ', registration);
                }).catch(registrationError => {
                    console.log('SW registration failed: ', registrationError);
                });
            });
        }
    </script>
</body>
</html>
