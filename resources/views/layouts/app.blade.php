<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Tontine Pro') }} - @yield('page-title', 'Tableau de Bord')</title>

    <!-- Google Fonts - Police moderne -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- Scripts -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])

    <!-- Styles -->
    <!-- Styles -->
    <style>
        /* Variables CSS modernes - Version Sidebar Bleue */
        :root {
            --sidebar-width: 280px;

            /* COULEURS PRINCIPALES */
            --primary-color: #3b82f6;

            /* NOUVELLE PALETTE SIDEBAR (BLEU FONCÉ) */
            --sidebar-bg: #0f172a;             /* Fond très foncé (Slate 900) ou utiliser un gradient */
            --sidebar-gradient: linear-gradient(180deg, #1e3a8a 0%, #172554 100%); /* Dégradé Bleu Royal */

            --sidebar-text: #e2e8f0;           /* Texte blanc cassé (Slate 200) */
            --sidebar-text-muted: #94a3b8;     /* Texte secondaire plus sombre */
            --sidebar-hover: rgba(255, 255, 255, 0.1); /* Effet verre blanc transparent */
            --sidebar-active: rgba(255, 255, 255, 0.2); /* Effet verre plus marqué */

            /* HEADER ET CONTENU - SETUP VARIABLES DE BASE */
            --header-bg: #ffffff;
            --main-bg: #f1f5f9;                /* Fond de page gris très léger */
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --input-bg: #ffffff;
            --table-head-bg: #f8fafc;
            --table-hover-bg: #f1f5f9;
            --table-stripe-bg: rgba(0,0,0,0.02);

            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --radius-md: 10px;
            --transition: all 0.2s ease;
        }

        /* DEFINITION DU DARK MODE AU NIVEAU GLOBAL */
        .dark-mode {
            --header-bg: #1e293b;
            --main-bg: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --border-color: #475569;  /* Bordures plus visibles en dark mode */
            --input-bg: #0f172a;
            --table-head-bg: #1e293b;  /* Plus foncé pour meilleur contraste */
            --table-hover-bg: rgba(59, 130, 246, 0.15);  /* Hover plus visible */
            --table-stripe-bg: rgba(255,255,255,0.03);  /* Rayures plus subtiles */

            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.2);
        }

        /* Appliquer la police Inter par défaut */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--main-bg);
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* Layout principal */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* ===== SIDEBAR MODERNE (VERSION BLEUE) ===== */
        .global-sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-gradient); /* Utilisation du dégradé */
            color: var(--sidebar-text);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1000;
            overflow-y: auto;
            transition: var(--transition);
            box-shadow: 4px 0 15px rgba(0,0,0,0.1); /* Ombre plus marquée */
            border-right: none; /* Plus de bordure car le contraste suffit */
        }

        .sidebar-content {
            padding: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* En-tête de la sidebar */
        .sidebar-header {
            padding: 30px 25px 25px;
            background: transparent;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1); /* Bordure subtile blanche */
            position: relative;
        }

        /* Avatar utilisateur */
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 700;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* Menu de navigation */
        .nav-menu {
            list-style: none;
            padding: 25px 15px; /* Marges latérales réduites */
            margin: 0;
            flex: 1;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: var(--sidebar-text); /* Texte clair */
            text-decoration: none;
            border-radius: var(--radius-md);
            transition: var(--transition);
            font-weight: 500;
            font-size: 0.95rem;
            border: 1px solid transparent;
        }

        /* Suppression de la barre latérale colorée du design précédent */
        .nav-link::before {
            display: none;
        }

        .nav-link:hover {
            background: var(--sidebar-hover);
            color: #ffffff !important;
            transform: translateX(4px);
        }

        .nav-link.active {
            background: var(--primary-color); /* Bleu vif pour l'actif */
            color: white !important;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .nav-icon {
            width: 24px;
            margin-right: 12px;
            text-align: center;
            font-size: 1.1rem;
            opacity: 0.8;
            color: inherit; /* L'icône prend la couleur du texte */
        }

        .nav-link:hover .nav-icon, .nav-link.active .nav-icon {
            opacity: 1;
        }

        .nav-text {
            font-family: 'Poppins', sans-serif;
            letter-spacing: 0.3px;
        }

        /* Section badge de rôle */
        .role-badge {
            display: inline-block;
            padding: 4px 10px;
            background: rgba(255, 255, 255, 0.15); /* Fond blanc transparent */
            border-radius: 20px;
            font-size: 0.70rem;
            font-weight: 600;
            color: #ffffff; /* Texte blanc */
            margin-top: 5px;
            letter-spacing: 0.5px;
        }

        /* Pied de sidebar */
        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.1);
        }

        .sidebar-footer .btn-outline-light {
            color: #cbd5e1;
            border-color: rgba(255, 255, 255, 0.3);
        }

        .sidebar-footer .btn-outline-light:hover {
            background: white;
            color: var(--primary-color);
            border-color: white;
        }

        /* ===== CONTENU PRINCIPAL ===== */
        .main-content-with-sidebar {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background: var(--main-bg);
            position: relative;
            transition: background-color 0.3s ease;
        }

        /* Header principal - Blanc pur maintenant */
        .main-header {
            background: var(--card-bg);
            padding: 15px 30px;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0px;
            z-index: 999;
            box-shadow: var(--shadow); /* Ombre douce sous le header blanc */
            transition: transform 0.3s ease-in-out, background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .main-header.header-hidden {
            transform: translateY(-100%);
        }

        .main-header h4 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: var(--text-main); /* Titre foncé */
            margin-bottom: 2px;
            transition: color 0.3s ease;
        }

        /* Couleur du sous-titre */
        .tab_de_bord small {
            color: var(--text-muted);
            transition: color 0.3s ease;
        }

        /* Zone de contenu */
        .page-content {
            padding: 30px;
            max-width: 100%;
        }

        /* Cartes ou contenu blanc */
        .bg-white-card {
            background: var(--card-bg);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            transition: background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
        }

        /* Amélioration de la zone de recherche */
        .search-box {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--main-bg); /* Fond légèrement gris */
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .search-box:focus-within {
            background: var(--card-bg);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        .search-box .form-control {
            background: transparent;
            color: var(--text-main);
        }
        .search-box .form-control::placeholder {
            color: var(--text-muted);
        }
        .search-box .input-group-text {
            color: var(--text-muted);
        }

        /* Bouton de notification */
        .notification-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%; /* Rond */
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: all 0.2s;
        }

        .notification-btn:hover {
            color: var(--primary-color);
            background: #eff6ff;
            border-color: #bfdbfe;
        }
        /*== Ces style concerne index des prets */
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .table-warning {
            background-color: #fff3cd !important;
        }

        .text-decoration-line-through {
            text-decoration: line-through;
        }
        /* Dans votre fichier CSS principal */
        .card-title {
            font-size: 0.9rem;
            font-weight: 600;
        }

        /* Style pour les indicateurs de filtre */
        .bg-warning-subtle {
            background-color: #fff3cd !important;
        }

        /* Badges personnalisés */
        .badge-confirmation {
            background-color: #dc3545;
            color: white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }

        /* Style pour les dates modifiées */
        .text-date-modifiee {
            border-left: 3px solid #ffc107;
            padding-left: 10px;
            background-color: #fff8e1;
            border-radius: 4px;
        }

        /* Style responsive pour les filtres */
        @media (max-width: 768px) {
            .row.g-3 {
                margin-bottom: 0.5rem !important;
            }
        }

        /* ===== RESPONSIVE DESIGN (Adapté pour mobile) ===== */
        @media (max-width: 1200px) {
            :root { --sidebar-width: 240px; }
        }

        @media (max-width: 992px) {
            :root { --sidebar-width: 80px; }
            .global-sidebar .nav-text, .global-sidebar .user-info, .global-sidebar .role-badge { display: none; }
            .sidebar-header { padding: 20px 10px; display: flex; justify-content: center; }
            .user-avatar { margin: 0 !important; width: 45px; height: 45px; }
            .nav-link { justify-content: center; padding: 15px; }
            .nav-icon { margin-right: 0; font-size: 1.3rem; }
            .main-content-with-sidebar { margin-left: 80px; }
        }

        @media (max-width: 768px) {
            :root { --sidebar-width: 100%; --sidebar-height: 70px; }

            .app-container { flex-direction: column; }

            .global-sidebar {
                width: 100%;
                height: var(--sidebar-height);
                position: fixed;
                bottom: 0; top: auto; left: 0; right: 0;
                background: var(--card-bg); /* Sur mobile, on remet le fond blanc pour le menu du bas */
                border-top: 1px solid var(--border-color);
                box-shadow: 0 -4px 20px rgba(0,0,0,0.05);
            }

            /* Sur mobile, les liens redeviennent foncés car le fond est blanc */
            .global-sidebar .nav-link {
                color: var(--text-muted);
                flex-direction: column;
                padding: 8px;
                font-size: 0.7rem;
            }
            .global-sidebar .nav-link.active {
                background: transparent;
                color: var(--primary-color) !important;
                box-shadow: none; border: none;
            }
            .global-sidebar .nav-icon { margin-bottom: 4px; font-size: 1.2rem; }
            .global-sidebar .nav-text { display: block; font-size: 0.65rem; }

            .sidebar-header, .sidebar-footer { display: none; }
            .nav-menu { display: flex; justify-content: space-around; padding: 0; margin-top: 5px;}
            .nav-item { margin: 0; flex: 1; text-align: center; }

            .main-content-with-sidebar { margin-left: 0; margin-bottom: var(--sidebar-height); }
        }

        /* ===== COMPOSANTS & VARIABLES SUPPORT ===== */
        .card, .modal-content {
            background-color: var(--card-bg);
            color: var(--text-main);
            border-color: var(--border-color);
        }

        .form-control, .form-select {
            background-color: var(--input-bg);
            border-color: var(--border-color);
            color: var(--text-main);
        }

        .form-control::placeholder {
            color: var(--text-muted);
            opacity: 0.7;
        }

        /* Tables Modernes */
        .table {
            color: var(--text-main);
            background-color: transparent;
        }

        .table thead th {
             background-color: var(--table-head-bg);
             border-color: var(--border-color);
             color: var(--text-muted);
             border-bottom: 2px solid var(--border-color);
        }

        .table td, .table th {
             border-color: var(--border-color);
        }

        .table-striped > tbody > tr:nth-of-type(odd) > * {
            color: var(--text-main);
            box-shadow: inset 0 0 0 9999px var(--table-stripe-bg);
        }

        .table-hover > tbody > tr:hover > * {
            color: var(--text-main);
            box-shadow: inset 0 0 0 9999px var(--table-hover-bg);
        }

        .dropdown-menu {
             background-color: var(--card-bg);
             border-color: var(--border-color);
        }
        .dropdown-item {
             color: var(--text-main);
        }
        .dropdown-item:hover {
             background-color: var(--table-hover-bg);
             color: var(--text-main);
        }

        /* Animation simple */
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .page-content { animation: fadeIn 0.4s ease-out; }
    </style>
    @stack('styles')

    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0d6efd">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
</head>
<body>
<div class="app-container">
    <!-- ===== SIDEBAR GLOBALE ===== -->
    <nav class="global-sidebar">
        <div class="sidebar-content">
            <!-- En-tête Sidebar -->
            <div class="sidebar-header">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        @if(Auth::check())
                            <x-user-avatar :user="Auth::user()" size="45" :circle="true" class="rounded-3 shadow-sm" />
                        @endif
                    </div>
                    <div class="user-info">
                        @if(Auth::check())
                            <h6 class="mb-0 fw-bold" style="font-family: 'Poppins', sans-serif;">
                                {{ Auth::user()->name }}
                            </h6>
                            <div class="role-badge">
                                @switch(Auth::user()->role)
                                    @case('admin')
                                        <i class="fas fa-crown me-1"></i> Administrateur
                                        @break
                                    @case('tresorier')
                                        <i class="fas fa-coins me-1"></i> Trésorier
                                        @break
                                    @case('membre')
                                        <i class="fas fa-user me-1"></i> Membre Actif
                                        @break
                                @endswitch
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Menu Navigation Principal -->
            <ul class="nav-menu">
                <!-- Tableau de Bord -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <span class="nav-text">Tableau de Bord</span>
                    </a>
                </li>

                <!-- Menu selon rôle -->
                @auth
                    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'tresorier')
                        <li class="nav-item">
                            <a href="{{ route('cycles.index') }}" class="nav-link {{ request()->routeIs('cycles.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-sync-alt"></i>
                                <span class="nav-text">Gestion Cycles</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('seances.index') }}" class="nav-link {{ request()->routeIs('seances.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <span class="nav-text">Séances</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('prets.index') }}" class="nav-link {{ request()->routeIs('prets.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-hand-holding-usd"></i>
                                <span class="nav-text">Gestion Prêts</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('users.index') }}" class="nav-link">
                                <i class="nav-icon fas fa-chart-pie"></i>
                                <span class="nav-text">Gestion des utilisateurs</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('sanctions.index') }}" class="nav-link {{ request()->routeIs('sanctions.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-gavel"></i>
                                <span class="nav-text">Sanctions</span>
                            </a>
                        </li>

                    @endif

                    @if(Auth::user()->role === 'membre')
                        <li class="nav-item">
                            <a href="{{ route('prets.index') }}" class="nav-link {{ request()->routeIs('prets.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-invoice-dollar"></i>
                                <span class="nav-text">Mes Prêts</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-history"></i>
                                <span class="nav-text">Historique</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-file-contract"></i>
                                <span class="nav-text">Mes Contrats</span>
                            </a>
                        </li>
                    @endif

                    <!-- Tous les participants -->
                    <li class="nav-item">
                        <a href="{{ route('gains.historique') }}" class="nav-link {{ request()->routeIs('gains.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-gift"></i>
                            <span class="nav-text">Mes Gains</span>
                        </a>
                    </li>
                    @php
                        $activeCycleSidebar = \App\Models\Cycle::where('est_actif', true)->first();
                    @endphp
                    @if($activeCycleSidebar)
                        <li class="nav-item">
                            <a href="{{ route('cycles.public', $activeCycleSidebar) }}" class="nav-link {{ request()->routeIs('cycles.public') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-list-ol"></i>
                                <span class="nav-text">Ordre de Passage</span>
                            </a>
                        </li>
                    @endif

                    <!-- Menu Commun -->
                    <li class="nav-item">
                        <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-cog"></i>
                            <span class="nav-text">Mon Profil</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-question-circle"></i>
                            <span class="nav-text">Aide & Support</span>
                        </a>
                    </li>
                @endauth
            </ul>

            <!-- Pied de sidebar -->
            <div class="sidebar-footer">
                @auth


                    <!-- Déconnexion -->
                    <form method="POST" action="{{ route('logout') }}" class="w-100">
                        @csrf
                        <button type="submit" class="btn btn-outline-light w-100">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            <span class="nav-text">Déconnexion</span>
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    <!-- ===== CONTENU PRINCIPAL ===== -->
    <div class="main-content-with-sidebar">
        <!-- Header Principal -->
        <header class="main-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="tab_de_bord">
                    <h4 class="mb-0">
                        @yield('page-title', 'Tableau de Bord')
                    </h4>
                    <small class="text-muted" style="font-size: 0.9rem;">
                        @yield('page-subtitle', 'Bienvenue dans votre espace de gestion')
                    </small>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <!-- Dark Mode Toggle -->
                    <button class="btn btn-light" onclick="toggleDarkMode()" style="border-radius: 10px; padding: 10px 15px; background: var(--card-bg); border-color: var(--border-color); color: var(--text-main);">
                        <i id="darkModeIcon" class="fas fa-moon"></i>
                    </button>

                    <!-- Unified Notification Bell -->
                    @auth
                        <div class="dropdown">
                            <a href="#" class="btn btn-light position-relative" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 10px; padding: 10px 15px;">
                                <i class="fas fa-bell" style="font-size: 1.1rem; color: #64748b;"></i>
                                @if(isset($unreadCount) && $unreadCount > 0)
                                    <span id="notifBadgeCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                        {{ $unreadCount }}
                                    </span>
                                @endif
                            </a>

                            <!-- Dropdown Menu -->
                            <div class="dropdown-menu dropdown-menu-end p-0 border-0 shadow-lg" style="width: 350px; border-radius: 12px; z-index: 1050;">
                                <!-- Header -->
                                <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <i class="fas fa-bullhorn me-2 text-primary"></i>Annonces
                                    </h6>
                                    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'tresorier')
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#sidebarAnnonceModal">
                                            <i class="fas fa-plus me-1"></i> Nouvelle
                                        </button>
                                    @endif
                                </div>

                                <!-- Announcements List -->
                                <div style="max-height: 400px; overflow-y: auto;">
                                    @if(isset($globalAnnonces) && count($globalAnnonces) > 0)
                                        @foreach($globalAnnonces as $annonce)
                                            @php
                                                $isRead = $annonce->isReadBy(Auth::user());
                                                $bgClass = $isRead ? '' : 'bg-light';
                                            @endphp
                                            <a href="#"
                                               class="dropdown-item p-3 border-bottom {{ $bgClass }}"
                                               data-bs-toggle="modal"
                                               data-bs-target="#modalAnnonce{{ $annonce->id }}"
                                               onclick="markAnnonceAsRead({{ $annonce->id }})">
                                                <div class="d-flex align-items-start">
                                                    <div class="flex-shrink-0 pt-1">
                                                        <i class="fas fa-info-circle text-primary"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-1 fw-bold text-truncate" style="max-width: 250px;">
                                                            {{ $annonce->titre }}
                                                        </h6>
                                                        <p class="mb-1 small text-muted text-truncate">
                                                            {{ Str::limit($annonce->message, 50) }}
                                                        </p>
                                                        <small class="text-primary" style="font-size: 0.75rem;">
                                                            {{ $annonce->created_at->diffForHumans() }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </a>
                                        @endforeach
                                    @else
                                        <div class="p-5 text-center text-muted">
                                            <i class="fas fa-bell-slash mb-2 fs-3 opacity-25"></i>
                                            <p class="mb-0 small">Aucune annonce pour le moment</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endauth

                    <!-- Barre de recherche -->
                    <div class="input-group search-box" style="max-width: 350px; position: relative;">
                        <span class="input-group-text bg-transparent border-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="globalSearchInput" class="form-control border-0 ps-0" placeholder="Rechercher dans les tableaux...">
                        <button id="clearSearchBtn" class="btn btn-sm" style="display: none; position: absolute; right: 5px; top: 50%; transform: translateY(-50%); z-index: 10; padding: 2px 8px; background: transparent; border: none; color: var(--text-muted); font-size: 1.2rem;" title="Effacer la recherche">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </div>
                    <small id="searchResultCount" style="display: none; color: var(--text-muted); font-size: 0.75rem; margin-left: 10px;"></small>
                </div>
            </div>
        </header>

        <!-- Contenu de la page -->
        <main class="page-content">
            {{ $slot ?? '' }}
        </main>
    </div>
</div>

<!-- Modal Annonce pour Sidebar -->
@auth
    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'tresorier')
        <div class="modal fade" id="sidebarAnnonceModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-md);">
                    <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%); border-radius: var(--radius-md) var(--radius-md) 0 0; padding: 25px;">
                        <h5 class="modal-title text-white fw-bold">
                            <i class="fas fa-bullhorn me-2"></i> Nouvelle Annonce
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form action="{{ route('annonces.store') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-bold mb-2">Titre de l'annonce</label>
                                <input type="text" name="titre" class="form-control form-control-lg" placeholder="Ex: Changement d'horaire" required style="border-radius: var(--radius-md); padding: 12px;">
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold mb-2">Message</label>
                                <textarea name="message" class="form-control" rows="5" placeholder="Contenu de votre annonce..." required style="border-radius: var(--radius-md); resize: none;"></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg" style="background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%); border: none; border-radius: var(--radius-md); padding: 14px; font-weight: 600;">
                                    <i class="fas fa-paper-plane me-2"></i> Publier l'annonce
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endauth

<!-- Scripts -->
<!-- ZONE DES MODALS (À PLACER JUSTE AVANT LA FIN DU BODY) -->
@if(isset($globalAnnonces))
    @foreach($globalAnnonces as $annonce)
        <div class="modal fade" id="modalAnnonce{{ $annonce->id }}" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fs-6 fw-bold">
                            <i class="fa-solid fa-bullhorn me-2"></i> {{ $annonce->titre }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-dark" style="white-space: pre-line;">{{ $annonce->message }}</p>
                        <hr class="my-4 opacity-10">
                        <small class="text-muted">
                            <i class="far fa-clock me-1"></i> Publié le {{ $annonce->created_at->format('d/m/Y à H:i') }}
                        </small>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-sm btn-secondary px-4" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif

<!-- SCRIPT DE GESTION (À PLACER APRÈS LES MODALS) -->
<script>
    // Liste pour éviter de décrémenter deux fois la même annonce
    let viewedAnnonces = [];

    function markAnnonceAsRead(id) {
        // Gestion du compteur rouge
        if (!viewedAnnonces.includes(id)) {
            fetch('/annonces/' + id + '/read').catch(e => console.log(e));

            const badge = document.querySelector('.position-absolute.top-0.start-100.translate-middle.badge');
            if (badge) {
                let count = parseInt(badge.innerText);
                if (count > 0) {
                    count = count - 1;
                    badge.innerText = count;
                }
                if (count <= 0) {
                    badge.style.display = 'none';
                }
            }
            viewedAnnonces.push(id);
        }
    }

    // Scroll Header Logic
    let lastScrollTop = 0;
    const header = document.querySelector('.main-header');

    window.addEventListener('scroll', function() {
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (scrollTop > lastScrollTop && scrollTop > 70) {
            // Scroll Down
            if(header) {
                header.style.transform = 'translateY(-100%)';
                header.style.transition = 'transform 0.3s ease-in-out';
            }
        } else {
            // Scroll Up
            if(header) {
                header.style.transform = 'translateY(0)';
            }
        }
        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    }, false);

    // Gestion du Mode Sombre
    function toggleDarkMode() {
        const isDark = document.documentElement.classList.toggle('dark-mode');
        localStorage.setItem('dark-mode', isDark ? 'enabled' : 'disabled');
        updateDarkModeIcon(isDark);
    }

    function updateDarkModeIcon(isDark) {
        const icon = document.getElementById('darkModeIcon');
        if (icon) {
            icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
        }
    }

    // Au chargement
    document.addEventListener('DOMContentLoaded', function() {
        // Dark mode initialization
        if (localStorage.getItem('dark-mode') === 'enabled') {
            document.documentElement.classList.add('dark-mode');
            updateDarkModeIcon(true);
        }

        // Enhanced Search Tool
        const searchInput = document.getElementById('globalSearchInput');
        const clearBtn = document.getElementById('clearSearchBtn');
        const resultCount = document.getElementById('searchResultCount');

        if(searchInput && clearBtn && resultCount) {
            searchInput.addEventListener('keyup', function() {
                const value = this.value.toLowerCase().trim();
                const tables = document.querySelectorAll('table tbody');
                let totalVisible = 0;
                let totalRows = 0;

                tables.forEach(table => {
                    const rows = table.querySelectorAll('tr');

                    rows.forEach(row => {
                        totalRows++;
                        const text = row.textContent.toLowerCase();
                        const isVisible = value === '' || text.indexOf(value) > -1;
                        row.style.display = isVisible ? '' : 'none';

                        if(isVisible) {
                            totalVisible++;
                        }
                    });
                });

                // Afficher/masquer le bouton clear et le compteur
                if(value) {
                    clearBtn.style.display = 'block';
                    resultCount.style.display = 'inline';
                    resultCount.textContent = `${totalVisible} résultat${totalVisible > 1 ? 's' : ''} sur ${totalRows}`;

                    // Message si aucun résultat
                    if(totalVisible === 0) {
                        resultCount.style.color = '#ef4444'; // Rouge
                        resultCount.textContent = 'Aucun résultat trouvé';
                    } else {
                        resultCount.style.color = 'var(--text-muted)';
                    }
                } else {
                    clearBtn.style.display = 'none';
                    resultCount.style.display = 'none';
                }
            });

            // Bouton clear
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('keyup'));
                searchInput.focus();
            });

            // Raccourci clavier Ctrl+K ou Cmd+K pour focus search
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    searchInput.focus();
                }
            });
        }
    });

    // Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js')
                .then(function(registration) {
                    console.log('ServiceWorker registered: ', registration.scope);
                })
                .catch(function(error) {
                    console.log('ServiceWorker registration failed: ', error);
                });
        });
    }
</script>

<!-- Global Loading Spinner -->
<div id="globalLoadingSpinner" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; backdrop-filter: blur(3px);">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
        <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem; border-width: 0.3rem;">
            <span class="visually-hidden">Chargement...</span>
        </div>
        <p class="text-white mt-3 fw-bold">Chargement en cours...</p>
    </div>
</div>

<script>
    // Global Form Loading Handler
    document.addEventListener('DOMContentLoaded', function() {
        const spinner = document.getElementById('globalLoadingSpinner');

        // Intercept all form submissions
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                // Don't show spinner for search forms or forms with data-no-spinner attribute
                if (form.hasAttribute('data-no-spinner') || form.querySelector('input[type="search"]')) {
                    return;
                }

                // Show spinner
                spinner.style.display = 'block';

                // Disable submit button to prevent double submission
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                }
            });
        });

        // Hide spinner on page load (in case of back button or error)
        window.addEventListener('pageshow', function() {
            spinner.style.display = 'none';
        });
    });
</script>

@stack('scripts')
</body>
</html>
