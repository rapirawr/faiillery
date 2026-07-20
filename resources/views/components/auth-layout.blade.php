<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', $cms['site_name'] ?? 'Faiillery') }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('favicon.jpg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Outfit', sans-serif; box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background-color: #FFF8ED;
            background-image:
                radial-gradient(ellipse 80% 60% at 50% -10%, rgba(198,156,109,0.18) 0%, transparent 70%),
                radial-gradient(ellipse 60% 50% at 100% 100%, rgba(139,94,60,0.1) 0%, transparent 60%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        /* Subtle dot pattern */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle, rgba(139,94,60,0.07) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
            z-index: 0;
        }

        .auth-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 460px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* Logo bar */
        .auth-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 24px;
            text-decoration: none;
        }
        .auth-logo-mark {
            width: 40px; height: 40px;
            background: #8B5E3C;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            position: relative; overflow: hidden;
            box-shadow: 0 4px 12px rgba(139,94,60,0.3);
            transition: transform 0.3s ease;
        }
        .auth-logo:hover .auth-logo-mark { transform: rotate(-6deg) scale(1.05); }
        .auth-logo-mark::after {
            content: '';
            position: absolute; top: 5px; right: 5px;
            width: 7px; height: 7px;
            background: #E3C79A;
            border-radius: 2px;
        }
        .auth-logo-letter {
            font-weight: 800; font-size: 20px;
            color: #FFF8ED; line-height: 1;
        }
        .auth-logo-name {
            font-size: 22px; font-weight: 800;
            color: #3B2417; letter-spacing: -0.3px;
        }

        /* Card */
        .auth-card {
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid rgba(227,199,154,0.9);
            border-radius: 24px;
            padding: 42px 40px 34px;
            box-shadow:
                0 1px 3px rgba(139,94,60,0.06),
                0 8px 32px rgba(139,94,60,0.12),
                0 0 0 1px rgba(255,255,255,0.8) inset;
        }

        @media (max-width: 480px) {
            .auth-card { padding: 28px 24px 24px; border-radius: 20px; }
        }

        /* Footer */
        .auth-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #C69C6D;
        }

        /* Override browser autofill blue background */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 9999px #FFF8ED inset !important;
            box-shadow: 0 0 0 9999px #FFF8ED inset !important;
            -webkit-text-fill-color: #3B2417 !important;
            caret-color: #3B2417;
        }

        @keyframes fade-up {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .auth-card { animation: fade-up 0.4s ease both; }
    </style>
</head>
<body>
    <div class="auth-container">

        <!-- Logo -->
        <a href="/" class="auth-logo">
            <div class="auth-logo-mark">
                <span class="auth-logo-letter">F</span>
            </div>
            <span class="auth-logo-name">{{ $cms['site_name'] }}</span>
        </a>

        <!-- Form card -->
        <div class="auth-card">
            {{ $slot }}
        </div>

        <p class="auth-footer">&copy; {{ str_replace('{year}', date('Y'), $cms['footer_text']) }}</p>
    </div>
</body>
</html>
