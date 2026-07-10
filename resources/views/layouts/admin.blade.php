<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Faiillery</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Outfit', sans-serif; }

        body {
            background-color: #FAF3E8;
            color: #3B2417;
        }

        /* Sidebar */
        .admin-sidebar {
            background: #FFF8ED;
            border-right: 1px solid #E3C79A;
            box-shadow: 2px 0 12px rgba(139,94,60,0.06);
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #8B5E3C;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .sidebar-link:hover {
            background: #F5E6CE;
            color: #3B2417;
        }
        .sidebar-link.active {
            background: #F5E6CE;
            color: #5C3A21;
            font-weight: 600;
            border-left: 3px solid #8B5E3C;
        }
        .sidebar-link.active svg {
            color: #8B5E3C;
        }

        /* Cards */
        .admin-card {
            background: #FFF8ED;
            border: 1px solid #E3C79A;
            border-radius: 14px;
            box-shadow: 0 1px 6px rgba(139,94,60,0.06);
        }
        .admin-card:hover {
            box-shadow: 0 4px 16px rgba(139,94,60,0.1);
            border-color: #C69C6D;
        }

        /* Stat card accent */
        .stat-card-accent {
            background: linear-gradient(135deg, #8B5E3C 0%, #5C3A21 100%);
            color: #FFF8ED;
            border: none;
        }

        /* Table */
        .admin-table thead th {
            background: #F5E6CE;
            color: #8B5E3C;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 12px 16px;
            border-bottom: 1px solid #E3C79A;
        }
        .admin-table tbody tr {
            border-bottom: 1px solid #F5E6CE;
            transition: background 0.12s ease;
        }
        .admin-table tbody tr:hover {
            background: #FDF5E8;
        }
        .admin-table tbody td {
            padding: 14px 16px;
        }

        /* Buttons */
        .btn-primary {
            background: #8B5E3C;
            color: #FFF8ED;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 8px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.1s ease;
        }
        .btn-primary:hover { background: #5C3A21; }
        .btn-primary:active { transform: scale(0.98); }

        .btn-ghost {
            background: transparent;
            color: #8B5E3C;
            font-weight: 500;
            font-size: 0.8rem;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid #E3C79A;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-ghost:hover {
            background: #F5E6CE;
            color: #3B2417;
            border-color: #C69C6D;
        }

        /* Icon action button */
        .icon-btn {
            padding: 7px;
            border-radius: 8px;
            color: #C69C6D;
            transition: all 0.15s ease;
            cursor: pointer;
            border: 1px solid transparent;
        }
        .icon-btn:hover {
            background: #F5E6CE;
            color: #5C3A21;
            border-color: #E3C79A;
        }
        .icon-btn.active {
            color: #8B5E3C;
            background: #F5E6CE;
            border-color: #C69C6D;
        }

        /* Badge */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .badge-admin    { background: #3B2417; color: #FFF8ED; }
        .badge-shadowban{ background: #F5E6CE; color: #8B5E3C; border: 1px solid #E3C79A; }
        .badge-live     { background: #8B5E3C; color: #FFF8ED; }
        .badge-ended    { background: #F5E6CE; color: #C69C6D; }
        .badge-pending  { background: #fef9c3; color: #a16207; }
        .badge-resolved { background: #dcfce7; color: #166534; }
        .badge-dismissed{ background: #F5E6CE; color: #C69C6D; }

        /* Header bar */
        .admin-header {
            background: #FFF8ED;
            border-bottom: 1px solid #E3C79A;
            padding: 18px 32px;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        /* Input */
        .admin-input {
            width: 100%;
            background: #FFF8ED;
            border: 1px solid #E3C79A;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.875rem;
            color: #3B2417;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .admin-input:focus {
            border-color: #8B5E3C;
            box-shadow: 0 0 0 3px rgba(139,94,60,0.12);
            background: #FFFBF5;
        }
        .admin-input::placeholder { color: #E3C79A; }

        /* Select */
        .admin-select {
            width: 100%;
            background: #FFF8ED;
            border: 1px solid #E3C79A;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.875rem;
            color: #3B2417;
            outline: none;
            cursor: pointer;
            transition: border-color 0.2s ease;
        }
        .admin-select:focus {
            border-color: #8B5E3C;
            box-shadow: 0 0 0 3px rgba(139,94,60,0.12);
        }

        /* Status dot */
        .status-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; }
        .status-dot.green { background: #22c55e; }
        .status-dot.red   { background: #8B5E3C; }

        /* Glassmorphism panel */
        .glass-panel {
            background: rgba(255,248,237,0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(227,199,154,0.6);
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(139,94,60,0.07);
        }

        /* Pagination override */
        nav[role="navigation"] span[aria-current="page"] span,
        nav[role="navigation"] button[aria-current="page"] {
            background: #8B5E3C !important;
            border-color: #8B5E3C !important;
            color: #FFF8ED !important;
        }
        nav[role="navigation"] a,
        nav[role="navigation"] button {
            border-color: #E3C79A !important;
            color: #8B5E3C !important;
        }
        nav[role="navigation"] a:hover,
        nav[role="navigation"] button:hover {
            background: #F5E6CE !important;
            color: #3B2417 !important;
        }

        @keyframes slideDown {
            from { transform: translateY(-10px); opacity: 0; }
            to   { transform: translateY(0); opacity: 1; }
        }
        .animate-slide-down { animation: slideDown 0.25s ease; }
    </style>
</head>
<body x-data class="min-h-screen flex antialiased">

    <!-- Sidebar -->
    <aside class="admin-sidebar w-60 flex flex-col py-6 fixed h-full z-50" style="padding-left:20px;padding-right:20px;">
        <!-- Brand -->
        <div class="flex items-center gap-2.5 mb-8 px-2">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#8B5E3C;">
                <span class="font-bold text-sm" style="color:#FFF8ED;">F</span>
            </div>
            <div>
                <div class="font-bold text-sm" style="color:#3B2417;line-height:1.2;">Faiillery</div>
                <div class="text-xs font-medium" style="color:#C69C6D;letter-spacing:0.05em;">Admin Console</div>
            </div>
        </div>

        <!-- Nav section label -->
        <div class="text-xs font-bold mb-2 px-2" style="color:#C69C6D;letter-spacing:0.1em;text-transform:uppercase;">Overview</div>

        <nav class="flex-1 space-y-0.5">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span>Dashboard</span>
            </a>

            <div class="text-xs font-bold mt-5 mb-2 px-2" style="color:#C69C6D;letter-spacing:0.1em;text-transform:uppercase;">Manage</div>

            <a href="{{ route('admin.users') }}" class="sidebar-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span>Users</span>
            </a>
            <a href="{{ route('admin.photos') }}" class="sidebar-link {{ request()->routeIs('admin.photos') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h14a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Photos</span>
            </a>
            <a href="{{ route('admin.reports') }}" class="sidebar-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>Reports</span>
            </a>
            <a href="{{ route('admin.announcement') }}" class="sidebar-link {{ request()->routeIs('admin.announcement') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                <span>Announcements</span>
            </a>

            <div class="text-xs font-bold mt-5 mb-2 px-2" style="color:#C69C6D;letter-spacing:0.1em;text-transform:uppercase;">Tools</div>

            <a href="{{ route('admin.cms') }}" class="sidebar-link {{ request()->routeIs('admin.cms') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                <span>CMS</span>
            </a>

            <a href="{{ route('admin.sql.index') }}" class="sidebar-link {{ request()->routeIs('admin.sql.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>SQL Console</span>
            </a>
        </nav>

        <!-- Footer -->
        <div class="pt-5 border-t" style="border-color:#f0f0f0;">
            <a href="{{ route('home') }}" class="sidebar-link" style="color:#C69C6D;">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Back to Site</span>
            </a>
            <div class="flex items-center gap-2 mt-4 px-2">
                <img src="{{ auth()->user()->avatar_url }}" class="w-7 h-7 rounded-full object-cover" style="border:1px solid #E3C79A;">
                <div class="min-w-0">
                    <div class="text-xs font-semibold truncate" style="color:#3B2417;">{{ auth()->user()->name }}</div>
                    <div class="text-xs" style="color:#C69C6D;">Administrator</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 ml-60 flex flex-col min-h-screen" style="background:#FAF3E8;">
        <!-- Top header -->
        <header class="admin-header flex items-center justify-between">
            <div>
                <h1 class="text-base font-bold" style="color:#3B2417;">
                    @yield('page-title', 'Dashboard')
                </h1>
                <p class="text-xs mt-0.5" style="color:#C69C6D;">
                    @yield('page-subtitle', 'Faiillery Admin Console')
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-full" style="background:#f0fdf4;color:#16a34a;">
                    <span class="status-dot green" style="animation:pulse 2s infinite;"></span>
                    System Online
                </span>
            </div>
        </header>

        <!-- Content area -->
        <main class="flex-1 p-8">
            @yield('content')
        </main>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[100] flex flex-col gap-2 pointer-events-none"></div>

    <!-- Global Modals -->
    @include('components.modals')

    <script>
        // Alpine Modal Controller
        document.addEventListener('alpine:init', () => {
            Alpine.data('appModals', () => ({
                confirmData: { show: false, title: '', message: '', confirmText: '', onConfirm: null },
                promptData: { show: false, title: '', message: '', input: '', confirmText: '', placeholder: '', onConfirm: null },

                openConfirm(detail) { this.confirmData = { show: true, ...detail }; },
                openPrompt(detail) {
                    this.promptData = { show: true, title: detail.title, message: detail.message, input: detail.defaultValue, onConfirm: detail.onConfirm, confirmText: detail.confirmText, placeholder: detail.placeholder };
                    setTimeout(() => this.$refs.promptInput?.focus(), 100);
                },
                closeModal() { this.confirmData.show = false; this.promptData.show = false; },
                confirmAction() { if (this.confirmData.onConfirm) this.confirmData.onConfirm(); this.closeModal(); },
                promptAction() { if (this.promptData.onConfirm) this.promptData.onConfirm(this.promptData.input); this.closeModal(); }
            }));
        });

        window.appConfirm = (title, message, onConfirm, confirmText = 'Lanjutkan', type = 'danger') => {
            window.dispatchEvent(new CustomEvent('app-confirm', { detail: { title, message, onConfirm, confirmText, type } }));
        };
        window.appPrompt = (title, message, onConfirm, defaultValue = '', placeholder = '', confirmText = 'Simpan') => {
            window.dispatchEvent(new CustomEvent('app-prompt', { detail: { title, message, onConfirm, defaultValue, placeholder, confirmText } }));
        };

        // Toast
        window.showToast = function(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const isSuccess = type === 'success';

            toast.className = `pointer-events-auto flex items-center gap-3 px-5 py-3.5 rounded-xl shadow-lg border transition-all duration-300 transform translate-y-4 opacity-0`;
            toast.style.cssText = `background:#FFF8ED;border-color:#E3C79A;color:#3B2417;font-size:0.875rem;font-weight:500;min-width:240px;`;

            const iconColor = isSuccess ? '#22c55e' : '#8B5E3C';
            const iconPath = isSuccess
                ? `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>`
                : `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>`;

            toast.innerHTML = `
                <div style="width:20px;height:20px;flex-shrink:0;color:${iconColor};">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:100%;height:100%;">${iconPath}</svg>
                </div>
                <span>${message}</span>`;

            container.appendChild(toast);
            requestAnimationFrame(() => { toast.classList.remove('translate-y-4', 'opacity-0'); toast.classList.add('translate-y-0', 'opacity-100'); });
            setTimeout(() => { toast.classList.remove('translate-y-0', 'opacity-100'); toast.classList.add('translate-y-4', 'opacity-0'); setTimeout(() => toast.remove(), 300); }, 3200);
        };

        @if(session('success')) window.showToast("{{ session('success') }}", 'success'); @endif
        @if(session('error')) window.showToast("{{ session('error') }}", 'error'); @endif
    </script>
</body>
</html>
