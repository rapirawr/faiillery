<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sedang Maintenance — Faiillery</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Outfit', sans-serif; box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            background-color: #FFF8ED;
            display: flex; align-items: center; justify-content: center;
            padding: 24px;
        }
        .card {
            background: white;
            border: 1px solid #E3C79A;
            border-radius: 24px;
            padding: 48px 40px;
            max-width: 440px;
            width: 100%;
            text-align: center;
            box-shadow: 0 8px 32px rgba(139,94,60,0.1);
        }
        .icon {
            width: 64px; height: 64px;
            background: #F5E6CE;
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
        }
        h1 { font-size: 1.5rem; font-weight: 800; color: #3B2417; margin-bottom: 12px; }
        p  { font-size: 0.9rem; color: #8B5E3C; line-height: 1.6; }
        .footer { margin-top: 32px; font-size: 0.75rem; color: #C69C6D; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg width="28" height="28" fill="none" stroke="#8B5E3C" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
        </div>
        <h1>Sedang Maintenance</h1>
        <p>{{ $message }}</p>
        <p class="footer">&copy; {{ date('Y') }} Faiillery</p>
    </div>
</body>
</html>
