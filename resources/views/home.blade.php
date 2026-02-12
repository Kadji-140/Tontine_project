<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TontinePro - Gestion Professionnelle de Tontines au Cameroun</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        /* ===== VARIABLES & RESET ===== */
        :root {
            /* Palette de couleurs professionnelle */
            --primary: #0066cc;       /* Bleu professionnel */
            --primary-dark: #0052a3;
            --primary-light: #4d94ff;
            --secondary: #00a86b;     /* Vert confiance */
            --accent: #ff6b35;        /* Orange énergie */
            --light: #f8f9fa;
            --dark: #1a1a1a;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --white: #ffffff;
            --shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 15px 50px rgba(0, 0, 0, 0.12);
            --radius: 12px;
            --radius-lg: 20px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--dark);
            background-color: var(--white);
            line-height: 1.6;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            line-height: 1.3;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: var(--transition);
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .section {
            padding: 100px 0;
        }

        .section-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: var(--gray);
            max-width: 600px;
            margin-bottom: 50px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 32px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            border-radius: var(--radius);
            border: none;
            cursor: pointer;
            transition: var(--transition);
            font-size: 1rem;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            box-shadow: 0 4px 15px rgba(0, 102, 204, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 102, 204, 0.4);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-secondary:hover {
            background: var(--primary);
            color: var(--white);
        }

        .btn-lg {
            padding: 18px 40px;
            font-size: 1.1rem;
        }

        /* ===== HEADER ===== */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 20px 0;
            transition: var(--transition);
        }

        .header.scrolled {
            padding: 15px 0;
            box-shadow: var(--shadow);
        }

        .header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .logo i {
            color: var(--secondary);
        }

        .nav {
            display: flex;
            gap: 40px;
        }

        .nav a {
            font-weight: 500;
            position: relative;
        }

        .nav a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: var(--transition);
        }

        .nav a:hover::after,
        .nav a.active::after {
            width: 100%;
        }

        /* ===== HERO SECTION ===== */
        .hero {
            padding: 180px 0 100px;
            background: linear-gradient(135deg,
            rgba(248, 249, 250, 0.95) 0%,
            rgba(248, 249, 250, 0.98) 100%),
            url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600" opacity="0.05"><path d="M0 0h1200v600H0z" fill="%230066cc"/><circle cx="300" cy="300" r="150" fill="%2300a86b"/><circle cx="900" cy="150" r="100" fill="%23ff6b35"/></svg>');
            background-size: cover;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            max-width: 600px;
        }

        .hero-title {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-title span {
            color: var(--primary);
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--gray);
            margin-bottom: 2.5rem;
            line-height: 1.8;
        }

        .hero-cta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .hero-image {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            max-width: 600px;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(-50%) translateX(0); }
            50% { transform: translateY(-50%) translateX(-20px); }
        }

        /* ===== STATS SECTION ===== */
        .stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--white);
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }

        .stats::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120"><path d="M0,0 L1200,0 L1200,120 C800,80 400,100 0,120 Z" fill="rgba(255,255,255,0.05)"/></svg>') no-repeat bottom;
            background-size: cover;
            opacity: 0.3;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .stat-item {
            padding: 40px 30px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: var(--radius-lg);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ffd700, #ffed4e);
            transform: scaleX(0);
            transition: var(--transition);
        }

        .stat-item:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .stat-item:hover::before {
            transform: scaleX(1);
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: #ffd700;
            transition: var(--transition);
        }

        .stat-item:hover .stat-icon {
            transform: rotate(360deg) scale(1.1);
            background: rgba(255, 215, 0, 0.3);
        }

        .stat-number {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            display: block;
            background: linear-gradient(135deg, #fff, #ffd700);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.95;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        /* ===== FEATURES SECTION ===== */
        .features {
            background: var(--white);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            margin-top: 50px;
        }

        .feature-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 40px 30px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            text-align: center;
            border: 1px solid var(--gray-light);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            color: var(--white);
            font-size: 2rem;
        }

        .feature-title {
            font-size: 1.4rem;
            margin-bottom: 15px;
            color: var(--dark);
        }

        .feature-description {
            color: var(--gray);
            line-height: 1.7;
        }

        /* ===== HOW IT WORKS ===== */
        .how-it-works {
            background: var(--light);
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 50px;
            position: relative;
        }

        .step {
            text-align: center;
            padding: 40px 25px;
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            position: relative;
            z-index: 1;
        }

        .step-number {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 auto 25px;
            position: relative;
        }

        /* ===== TESTIMONIALS ===== */
        .testimonials {
            background: var(--white);
        }

        .testimonial-slider {
            position: relative;
            max-width: 800px;
            margin: 50px auto 0;
        }

        .testimonial-card {
            background: var(--light);
            border-radius: var(--radius-lg);
            padding: 50px 40px;
            text-align: center;
            box-shadow: var(--shadow);
            position: relative;
        }

        .testimonial-text {
            font-size: 1.2rem;
            line-height: 1.8;
            color: var(--dark);
            margin-bottom: 30px;
            font-style: italic;
            position: relative;
        }

        .testimonial-text::before,
        .testimonial-text::after {
            content: '"';
            font-size: 3rem;
            color: var(--primary-light);
            opacity: 0.3;
            position: absolute;
        }

        .testimonial-text::before {
            top: -20px;
            left: -10px;
        }

        .testimonial-text::after {
            bottom: -40px;
            right: -10px;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .author-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
            font-size: 1.2rem;
        }

        /* ===== PRICING ===== */
        .pricing {
            background: var(--light);
            text-align: center;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .pricing-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 50px 40px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .pricing-card.popular {
            border-color: var(--primary);
            transform: scale(1.05);
        }

        .pricing-card.popular::before {
            content: 'POPULAIRE';
            position: absolute;
            top: 20px;
            right: -40px;
            background: var(--primary);
            color: var(--white);
            padding: 5px 40px;
            transform: rotate(45deg);
            font-size: 0.8rem;
            font-weight: 600;
        }

        .pricing-header {
            margin-bottom: 30px;
        }

        .pricing-title {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .pricing-price {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .pricing-period {
            color: var(--gray);
        }

        .pricing-features {
            list-style: none;
            margin: 30px 0;
            text-align: left;
        }

        .pricing-features li {
            padding: 10px 0;
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pricing-features li i {
            color: var(--secondary);
        }

        /* ===== CTA SECTION ===== */
        .cta-section {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            text-align: center;
            padding: 100px 0;
        }

        .cta-title {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .cta-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto 40px;
        }

        .cta-section .btn {
            background: var(--white);
            color: var(--primary);
            font-weight: 600;
        }

        .cta-section .btn:hover {
            background: var(--gray-light);
        }

        /* ===== FOOTER ===== */
        .footer {
            background: var(--dark);
            color: var(--white);
            padding: 80px 0 30px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 50px;
            margin-bottom: 50px;
        }

        .footer-logo {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--white);
        }

        .footer-description {
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.8;
            margin-bottom: 25px;
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            transition: var(--transition);
        }

        .social-links a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }

        .footer-links h4 {
            font-size: 1.2rem;
            margin-bottom: 25px;
            color: var(--white);
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
        }

        .footer-links a:hover {
            color: var(--white);
            padding-left: 5px;
        }

        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
        }

         .comparison-table {
             overflow-x: auto;
             margin: 0px 0;
             border-radius: var(--radius);
             box-shadow: var(--shadow);
         }

        .comparison-table table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .comparison-table th {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            padding: 20px;
            text-align: center;
            font-weight: 600;
        }

        .comparison-table td {
            padding: 20px;
            border-bottom: 1px solid var(--gray-light);
        }

        .comparison-table tr:nth-child(even) {
            background: var(--light);
        }

        .comparison-table tr:hover {
            background: rgba(0, 102, 204, 0.05);
        }

        .comparison-table td:first-child {
            font-weight: 600;
            color: var(--dark);
        }

        .comparison-table td:nth-child(2) {
            color: #dc3545;
        }

        .comparison-table td:nth-child(3) {
            color: var(--secondary);
        }

        .comparison-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .stat-box {
            text-align: center;
            padding: 30px;
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .stat-label {
            color: var(--gray);
            font-size: 1.1rem;
        }
        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .hero-image {
                display: none;
            }

            .hero {
                text-align: center;
                padding: 150px 0 80px;
            }

            .hero-content {
                max-width: 100%;
            }

            .hero-cta {
                justify-content: center;
            }

            .pricing-card.popular {
                transform: none;
            }
        }

        @media (max-width: 768px) {
            .section {
                padding: 80px 0;
            }

            .section-title {
                font-size: 2rem;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .nav {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
            }
        }

        @media (max-width: 576px) {
            .btn {
                width: 100%;
                justify-content: center;
            }

            .hero-cta {
                flex-direction: column;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .features-grid,
            .pricing-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
</head>
<body>
<!-- Header -->
<header class="header" id="header">
    <div class="container">
        <a href="#" class="logo">
            <i class="fas fa-hand-holding-usd"></i>
            TontinePro
        </a>

        <nav class="nav">
            <a href="#accueil" class="active">Accueil</a>
            <a href="#fonctionnalites">Fonctionnalités</a>
            <a href="#temoignages">Témoignages</a>
            <a href="#Comparaisons">Comparaisons</a>
            <a href="#contact">Contact</a>
            <button id="installAppBtn" class="btn btn-outline-light d-none">
                <i class="bi bi-download"></i> Installer l'App
            </button>
            <a href="{{ route('login') }}" class="btn btn-secondary">Connexion</a>
        </nav>
    </div>
</header>

<!-- Hero Section -->
<!-- Dans la section Hero, remplace le contenu entre <section class="hero"> et </section> -->
<section class="hero" id="accueil">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">
                Gestion Professionnelle de
                <span>Tontines Camerounaises</span>
            </h1>
            <p class="hero-subtitle">
                Digitalisez votre épargne collective avec une plateforme sécurisée,
                transparente et conforme aux traditions. Rejoignez des centaines
                d'associations qui font confiance à notre solution.
            </p>
            <div class="hero-cta">
                <a href="/register" class="btn btn-primary btn-lg">
                    <i class="fas fa-rocket"></i>
                    Commencer Gratuitement
                </a>
                <a href="#fonctionnalites" class="btn btn-secondary btn-lg">
                    <i class="fas fa-play-circle"></i>
                    Voir la Démo
                </a>
            </div>
        </div>

        <!-- CSS Avancé -->
        <style>
            .hero-image-pro {
                position: absolute;
                right: 0;
                top: 50%;
                transform: translateY(-50%);
                width: 45%;
                max-width: 600px;
                z-index: 2;
            }

            .hero-illustration-container {
                position: relative;
                width: 100%;
                height: 450px;
                perspective: 1000px;
            }

            /* Carte principale avec effet 3D */
            .illustration-card {
                position: relative;
                width: 100%;
                height: 100%;
                transform-style: preserve-3d;
                transition: transform 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }

            .illustration-front, .illustration-back {
                position: absolute;
                width: 100%;
                height: 100%;
                backface-visibility: hidden;
                border-radius: 25px;
                overflow: hidden;
                box-shadow:
                    0 25px 50px rgba(0, 102, 204, 0.2),
                    0 0 0 1px rgba(255, 255, 255, 0.1) inset;
            }

            .illustration-front {
                background: linear-gradient(145deg, #ffffff, #f8fafc);
                transform: rotateY(0deg);
            }

            .illustration-back {
                background: linear-gradient(145deg, #0066cc, #0052a3);
                transform: rotateY(180deg);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                padding: 30px;
                text-align: center;
            }

            /* Points flottants animés */
            .floating-dots {
                position: absolute;
                width: 100%;
                height: 100%;
                z-index: 1;
            }

            .floating-dot {
                position: absolute;
                border-radius: 50%;
                background: linear-gradient(135deg, #0066cc, #00a86b);
                animation: floatDot 15s infinite linear;
                opacity: 0.3;
            }

            /* Stats flottantes */
            .floating-stats {
                position: absolute;
                bottom: 20px;
                left: 20px;
                z-index: 3;
            }

            .stat-bubble {
                background: rgba(255, 255, 255, 0.95);
                border-radius: 15px;
                padding: 15px 20px;
                margin-bottom: 15px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
                animation: bounce 2s infinite;
                position: relative;
                overflow: hidden;
            }

            .stat-bubble::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 4px;
                height: 100%;
                background: linear-gradient(to bottom, #0066cc, #00a86b);
            }

            .stat-value {
                font-size: 1.8rem;
                font-weight: 700;
                color: #0066cc;
                margin-bottom: 5px;
            }

            .stat-label {
                font-size: 0.9rem;
                color: #666;
            }

            /* Animations */
            @keyframes float {
                0%, 100% {
                    transform: translateY(-50%) translateX(0) rotate(0deg);
                }
                33% {
                    transform: translateY(-50%) translateX(-15px) rotate(-1deg);
                }
                66% {
                    transform: translateY(-50%) translateX(15px) rotate(1deg);
                }
            }

            @keyframes floatDot {
                0% {
                    transform: translate(0, 0) rotate(0deg);
                    opacity: 0.3;
                }
                25% {
                    transform: translate(20px, -30px) rotate(90deg);
                    opacity: 0.5;
                }
                50% {
                    transform: translate(-15px, 20px) rotate(180deg);
                    opacity: 0.3;
                }
                75% {
                    transform: translate(30px, 15px) rotate(270deg);
                    opacity: 0.5;
                }
                100% {
                    transform: translate(0, 0) rotate(360deg);
                    opacity: 0.3;
                }
            }

            @keyframes bounce {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
            }

            @keyframes pulse {
                0% { transform: scale(1); opacity: 0.7; }
                50% { transform: scale(1.1); opacity: 1; }
                100% { transform: scale(1); opacity: 0.7; }
            }

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateX(100px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }

            /* Responsive */
            @media (max-width: 1200px) {
                .hero-image-pro {
                    width: 40%;
                }
            }

            @media (max-width: 992px) {
                .hero-image-pro {
                    position: relative;
                    width: 100%;
                    max-width: 500px;
                    margin: 40px auto 0;
                    top: 0;
                    transform: none;
                }

                .hero-illustration-container {
                    height: 350px;
                }
            }
        </style>

        <!-- HTML avec SVG amélioré -->
        <div class="hero-image-pro">
            <div class="hero-illustration-container">
                <!-- Points flottants -->
                <div class="floating-dots">
                    <div class="floating-dot" style="width: 20px; height: 20px; top: 10%; left: 15%;"></div>
                    <div class="floating-dot" style="width: 15px; height: 15px; top: 30%; right: 20%; animation-delay: -2s;"></div>
                    <div class="floating-dot" style="width: 25px; height: 25px; bottom: 20%; left: 25%; animation-delay: -5s;"></div>
                    <div class="floating-dot" style="width: 18px; height: 18px; bottom: 40%; right: 15%; animation-delay: -8s;"></div>
                </div>

                <!-- Carte d'illustration -->
                <div class="illustration-card">
                    <!-- Face avant -->
                    <div class="illustration-front">
                        <svg width="100%" height="100%" viewBox="0 0 500 450" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Fond avec texture subtile -->
                            <defs>
                                <linearGradient id="bgGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#ffffff"/>
                                    <stop offset="100%" stop-color="#f8fafc"/>
                                </linearGradient>

                                <pattern id="gridPattern" width="50" height="50" patternUnits="userSpaceOnUse">
                                    <path d="M 50 0 L 0 0 0 50" fill="none" stroke="#e6f2ff" stroke-width="1" opacity="0.3"/>
                                </pattern>

                                <filter id="glow">
                                    <feGaussianBlur stdDeviation="5" result="blur"/>
                                    <feMerge>
                                        <feMergeNode in="blur"/>
                                        <feMergeNode in="SourceGraphic"/>
                                    </feMerge>
                                </filter>
                            </defs>

                            <!-- Fond principal -->
                            <rect width="500" height="450" rx="20" fill="url(#bgGradient)"/>
                            <rect width="500" height="450" rx="20" fill="url(#gridPattern)" opacity="0.5"/>

                            <!-- Graphique animé -->
                            <g class="chart-animation">
                                <!-- Ligne de tendance -->
                                <path id="trendLine" d="M80 300Q150 250 200 270T300 250T380 200T450 180"
                                      stroke="#0066cc" stroke-width="4" fill="none" stroke-linecap="round"
                                      stroke-dasharray="1000" stroke-dashoffset="1000">
                                </path>

                                <!-- Points sur le graphique -->
                                <circle cx="80" cy="300" r="8" fill="#0066cc" class="chart-point">
                                    <animate attributeName="r" values="8;12;8" dur="2s" repeatCount="indefinite"/>
                                </circle>
                                <circle cx="200" cy="270" r="8" fill="#00a86b" class="chart-point">
                                    <animate attributeName="r" values="8;12;8" dur="2s" repeatCount="indefinite" begin="0.5s"/>
                                </circle>
                                <circle cx="300" cy="250" r="8" fill="#0066cc" class="chart-point">
                                    <animate attributeName="r" values="8;12;8" dur="2s" repeatCount="indefinite" begin="1s"/>
                                </circle>
                                <circle cx="380" cy="200" r="8" fill="#00a86b" class="chart-point">
                                    <animate attributeName="r" values="8;12;8" dur="2s" repeatCount="indefinite" begin="1.5s"/>
                                </circle>
                                <circle cx="450" cy="180" r="8" fill="#0066cc" class="chart-point">
                                    <animate attributeName="r" values="8;12;8" dur="2s" repeatCount="indefinite" begin="2s"/>
                                </circle>
                            </g>

                            <!-- Groupe communautaire animé -->
                            <g class="community-group" transform="translate(100, 100)">
                                <!-- Cercle de connexion -->
                                <circle cx="150" cy="100" r="60" stroke="#0066cc" stroke-width="2"
                                        stroke-dasharray="10,5" fill="none" opacity="0.5">
                                    <animateTransform attributeName="transform" type="rotate"
                                                      from="0 150 100" to="360 150 100"
                                                      dur="20s" repeatCount="indefinite"/>
                                </circle>

                                <!-- Membres -->
                                <g class="member" transform="translate(150, 100)">
                                    <circle r="25" fill="#ffd6cc">
                                        <animateTransform attributeName="transform" type="rotate"
                                                          from="0 0 0" to="360 0 0"
                                                          dur="15s" repeatCount="indefinite"/>
                                    </circle>
                                    <text text-anchor="middle" y="5" fill="#333" font-family="Arial" font-weight="600">M</text>
                                </g>

                                <g class="member" transform="translate(150, 100)">
                                    <circle r="25" fill="#ccd9ff">
                                        <animateTransform attributeName="transform" type="rotate"
                                                          from="120 0 0" to="480 0 0"
                                                          dur="15s" repeatCount="indefinite"/>
                                    </circle>
                                    <text text-anchor="middle" y="5" fill="#333" font-family="Arial" font-weight="600">J</text>
                                </g>

                                <g class="member" transform="translate(150, 100)">
                                    <circle r="25" fill="#d4ffcc">
                                        <animateTransform attributeName="transform" type="rotate"
                                                          from="240 0 0" to="600 0 0"
                                                          dur="15s" repeatCount="indefinite"/>
                                    </circle>
                                    <text text-anchor="middle" y="5" fill="#333" font-family="Arial" font-weight="600">A</text>
                                </g>
                            </g>

                            <!-- Pièces d'argent animées -->
                            <g class="money-animation">
                                <!-- Pièce 1 -->
                                <g class="coin" transform="translate(350, 250)">
                                    <circle r="20" fill="#ffd700" stroke="#ffb300" stroke-width="2">
                                        <animateTransform attributeName="transform" type="translate"
                                                          values="0,0; 0,-20; 0,0"
                                                          dur="3s" repeatCount="indefinite"/>
                                    </circle>
                                    <text text-anchor="middle" y="5" fill="#333" font-family="Arial" font-weight="700">5K</text>
                                    <animateTransform attributeName="transform" type="rotate"
                                                      from="0 350 250" to="360 350 250"
                                                      dur="8s" repeatCount="indefinite"/>
                                </g>

                                <!-- Pièce 2 -->
                                <g class="coin" transform="translate(400, 280)">
                                    <circle r="25" fill="#00a86b" stroke="#008055" stroke-width="2">
                                        <animateTransform attributeName="transform" type="translate"
                                                          values="0,0; 0,-15; 0,0"
                                                          dur="4s" repeatCount="indefinite" begin="1s"/>
                                    </circle>
                                    <text text-anchor="middle" y="5" fill="white" font-family="Arial" font-weight="700">10K</text>
                                    <animateTransform attributeName="transform" type="rotate"
                                                      from="360 400 280" to="0 400 280"
                                                      dur="10s" repeatCount="indefinite"/>
                                </g>

                                <!-- Pièce 3 -->
                                <g class="coin" transform="translate(300, 320)">
                                    <circle r="18" fill="#0066cc" stroke="#0052a3" stroke-width="2">
                                        <animateTransform attributeName="transform" type="translate"
                                                          values="0,0; 0,-25; 0,0"
                                                          dur="5s" repeatCount="indefinite" begin="2s"/>
                                    </circle>
                                    <text text-anchor="middle" y="5" fill="white" font-family="Arial" font-weight="700">25K</text>
                                    <animateTransform attributeName="transform" type="rotate"
                                                      from="180 300 320" to="540 300 320"
                                                      dur="12s" repeatCount="indefinite"/>
                                </g>
                            </g>

                            <!-- Icône smartphone moderne -->
                            <g class="smartphone" transform="translate(50, 300)">
                                <rect x="0" y="0" width="80" height="160" rx="15" fill="#1a1a1a"/>
                                <rect x="5" y="5" width="70" height="150" rx="10" fill="white"/>

                                <!-- Écran -->
                                <rect x="10" y="10" width="60" height="100" rx="5" fill="#0066cc" opacity="0.1"/>

                                <!-- Graphique sur écran -->
                                <path d="M20 40L35 30L50 35L70 25" stroke="#0066cc" stroke-width="2" fill="none">
                                    <animate attributeName="stroke-dashoffset" from="100" to="0" dur="2s" repeatCount="indefinite"/>
                                </path>

                                <!-- Notifications -->
                                <circle cx="65" cy="20" r="8" fill="#ff6b35">
                                    <animate attributeName="r" values="8;10;8" dur="1.5s" repeatCount="indefinite"/>
                                </circle>
                                <text x="65" y="24" text-anchor="middle" fill="white" font-family="Arial" font-size="10">3</text>

                                <!-- Boutons -->
                                <rect x="35" y="130" width="10" height="3" rx="1.5" fill="#1a1a1a"/>
                            </g>

                            <!-- Flèches de connexion animées -->
                            <g class="connection-arrows">
                                <path d="M200 150L250 120" stroke="#ff6b35" stroke-width="2" stroke-dasharray="5,5">
                                    <animate attributeName="stroke-dashoffset" from="10" to="0" dur="1s" repeatCount="indefinite"/>
                                </path>
                                <path d="M300 200L350 170" stroke="#ff6b35" stroke-width="2" stroke-dasharray="5,5">
                                    <animate attributeName="stroke-dashoffset" from="0" to="10" dur="1s" repeatCount="indefinite" begin="0.5s"/>
                                </path>
                            </g>

                            <!-- Texte animé -->
                            <text x="250" y="430" text-anchor="middle" fill="#0066cc"
                                  font-family="Arial" font-size="16" font-weight="600">
                                <tspan x="250" dy="0">Épargne Collective</tspan>
                                <tspan x="250" dy="20" fill="#00a86b">Croissance Assurée</tspan>
                                <animate attributeName="opacity" values="0.5;1;0.5" dur="3s" repeatCount="indefinite"/>
                            </text>
                        </svg>
                    </div>

                    <!-- Face arrière (survole pour voir) -->
                    <div class="illustration-back">
                        <div>
                            <div style="font-size: 2.5rem; margin-bottom: 20px;">
                                <i class="fas fa-chart-network"></i>
                            </div>
                            <h3 style="margin-bottom: 15px; font-size: 1.5rem;">Tontine Intelligente</h3>
                            <p style="opacity: 0.9; line-height: 1.6;">
                                Gestion automatisée • Transparence totale • Croissance collective
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Stats flottantes -->
                <div class="floating-stats">
                    <div class="stat-bubble" style="animation-delay: 0s;">
                        <div class="stat-value">+45%</div>
                        <div class="stat-label">Croissance moyenne</div>
                    </div>
                    <div class="stat-bubble" style="animation-delay: 0.5s;">
                        <div class="stat-value">99.9%</div>
                        <div class="stat-label">Satisfaction clients</div>
                    </div>
                    <div class="stat-bubble" style="animation-delay: 1s;">
                        <div class="stat-value">24/7</div>
                        <div class="stat-label">Disponibilité</div>
                    </div>
                </div>
            </div>
        </div>


    </div>
</section>

<!-- Stats Section -->
<section class="stats">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <span class="stat-number" data-count="1500">0</span>
                <span class="stat-label">Membres Actifs</span>
            </div>
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <span class="stat-number" data-count="250">0</span>
                <span class="stat-label">Tontines Gérées</span>
            </div>
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-coins"></i>
                </div>
                <span class="stat-number" data-count="75">0</span>
                <span class="stat-label">Millions FCFA</span>
            </div>
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <span class="stat-number" data-count="99">0</span>
                <span class="stat-label">% Satisfaction</span>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section features" id="fonctionnalites">
    <div class="container">
        <h2 class="section-title">Fonctionnalités Complètes</h2>
        <p class="section-subtitle">
            Tout ce dont vous avez besoin pour gérer efficacement votre tontine
        </p>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="feature-title">Sécurité Maximale</h3>
                <p class="feature-description">
                    Chiffrement bancaire, authentification à deux facteurs et
                    sauvegarde automatique des données.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="feature-title">Transparence Totale</h3>
                <p class="feature-description">
                    Tableaux de bord en temps réel, rapports détaillés et
                    historique complet des transactions.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3 class="feature-title">Mobile First</h3>
                <p class="feature-description">
                    Application optimisée pour smartphone avec notifications
                    push et interface intuitive.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-file-contract"></i>
                </div>
                <h3 class="feature-title">Gestion des Prêts</h3>
                <p class="feature-description">
                    Système de prêts avec garanties, échéanciers automatiques
                    et calcul des intérêts.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3 class="feature-title">Notifications Intelligentes</h3>
                <p class="feature-description">
                    Annonce sur les different changement, activite etc... de la réunions .
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3 class="feature-title">Support Local</h3>
                <p class="feature-description">
                    Assistance en français et langues locales par téléphone,
                    email et présentiel.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="section how-it-works">
    <div class="container">
        <h2 class="section-title">Comment ça marche ?</h2>
        <p class="section-subtitle">
            Simple, rapide et efficace en 4 étapes
        </p>

        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Créez votre compte</h3>
                <p>Inscrivez-vous gratuitement en 2 minutes</p>
            </div>

            <div class="step">
                <div class="step-number">2</div>
                <h3>Configurez votre tontine</h3>
                <p>Définissez les règles, membres et montants</p>
            </div>

            <div class="step">
                <div class="step-number">3</div>
                <h3>Invitez vos membres</h3>
                <p>Partagez le lien d'invitation à votre groupe</p>
            </div>

            <div class="step">
                <div class="step-number">4</div>
                <h3>Commencez à gérer</h3>
                <p>Suivez et gérez votre tontine en temps réel</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="section testimonials" id="temoignages">
    <div class="container">
        <h2 class="section-title">Ils nous font confiance</h2>
        <p class="section-subtitle">
            Découvrez ce que disent nos utilisateurs satisfaits
        </p>

        <div class="testimonial-slider">
            <div class="testimonial-card">
                <p class="testimonial-text">
                    TontinePro a révolutionné la gestion de notre association.
                    La transparence et la facilité d'utilisation ont renforcé
                    la confiance entre tous les membres.
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">MA</div>
                    <div>
                        <h4>Marie Abena</h4>
                        <p>Présidente, Tontine "Les Lionnes" - Douala</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing -->
<!-- Dès que ce sera possible implementer cette solution
<section class="section pricing" id="tarifs">
    <div class="container">
        <h2 class="section-title">Tarifs Transparents</h2>
        <p class="section-subtitle">
            Choisissez l'offre qui correspond à vos besoins
        </p>

        <div class="pricing-grid">
            <div class="pricing-card">
                <div class="pricing-header">
                    <h3 class="pricing-title">Basique</h3>
                    <div class="pricing-price">Gratuit</div>
                    <div class="pricing-period">Pour les petites tontines</div>
                </div>

                <ul class="pricing-features">
                    <li><i class="fas fa-check"></i> Jusqu'à 10 membres</li>
                    <li><i class="fas fa-check"></i> Gestion des cotisations</li>
                    <li><i class="fas fa-check"></i> Tableau de bord basique</li>
                    <li><i class="fas fa-check"></i> Support par email</li>
                    <li><i class="fas fa-times"></i> Gestion des prêts</li>
                    <li><i class="fas fa-times"></i> Rapports avancés</li>
                </ul>

                <a href="/register" class="btn btn-secondary">Commencer</a>
            </div>

            <div class="pricing-card popular">
                <div class="pricing-header">
                    <h3 class="pricing-title">Professionnel</h3>
                    <div class="pricing-price">5.000 FCFA</div>
                    <div class="pricing-period">par mois</div>
                </div>

                <ul class="pricing-features">
                    <li><i class="fas fa-check"></i> Jusqu'à 50 membres</li>
                    <li><i class="fas fa-check"></i> Gestion complète des prêts</li>
                    <li><i class="fas fa-check"></i> Tableau de bord avancé</li>
                    <li><i class="fas fa-check"></i> Rapports détaillés</li>
                    <li><i class="fas fa-check"></i> Notifications SMS</li>
                    <li><i class="fas fa-check"></i> Support prioritaire</li>
                </ul>

                <a href="/register" class="btn btn-primary">Choisir cette offre</a>
            </div>

            <div class="pricing-card">
                <div class="pricing-header">
                    <h3 class="pricing-title">Entreprise</h3>
                    <div class="pricing-price">15.000 FCFA</div>
                    <div class="pricing-period">par mois</div>
                </div>

                <ul class="pricing-features">
                    <li><i class="fas fa-check"></i> Membres illimités</li>
                    <li><i class="fas fa-check"></i> Multiples tontines</li>
                    <li><i class="fas fa-check"></i> API d'intégration</li>
                    <li><i class="fas fa-check"></i> Formation personnalisée</li>
                    <li><i class="fas fa-check"></i> Support 24/7</li>
                    <li><i class="fas fa-check"></i> Contrats légaux</li>
                </ul>

                <a href="/contact" class="btn btn-secondary">Nous contacter</a>
            </div>
        </div>
    </div>
</section>     !-->
<!-- Section Comparaison -->
<section class="section comparaison" id="comparaison">
    <div class="container">
        <h2 class="section-title">TontinePro vs Méthode Traditionnelle</h2>
        <p class="section-subtitle">
            Comparez les avantages de la digitalisation
        </p>

        <div class="comparison-table">
            <table>
                <thead>
                <tr>
                    <th>Aspect</th>
                    <th>Tontine Traditionnelle</th>
                    <th>Avec TontinePro</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><strong>Transparence</strong></td>
                    <td>✗ Limitée, dépend du trésorier</td>
                    <td>✓ Totale, accessible 24/7</td>
                </tr>
                <tr>
                    <td><strong>Sécurité</strong></td>
                    <td>✗ Risque de perte/vol</td>
                    <td>✓ Chiffrement bancaire</td>
                </tr>
                <tr>
                    <td><strong>Gain de Temps</strong></td>
                    <td>✗ Calculs manuels fastidieux</td>
                    <td>✓ Automatisation complète</td>
                </tr>
                <tr>
                    <td><strong>Accessibilité</strong></td>
                    <td>✗ Présence physique requise</td>
                    <td>✓ Depuis smartphone partout</td>
                </tr>
                <tr>
                    <td><strong>Historique</strong></td>
                    <td>✗ Carnets papier perdus</td>
                    <td>✓ Archivage numérique sécurisé</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="comparison-stats">
            <div class="stat-box">
                <div class="stat-number">90%</div>
                <div class="stat-label">de temps économisé</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">100%</div>
                <div class="stat-label">transparence garantie</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">0</div>
                <div class="stat-label">erreur de calcul</div>
            </div>
        </div>
    </div>
</section>



<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2 class="cta-title">Prêt à digitaliser votre tontine ?</h2>
        <p class="cta-subtitle">
            Rejoignez la communauté des tontines modernes. Simple, sécurisé et adapté
            aux besoins camerounais.
        </p>
        <a href="{{ route('register') }}" class="btn btn-lg">
            <i class="fas fa-user-plus"></i>
            Créer mon compte gratuit
        </a>
    </div>
</section>

<!-- Footer -->
<footer class="footer" id="contact">
    <div class="container">
        <div class="footer-content">
            <div class="footer-col">
                <div class="footer-logo">TontinePro</div>
                <p class="footer-description">
                    Plateforme leader de gestion de tontines au Cameroun.
                    Nous combinons tradition et technologie pour une épargne
                    collective moderne et sécurisée.
                </p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>

            <div class="footer-col">
                <h4>Navigation</h4>
                <ul class="footer-links">
                    <li><a href="#accueil">Accueil</a></li>
                    <li><a href="#fonctionnalites">Fonctionnalités</a></li>
                    <li><a href="#tarifs">Tarifs</a></li>
                    <li><a href="#temoignages">Témoignages</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Légal</h4>
                <ul class="footer-links">
                    <li><a href="#">Conditions d'utilisation</a></li>
                    <li><a href="#">Politique de confidentialité</a></li>
                    <li><a href="#">Mentions légales</a></li>
                    <li><a href="#">CGV</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Contact</h4>
                <ul class="footer-links">
                    <li><i class="fas fa-map-marker-alt"></i> Yaoundé, Cameroun</li>
                    <li><i class="fas fa-phone"></i> +237 6XX XX XX XX</li>
                    <li><i class="fas fa-envelope"></i> contact@tontinepro.cm</li>
                    <li><i class="fas fa-clock"></i> Lun-Ven: 8h-18h</li>
                </ul>
            </div>
        </div>

        <div class="copyright">
            &copy; 2024 TontinePro. Tous droits réservés.
            Conçu avec ❤️ pour le Cameroun 🇨🇲
        </div>
    </div>
</footer>

<!-- JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Header scroll effect
        const header = document.getElementById('header');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;

                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Animated counters
        function animateCounter(element, target, duration = 2000) {
            let start = 0;
            const increment = target / (duration / 16);
            const timer = setInterval(() => {
                start += increment;
                if (start >= target) {
                    element.textContent = target + '+';
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(start);
                }
            }, 16);
        }

        // Observe when stats section is in view
        const statsSection = document.querySelector('.stats');
        const statNumbers = document.querySelectorAll('.stat-number');
        let animated = false;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !animated) {
                    animated = true;
                    statNumbers.forEach(stat => {
                        const target = parseInt(stat.getAttribute('data-count'));
                        animateCounter(stat, target);
                    });
                }
            });
        }, { threshold: 0.5 });

        if (statsSection) {
            observer.observe(statsSection);
        }

        // Active nav link on scroll
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav a');

        window.addEventListener('scroll', function() {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (scrollY >= (sectionTop - 100)) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        });

        // Feature cards animation
        const featureCards = document.querySelectorAll('.feature-card');
        featureCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });

    // Ici c'est pour l'installation de l'appli
        let deferredPrompt;
        const installBtn = document.getElementById('installAppBtn');

        // 1. On écoute l'événement qui dit "L'appli est installable"
        window.addEventListener('beforeinstallprompt', (e) => {
        // Empêcher la barre automatique (qui ne s'affiche pas toujours)
        e.preventDefault();
        // Sauvegarder l'événement pour plus tard
        deferredPrompt = e;
        // Afficher notre bouton
        installBtn.classList.remove('d-none');
    });

        // 2. Quand on clique sur le bouton
        installBtn.addEventListener('click', async () => {
        if (deferredPrompt) {
        // Afficher la vraie demande d'installation
        deferredPrompt.prompt();
        // Attendre la réponse de l'utilisateur
        const { outcome } = await deferredPrompt.userChoice;
        console.log(`Réponse utilisateur : ${outcome}`);
        // On ne peut l'utiliser qu'une fois
        deferredPrompt = null;
        // Cacher le bouton
        installBtn.classList.add('d-none');
    }
    });

        // 3. Si l'appli est déjà installée, on cache le bouton
        window.addEventListener('appinstalled', () => {
        installBtn.classList.add('d-none');
        console.log('Application installée !');
    });
</script>

*/ Immage de banniere scrip js */

<!-- GSAP pour animations avancées -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser GSAP
        const heroImage = document.querySelector('.hero-image-pro');

        // Animation de la carte au hover
        const illustrationCard = document.querySelector('.illustration-card');

        illustrationCard.addEventListener('mouseenter', () => {
            gsap.to(illustrationCard, {
                rotationY: 180,
                duration: 0.8,
                ease: "power2.inOut"
            });
        });

        illustrationCard.addEventListener('mouseleave', () => {
            gsap.to(illustrationCard, {
                rotationY: 0,
                duration: 0.8,
                ease: "power2.inOut"
            });
        });

        // Animation du graphique
        const trendLine = document.getElementById('trendLine');
        if (trendLine) {
            gsap.fromTo(trendLine,
                { strokeDashoffset: 1000 },
                {
                    strokeDashoffset: 0,
                    duration: 3,
                    ease: "power2.out",
                    delay: 0.5
                }
            );
        }

        // Animation des pièces
        const coins = document.querySelectorAll('.coin');
        coins.forEach((coin, index) => {
            gsap.to(coin, {
                y: "random(-20, 0)",
                duration: "random(2, 4)",
                repeat: -1,
                yoyo: true,
                ease: "sine.inOut",
                delay: index * 0.3
            });
        });

        // Animation des membres
        const members = document.querySelectorAll('.member');
        members.forEach((member, index) => {
            gsap.to(member, {
                scale: 1.1,
                duration: 1.5,
                repeat: -1,
                yoyo: true,
                ease: "power1.inOut",
                delay: index * 0.5
            });
        });

        // Animation des points flottants
        const floatingDots = document.querySelectorAll('.floating-dot');
        floatingDots.forEach((dot, index) => {
            gsap.to(dot, {
                x: "random(-30, 30)",
                y: "random(-30, 30)",
                duration: "random(3, 6)",
                repeat: -1,
                yoyo: true,
                ease: "sine.inOut",
                delay: index * 0.5
            });
        });

        // Animation des stats flottantes
        const statBubbles = document.querySelectorAll('.stat-bubble');
        statBubbles.forEach((bubble, index) => {
            gsap.from(bubble, {
                x: -50,
                opacity: 0,
                duration: 1,
                delay: index * 0.3,
                ease: "back.out(1.7)"
            });

            // Effet pulse continu
            gsap.to(bubble, {
                scale: 1.05,
                duration: 1.5,
                repeat: -1,
                yoyo: true,
                ease: "sine.inOut",
                delay: index * 0.5
            });
        });

        // Effet parallax au scroll
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;

            gsap.to(heroImage, {
                y: rate * 0.5,
                rotationZ: rate * 0.01,
                duration: 0.5,
                ease: "power1.out"
            });
        });

        // Animation d'entrée
        gsap.from(heroImage, {
            x: 100,
            opacity: 0,
            duration: 1.5,
            delay: 0.3,
            ease: "power3.out"
        });

        // Effet de brillance sur les pièces
        setInterval(() => {
            coins.forEach(coin => {
                gsap.to(coin, {
                    scale: 1.2,
                    duration: 0.3,
                    yoyo: true,
                    repeat: 1,
                    ease: "power2.inOut"
                });
            });
        }, 3000);
    });
</script>
</body>
</html>
