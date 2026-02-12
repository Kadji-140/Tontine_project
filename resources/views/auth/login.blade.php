<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
</head>
<body>
    <!-- Styles élégants et subtils -->
    <style>
        :root {
            --primary: #0066cc;
            --primary-light: rgba(0, 102, 204, 0.08);
            --secondary: #00a86b;
            --accent: #ff6b35;
            --gray-50: #fafbfc;
            --gray-100: #f5f7fa;
            --gray-200: #e9edf2;
            --gray-600: #5a6874;
            --gray-900: #1e293b;
            --shadow-soft: 0 20px 35px -8px rgba(0, 0, 0, 0.05), 0 5px 12px -4px rgba(0, 0, 0, 0.02);
            --shadow-hover: 0 30px 50px -12px rgba(0, 102, 204, 0.12);
            --radius-md: 16px;
            --radius-lg: 24px;
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--gray-100);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        /* Conteneur principal - centré et compact */
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: linear-gradient(145deg, var(--gray-100) 0%, #ffffff 100%);
        }

        .login-card {
            display: flex;
            max-width: 1000px;
            width: 100%;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .login-card:hover {
            box-shadow: var(--shadow-hover);
        }

        /* Section gauche - Illustration */
        .illustration-section {
            flex: 1;
            background: linear-gradient(145deg, #ffffff 0%, var(--gray-50) 100%);
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            border-right: 1px solid var(--gray-200);
        }

        .illustration-container {
            width: 100%;
            max-width: 380px;
            margin: 0 auto;
        }

        /* Section droite - Formulaire */
        .form-section {
            flex: 0.9;
            padding: 48px 40px;
            background: white;
        }

        .form-container {
            max-width: 360px;
            margin: 0 auto;
        }

        /* En-tête du formulaire */
        .form-header {
            margin-bottom: 32px;
        }

        .form-header h2 {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }

        .form-header p {
            color: var(--gray-600);
            font-size: 0.95rem;
            line-height: 1.5;
        }

        /* Groupes de formulaire */
        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--gray-900);
            margin-bottom: 6px;
            letter-spacing: 0.3px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: var(--gray-600);
            font-size: 0.9rem;
            opacity: 0.7;
            transition: var(--transition);
        }

        .form-input {
            width: 100%;
            padding: 12px 16px 12px 42px;
            border: 1.5px solid var(--gray-200);
            border-radius: 12px;
            font-size: 0.95rem;
            transition: var(--transition);
            background: white;
            color: var(--gray-900);
        }

        .form-input:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(0, 102, 204, 0.04);
            outline: none;
        }

        .form-input::placeholder {
            color: var(--gray-600);
            opacity: 0.5;
            font-size: 0.9rem;
        }

        .form-input.is-invalid {
            border-color: #dc3545;
            background: rgba(220, 53, 69, 0.02);
        }

        /* Toggle password */
        .toggle-password {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: var(--gray-600);
            font-size: 0.9rem;
            opacity: 0.7;
            cursor: pointer;
            transition: var(--transition);
            padding: 4px;
        }

        .toggle-password:hover {
            opacity: 1;
            color: var(--primary);
        }

        /* Checkbox "Se souvenir de moi" */
        .remember-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-custom {
            width: 18px;
            height: 18px;
            border: 1.5px solid var(--gray-200);
            border-radius: 5px;
            cursor: pointer;
            transition: var(--transition);
            accent-color: var(--primary);
        }

        .checkbox-label {
            font-size: 0.85rem;
            color: var(--gray-600);
            cursor: pointer;
        }

        /* Lien mot de passe oublié */
        .forgot-link {
            font-size: 0.85rem;
            color: var(--gray-600);
            text-decoration: none;
            transition: var(--transition);
        }

        .forgot-link:hover {
            color: var(--primary);
            text-decoration: underline;
        }

        /* Bouton de connexion */
        .btn-login {
            width: 100%;
            padding: 14px 20px;
            background: var(--gray-900);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            transition: var(--transition);
            letter-spacing: 0.3px;
        }

        .btn-login:hover {
            background: #0f2937;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -8px rgba(0, 0, 0, 0.1);
        }

        .btn-login i {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Lien d'inscription */
        .register-link {
            text-align: center;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid var(--gray-200);
            font-size: 0.9rem;
            color: var(--gray-600);
        }

        .register-link a {
            color: var(--gray-900);
            text-decoration: none;
            font-weight: 600;
            margin-left: 6px;
        }

        .register-link a:hover {
            color: var(--primary);
        }

        /* Message de statut */
        .status-message {
            background: rgba(0, 168, 107, 0.08);
            border: 1px solid rgba(0, 168, 107, 0.2);
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #00a86b;
            font-size: 0.9rem;
        }

        .status-message i {
            font-size: 1rem;
        }

        /* Message d'erreur */
        .error-message {
            color: #dc3545;
            font-size: 0.75rem;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .error-message i {
            font-size: 0.7rem;
        }

        /* Badges de sécurité */
        .security-badges {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-200);
        }

        .security-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--gray-600);
            font-size: 0.75rem;
        }

        .security-badge i {
            color: var(--primary);
            opacity: 0.7;
            font-size: 0.8rem;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .login-card {
                flex-direction: column;
                max-width: 450px;
            }

            .illustration-section {
                border-right: none;
                border-bottom: 1px solid var(--gray-200);
                padding: 30px;
            }

            .form-section {
                padding: 40px 30px;
            }

            .illustration-container {
                max-width: 280px;
            }
        }

        @media (max-width: 480px) {
            .login-wrapper {
                padding: 20px 15px;
            }

            .form-section {
                padding: 30px 25px;
            }

            .remember-group {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }

        /* Animation douce */
        @keyframes subtleFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .illustration-container {
            animation: subtleFloat 6s ease-in-out infinite;
        }

        .illustration-container svg {
            filter: drop-shadow(0 8px 16px rgba(0, 102, 204, 0.04));
        }
    </style>

    <div class="login-wrapper">
        <div class="login-card">
            <!-- SECTION GAUCHE - ILLUSTRATION ANIMÉE -->
            <div class="illustration-section">
                <div class="illustration-container">
                    <!-- Illustration card avec animation -->
                    <div class="illustration-card">
                        <div class="illustration-front">
                            <svg width="100%" height="100%" viewBox="0 0 500 450" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <!-- Définitions -->
                                <defs>
                                    <linearGradient id="bgGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#ffffff"/>
                                        <stop offset="100%" stop-color="#f8fafc"/>
                                    </linearGradient>
                                    <linearGradient id="lineGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" stop-color="#0066cc"/>
                                        <stop offset="100%" stop-color="#00a86b"/>
                                    </linearGradient>
                                    <pattern id="gridPattern" width="40" height="40" patternUnits="userSpaceOnUse">
                                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#e6f2ff" stroke-width="1" opacity="0.2"/>
                                    </pattern>
                                </defs>

                                <!-- Fond subtil -->
                                <rect width="500" height="450" rx="20" fill="url(#bgGradient)"/>
                                <rect width="500" height="450" rx="20" fill="url(#gridPattern)" opacity="0.3"/>

                                <!-- Icône de connexion sécurisée -->
                                <g transform="translate(200, 150)">
                                    <!-- Cercle de protection -->
                                    <circle cx="100" cy="100" r="80"
                                            stroke="#0066cc"
                                            stroke-width="1.5"
                                            stroke-dasharray="10,6"
                                            fill="none"
                                            opacity="0.25">
                                        <animateTransform attributeName="transform"
                                                          type="rotate"
                                                          from="0 100 100"
                                                          to="360 100 100"
                                                          dur="40s"
                                                          repeatCount="indefinite"/>
                                    </circle>

                                    <!-- Bouclier -->
                                    <path d="M100 30L160 60V130C160 170 130 190 100 200C70 190 40 170 40 130V60L100 30Z"
                                          fill="#0066cc"
                                          opacity="0.9"/>

                                    <!-- Cadenas -->
                                    <circle cx="100" cy="110" r="20" fill="white"/>
                                    <rect x="90" y="95" width="20" height="25" rx="4" fill="#0066cc" opacity="0.8"/>
                                    <circle cx="100" cy="110" r="8" fill="white"/>
                                    <path d="M100 105L100 115" stroke="#0066cc" stroke-width="2"/>

                                    <!-- Points de connexion -->
                                    <circle cx="50" cy="130" r="6" fill="#00a86b" opacity="0.7">
                                        <animate attributeName="r" values="6;8;6" dur="3s" repeatCount="indefinite"/>
                                    </circle>
                                    <circle cx="150" cy="130" r="6" fill="#00a86b" opacity="0.7">
                                        <animate attributeName="r" values="6;8;6" dur="3s" begin="0.5s" repeatCount="indefinite"/>
                                    </circle>
                                </g>

                                <!-- Graphique de confiance -->
                                <g transform="translate(280, 250)">
                                    <path d="M0 80L40 60L80 40L120 30L160 20"
                                          stroke="#00a86b"
                                          stroke-width="2"
                                          fill="none"
                                          opacity="0.6"
                                          stroke-dasharray="200"
                                          stroke-dashoffset="200">
                                        <animate attributeName="stroke-dashoffset"
                                                 from="200"
                                                 to="0"
                                                 dur="3s"
                                                 fill="freeze"
                                                 repeatCount="indefinite"/>
                                    </path>
                                </g>

                                <!-- Éléments décoratifs -->
                                <g opacity="0.1">
                                    <circle cx="50" cy="380" r="30" fill="#0066cc"/>
                                    <circle cx="450" cy="80" r="25" fill="#00a86b"/>
                                </g>

                                <!-- Texte discret -->
                                <text x="250" y="420" text-anchor="middle" fill="#1e293b" font-family="Inter" font-size="11" opacity="0.3">
                                    Accès sécurisé • Espace membre
                                </text>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Petit message de confiance -->
                <div style="text-align: center; margin-top: 20px;">
                    <p style="color: var(--gray-600); font-size: 0.8rem; font-weight: 400; letter-spacing: 0.3px;">
                        <span style="color: var(--primary); font-weight: 600;">Connexion 24/7</span> • Support prioritaire
                    </p>
                </div>
            </div>

            <!-- SECTION DROITE - FORMULAIRE DE CONNEXION -->
            <div class="form-section">
                <div class="form-container">
                    <div class="form-header">
                        <h2>Content de vous revoir</h2>
                        <p>Connectez-vous à votre espace</p>
                    </div>

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="status-message">
                            <i class="fas fa-check-circle"></i>
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email -->
                        <div class="form-group">
                            <label for="email" class="form-label">Adresse email</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope input-icon"></i>
                                <input id="email" type="email"
                                       class="form-input @error('email') is-invalid @enderror"
                                       name="email" value="{{ old('email') }}"
                                       placeholder="contact@exemple.com"
                                       required autofocus>
                            </div>
                            @error('email')
                            <span class="error-message">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Mot de passe -->
                        <div class="form-group">
                            <label for="password" class="form-label">Mot de passe</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock input-icon"></i>
                                <input id="password" type="password"
                                       class="form-input @error('password') is-invalid @enderror"
                                       name="password"
                                       placeholder="••••••••"
                                       required autocomplete="current-password">
                                <button type="button" class="toggle-password" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            @error('password')
                            <span class="error-message">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="remember-group">
                            <div class="checkbox-wrapper">
                                <input id="remember_me" type="checkbox" class="checkbox-custom" name="remember">
                                <label for="remember_me" class="checkbox-label">Se souvenir de moi</label>
                            </div>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="forgot-link">
                                    Mot de passe oublié ?
                                </a>
                            @endif
                        </div>

                        <!-- Bouton de connexion -->
                        <button type="submit" class="btn-login">
                            <i class="fas fa-sign-in-alt"></i>
                            Se connecter
                        </button>

                        <!-- Lien d'inscription -->
                        <div class="register-link">
                            Pas encore de compte ?
                            <a href="{{ route('register') }}">
                                S'inscrire <i class="fas fa-arrow-right" style="font-size: 0.75rem;"></i>
                            </a>
                        </div>

                        <!-- Badges de sécurité -->
                        <div class="security-badges">
                            <span class="security-badge">
                                <i class="fas fa-shield-alt"></i> SSL 256-bit
                            </span>
                            <span class="security-badge">
                                <i class="fas fa-lock"></i> Chiffré
                            </span>
                            <span class="security-badge">
                                <i class="fas fa-clock"></i> 24/7
                            </span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        // Animation d'entrée
        document.addEventListener('DOMContentLoaded', function() {
            const loginCard = document.querySelector('.login-card');
            if (loginCard) {
                loginCard.style.opacity = '0';
                loginCard.style.transform = 'translateY(10px)';

                setTimeout(() => {
                    loginCard.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    loginCard.style.opacity = '1';
                    loginCard.style.transform = 'translateY(0)';
                }, 100);
            }
        });
    </script>
</body>
</html>
