<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — EEIG Scaffolding</title>
    <meta name="description" content="EEIG / EEC Scaffolding & Formwork Inventory Management System — real-time stock balances, rental costing, and inter-site transfer tracking.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>

    <!-- Early Theme Initializer (Zero-flicker on reload) -->
    <script>
        (function() {
            const saved = localStorage.getItem('theme');
            if (saved === 'light' || saved === 'dark') {
                document.documentElement.setAttribute('data-theme', saved);
            } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
                document.documentElement.setAttribute('data-theme', 'light');
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>

    <style>
        :root, [data-theme="dark"] {
            --bg-base:        #0b0f19;
            --bg-surface:     #111827;
            --bg-card:        #1a2235;
            --bg-card-hover:  #1e2a40;
            --border:         rgba(255,255,255,0.08);
            --border-subtle:  rgba(255,255,255,0.04);
            --border-glow:    rgba(99,102,241,0.4);

            --primary:        #6366f1;
            --primary-hover:  #4f46e5;
            --primary-glow:   rgba(99,102,241,0.25);

            --accent-green:   #10b981;
            --accent-amber:   #f59e0b;
            --accent-red:     #ef4444;
            --accent-blue:    #3b82f6;
            --accent-purple:  #a855f7;
            --accent-teal:    #14b8a6;

            --text-primary:   #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted:     #64748b;

            --sidebar-w: 256px;
            --topbar-h:  64px;

            --radius-sm: 6px;
            --radius:    10px;
            --radius-lg: 16px;

            --shadow-card: 0 4px 24px rgba(0,0,0,0.4);
            --shadow-glow: 0 0 40px rgba(99,102,241,0.15);
            --table-hover: rgba(255,255,255,0.025);
            --table-border: rgba(255,255,255,0.05);
            --nav-hover:   rgba(255,255,255,0.05);
            --user-bg:     rgba(255,255,255,0.03);
            --progress-wrap: rgba(255,255,255,0.06);
            --topbar-blur: rgba(17, 24, 39, 0.85);
        }

        [data-theme="light"] {
            --bg-base:        #f8fafc;
            --bg-surface:     #ffffff;
            --bg-card:        #ffffff;
            --bg-card-hover:  #f1f5f9;
            --border:         rgba(15, 23, 42, 0.09);
            --border-subtle:  rgba(15, 23, 42, 0.05);
            --border-glow:    rgba(79, 70, 229, 0.25);

            --primary:        #4f46e5;
            --primary-hover:  #4338ca;
            --primary-glow:   rgba(79, 70, 229, 0.12);

            --accent-green:   #059669;
            --accent-amber:   #d97706;
            --accent-red:     #dc2626;
            --accent-blue:    #2563eb;
            --accent-purple:  #9333ea;
            --accent-teal:    #0d9488;

            --text-primary:   #0f172a;
            --text-secondary: #334155;
            --text-muted:     #64748b;

            --sidebar-w: 256px;
            --topbar-h:  64px;

            --radius-sm: 6px;
            --radius:    10px;
            --radius-lg: 16px;

            --shadow-card: 0 1px 3px rgba(0, 0, 0, 0.05), 0 6px 18px rgba(0, 0, 0, 0.04);
            --shadow-glow: 0 0 30px rgba(79, 70, 229, 0.08);
            --table-hover: rgba(15, 23, 42, 0.02);
            --table-border: rgba(15, 23, 42, 0.06);
            --nav-hover:   rgba(15, 23, 42, 0.04);
            --user-bg:     rgba(15, 23, 42, 0.03);
            --progress-wrap: rgba(15, 23, 42, 0.07);
            --topbar-blur: rgba(255, 255, 255, 0.85);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { font-size: 14px; scroll-behavior: smooth; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            line-height: 1.6;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        /* ── Sidebar ───────────────────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: var(--bg-surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            z-index: 50;
            transition: transform 0.3s ease;
        }

        .sidebar-logo {
            padding: 20px 20px 16px;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-logo-mark {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logo-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--primary), var(--accent-purple));
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 20px var(--primary-glow);
            flex-shrink: 0;
        }
        .logo-text h1 {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.02em;
            line-height: 1.2;
            color: var(--text-primary);
        }
        .logo-text span {
            font-size: 10px;
            color: var(--text-muted);
            font-weight: 400;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .sidebar-nav {
            padding: 16px 12px;
            flex: 1;
            overflow-y: auto;
        }

        .nav-section-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 8px 8px 4px;
            margin-top: 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: var(--radius-sm);
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 2px;
        }
        .nav-item:hover {
            background: var(--nav-hover);
            color: var(--text-primary);
        }
        .nav-item.active {
            background: var(--primary-glow);
            color: var(--primary);
            border: 1px solid var(--border-glow);
            font-weight: 600;
        }
        [data-theme="dark"] .nav-item.active {
            color: #a5b4fc;
        }
        .nav-item .nav-icon {
            width: 18px; height: 18px;
            flex-shrink: 0;
            opacity: 0.8;
        }
        .nav-badge {
            margin-left: auto;
            background: var(--accent-red);
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 20px;
            min-width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--border);
        }
        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: var(--radius-sm);
            background: var(--user-bg);
            border: 1px solid var(--border-subtle);
        }
        .user-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent-teal));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
        }
        .user-info p { font-size: 12px; font-weight: 600; line-height: 1.3; }
        .user-info span { font-size: 10px; color: var(--text-muted); text-transform: capitalize; }

        /* ── Main Layout ───────────────────────────── */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            height: var(--topbar-h);
            background: var(--topbar-blur);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 28px;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 40;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            transition: background 0.25s ease, border-color 0.25s ease;
        }

        .topbar-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
        }
        .topbar-breadcrumb .page-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
        }
        .topbar-breadcrumb .page-sub {
            font-size: 13px;
            color: var(--text-muted);
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-content {
            padding: 28px;
            flex: 1;
        }

        /* ── Buttons ───────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 0 20px var(--primary-glow);
        }
        .btn-primary:hover {
            background: var(--primary-hover);
            box-shadow: 0 0 30px rgba(99,102,241,0.4);
            transform: translateY(-1px);
        }
        .btn-outline {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border);
        }
        .btn-outline:hover {
            background: var(--nav-hover);
            color: var(--text-primary);
            border-color: var(--border-glow);
        }
        .btn-danger {
            background: rgba(239,68,68,0.15);
            color: var(--accent-red);
            border: 1px solid rgba(239,68,68,0.3);
        }
        .btn-danger:hover { background: rgba(239,68,68,0.25); }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-icon { padding: 8px; }

        /* ── Cards ─────────────────────────────────── */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            overflow: hidden;
        }
        .card-header {
            padding: 20px 24px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .card-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary);
        }
        .card-subtitle {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 2px;
        }
        .card-body { padding: 24px; }

        /* ── Stat Cards ────────────────────────────── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 22px 22px 18px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: default;
        }
        .stat-card:hover {
            border-color: var(--border-glow);
            box-shadow: var(--shadow-glow);
            transform: translateY(-2px);
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 100px; height: 100px;
            border-radius: 50%;
            filter: blur(40px);
            opacity: 0.2;
            pointer-events: none;
        }
        .stat-card.purple::before { background: var(--primary); }
        .stat-card.green::before  { background: var(--accent-green); }
        .stat-card.amber::before  { background: var(--accent-amber); }
        .stat-card.red::before    { background: var(--accent-red); }
        .stat-card.blue::before   { background: var(--accent-blue); }
        .stat-card.teal::before   { background: var(--accent-teal); }

        .stat-icon {
            width: 40px; height: 40px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
        }
        .stat-icon.purple { background: rgba(99,102,241,0.15); color: #a5b4fc; }
        .stat-icon.green  { background: rgba(16,185,129,0.15); color: var(--accent-green); }
        .stat-icon.amber  { background: rgba(245,158,11,0.15);  color: var(--accent-amber); }
        .stat-icon.red    { background: rgba(239,68,68,0.15);   color: var(--accent-red); }
        .stat-icon.blue   { background: rgba(59,130,246,0.15);  color: var(--accent-blue); }
        .stat-icon.teal   { background: rgba(20,184,166,0.15);  color: var(--accent-teal); }

        .stat-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 6px;
        }
        .stat-value {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1;
            color: var(--text-primary);
        }
        .stat-sub {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 6px;
        }
        .stat-change {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 20px;
            margin-top: 8px;
        }
        .stat-change.up   { background: rgba(16,185,129,0.15); color: var(--accent-green); }
        .stat-change.down { background: rgba(239,68,68,0.15);  color: var(--accent-red); }

        /* ── Table ─────────────────────────────────── */
        .table-wrapper { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        thead th {
            padding: 12px 16px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
            background: var(--bg-card);
        }
        tbody td {
            padding: 13px 16px;
            font-size: 13px;
            color: var(--text-primary);
            border-bottom: 1px solid var(--table-border);
            vertical-align: middle;
        }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr { transition: background 0.15s ease; }
        tbody tr:hover td { background: var(--table-hover); }

        /* ── Badges ────────────────────────────────── */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-green   { background: rgba(16,185,129,0.15);  color: var(--accent-green); }
        .badge-amber   { background: rgba(245,158,11,0.15);   color: var(--accent-amber); }
        .badge-red     { background: rgba(239,68,68,0.15);    color: var(--accent-red); }
        .badge-blue    { background: rgba(59,130,246,0.15);   color: var(--accent-blue); }
        .badge-purple  { background: rgba(99,102,241,0.15);   color: #a5b4fc; }
        .badge-gray    { background: rgba(148,163,184,0.12);  color: var(--text-secondary); }
        .badge-teal    { background: rgba(20,184,166,0.15);   color: var(--accent-teal); }

        /* ── Grid Layouts ──────────────────────────── */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }

        /* ── Alerts ────────────────────────────────── */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 18px;
            border-radius: var(--radius);
            border: 1px solid;
            margin-bottom: 20px;
        }
        .alert-success { background: rgba(16,185,129,0.08);  border-color: rgba(16,185,129,0.2);  color: var(--accent-green); }
        .alert-warning { background: rgba(245,158,11,0.08);   border-color: rgba(245,158,11,0.2);   color: var(--accent-amber); }
        .alert-error   { background: rgba(239,68,68,0.08);    border-color: rgba(239,68,68,0.2);    color: var(--accent-red); }
        .alert-text    { font-size: 13px; line-height: 1.5; }

        /* ── Progress ──────────────────────────────── */
        .progress-bar-wrap {
            background: var(--progress-wrap);
            border-radius: 20px;
            height: 6px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 20px;
            transition: width 0.8s ease;
        }

        /* ── Forms ─────────────────────────────────── */
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 6px;
            letter-spacing: 0.03em;
        }
        .form-control {
            width: 100%;
            background: var(--bg-base);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 9px 13px;
            color: var(--text-primary);
            font-size: 13px;
            font-family: inherit;
            transition: border-color 0.2s;
            outline: none;
        }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }
        select.form-control { cursor: pointer; }

        /* ── Utilities ─────────────────────────────── */
        .text-muted  { color: var(--text-muted); }
        .text-green  { color: var(--accent-green); }
        .text-red    { color: var(--accent-red); }
        .text-amber  { color: var(--accent-amber); }
        .text-blue   { color: var(--accent-blue); }
        .text-right  { text-align: right; }
        .font-mono   { font-family: 'Courier New', monospace; font-size: 12px; }
        .font-bold   { font-weight: 700; }
        .mt-4  { margin-top: 16px; }
        .mb-4  { margin-bottom: 16px; }
        .mt-6  { margin-top: 24px; }
        .mb-6  { margin-bottom: 24px; }
        .gap-4 { gap: 16px; }
        .flex  { display: flex; align-items: center; }
        .flex-between { display: flex; align-items: center; justify-content: space-between; }
        .empty-state { text-align: center; padding: 48px 24px; color: var(--text-muted); }
        .empty-state p { margin-top: 8px; font-size: 13px; }

        /* ── Light Mode Specific Contrast Polish ───── */
        [data-theme="light"] .badge-green   { background: rgba(5,150,105,0.12);  color: #047857; }
        [data-theme="light"] .badge-amber   { background: rgba(217,119,6,0.12);   color: #b45309; }
        [data-theme="light"] .badge-red     { background: rgba(220,38,38,0.12);   color: #b91c1c; }
        [data-theme="light"] .badge-blue    { background: rgba(37,99,235,0.12);   color: #1d4ed8; }
        [data-theme="light"] .badge-purple  { background: rgba(79,70,229,0.12);   color: #4338ca; }
        [data-theme="light"] .badge-gray    { background: rgba(100,116,139,0.12); color: #334155; }
        [data-theme="light"] .badge-teal    { background: rgba(13,148,136,0.12);  color: #0f766e; }

        [data-theme="light"] .stat-icon.purple { background: rgba(79,70,229,0.12); color: #4338ca; }
        [data-theme="light"] .stat-icon.green  { background: rgba(5,150,105,0.12); color: #047857; }
        [data-theme="light"] .stat-icon.amber  { background: rgba(217,119,6,0.12); color: #b45309; }
        [data-theme="light"] .stat-icon.red    { background: rgba(220,38,38,0.12); color: #b91c1c; }
        [data-theme="light"] .stat-icon.blue   { background: rgba(37,99,235,0.12); color: #1d4ed8; }
        [data-theme="light"] .stat-icon.teal   { background: rgba(13,148,136,0.12); color: #0f766e; }

        /* ── Theme Toggle Button ─────────────────── */
        .theme-toggle-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            background: var(--bg-card);
            border: 1px solid var(--border);
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
            padding: 0;
            flex-shrink: 0;
        }
        .theme-toggle-btn:hover {
            background: var(--bg-card-hover);
            color: var(--text-primary);
            border-color: var(--border-glow);
            box-shadow: 0 0 16px var(--primary-glow);
            transform: translateY(-1px);
        }
        .theme-toggle-btn:active {
            transform: scale(0.95);
        }
        .theme-toggle-btn .theme-icon {
            width: 17px;
            height: 17px;
            transition: transform 0.25s ease;
        }
        .theme-toggle-btn:hover .theme-icon {
            transform: rotate(15deg);
        }
        [data-theme="dark"] .theme-icon-sun {
            display: inline-block !important;
            color: #f59e0b;
        }
        [data-theme="dark"] .theme-icon-moon {
            display: none !important;
        }
        [data-theme="light"] .theme-icon-sun {
            display: none !important;
        }
        [data-theme="light"] .theme-icon-moon {
            display: inline-block !important;
            color: #4f46e5;
        }

        /* ── Animations ────────────────────────────── */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fadeInUp 0.4s ease forwards; }
        .fade-in-2 { animation: fadeInUp 0.4s 0.1s ease both; }
        .fade-in-3 { animation: fadeInUp 0.4s 0.2s ease both; }
        .fade-in-4 { animation: fadeInUp 0.4s 0.3s ease both; }

        @media (max-width: 1024px) {
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-wrapper { margin-left: 0; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
    @stack('styles')
</head>
<body>

<!-- ── Sidebar ───────────────────────────────────────── -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-mark">
            <div class="logo-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <div class="logo-text">
                <h1>EEIG Scaffolding</h1>
                <span>Inventory System</span>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <span class="nav-section-label">Overview</span>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i data-lucide="layout-dashboard" class="nav-icon"></i>
            Dashboard
        </a>

        <span class="nav-section-label">Inventory</span>
        <a href="{{ route('sites.index') }}" class="nav-item {{ request()->routeIs('sites.*') ? 'active' : '' }}">
            <i data-lucide="building-2" class="nav-icon"></i>
            Project Sites
        </a>
        <a href="{{ route('materials.index') }}" class="nav-item {{ request()->routeIs('materials.*') ? 'active' : '' }}">
            <i data-lucide="package" class="nav-icon"></i>
            Materials Catalog
        </a>
        <a href="{{ route('transactions.index') }}" class="nav-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
            <i data-lucide="arrow-left-right" class="nav-icon"></i>
            Stock Movements
        </a>

        <span class="nav-section-label">Finance</span>
        <a href="{{ route('rentals.index') }}" class="nav-item {{ request()->routeIs('rentals.*') ? 'active' : '' }}">
            <i data-lucide="receipt" class="nav-icon"></i>
            Rental Records
        </a>
        <a href="{{ route('reports.company-balance') }}" class="nav-item {{ request()->routeIs('reports.company-balance') ? 'active' : '' }}">
            <i data-lucide="bar-chart-3" class="nav-icon"></i>
            Company Balance
        </a>
        <a href="{{ route('reports.cost-summary') }}" class="nav-item {{ request()->routeIs('reports.cost-summary') ? 'active' : '' }}">
            <i data-lucide="file-spreadsheet" class="nav-icon"></i>
            Cost Summary
        </a>
        <a href="{{ route('reports.anomalies') }}" class="nav-item {{ request()->routeIs('reports.anomalies') ? 'active' : '' }}">
            <i data-lucide="alert-triangle" class="nav-icon"></i>
            Anomaly Audit
            @php $openAnomalies = \App\Models\StockAnomaly::where('status','open')->count(); @endphp
            @if($openAnomalies > 0)
                <span class="nav-badge">{{ $openAnomalies }}</span>
            @endif
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar">A</div>
            <div class="user-info">
                <p>EEIG Admin</p>
                <span>Administrator</span>
            </div>
        </div>
    </div>
</aside>

<!-- ── Main Content ──────────────────────────────────── -->
<div class="main-wrapper">
    <header class="topbar">
        <div class="topbar-breadcrumb">
            <span class="page-title">@yield('page-title', 'Dashboard')</span>
            @hasSection('page-subtitle')
                <span class="text-muted">·</span>
                <span class="page-sub">@yield('page-subtitle')</span>
            @endif
        </div>
        <div class="topbar-actions">
            @yield('topbar-actions')

            <button type="button" class="theme-toggle-btn" id="themeToggleBtn" aria-label="Toggle theme" title="Toggle theme (Light / Dark)">
                <i data-lucide="sun" class="theme-icon theme-icon-sun"></i>
                <i data-lucide="moon" class="theme-icon theme-icon-moon"></i>
            </button>
        </div>
    </header>

    <main class="page-content">
        @if(session('success'))
            <div class="alert alert-success">
                <i data-lucide="check-circle-2" style="flex-shrink:0;width:18px;height:18px;"></i>
                <span class="alert-text">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">
                <i data-lucide="x-circle" style="flex-shrink:0;width:18px;height:18px;"></i>
                <span class="alert-text">{{ session('error') }}</span>
            </div>
        @endif
        @yield('content')
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            lucide.createIcons();
        }

        const themeToggleBtn = document.getElementById('themeToggleBtn');
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', () => {
                const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
                const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', nextTheme);
                localStorage.setItem('theme', nextTheme);
                if (window.lucide) {
                    lucide.createIcons();
                }
            });
        }
    });
</script>
@stack('scripts')
</body>
</html>
