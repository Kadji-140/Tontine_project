<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
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
        .register-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: linear-gradient(145deg, var(--gray-100) 0%, #ffffff 100%);
        }

        .register-card {
            display: flex;
            max-width: 1100px;
            width: 100%;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .register-card:hover {
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
            max-width: 400px;
            margin: 0 auto;
        }

        /* Section droite - Formulaire */
        .form-section {
            flex: 0.9;
            padding: 48px 40px;
            background: white;
        }

        .form-container {
            max-width: 380px;
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
            margin-bottom: 20px;
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

        /* Indicateur de force du mot de passe - discret */
        .password-strength {
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .strength-bars {
            display: flex;
            gap: 4px;
            flex: 1;
        }

        .strength-bar {
            height: 4px;
            flex: 1;
            background: var(--gray-200);
            border-radius: 2px;
            transition: var(--transition);
        }

        .strength-bar.active {
            background: var(--primary);
            opacity: 0.7;
        }

        .strength-text {
            font-size: 0.75rem;
            color: var(--gray-600);
            min-width: 70px;
        }

        /* Bouton de toggle password */
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

        /* Checkbox des conditions */
        .terms-group {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 24px 0;
        }

        .terms-checkbox {
            width: 18px;
            height: 18px;
            border: 1.5px solid var(--gray-200);
            border-radius: 5px;
            cursor: pointer;
            transition: var(--transition);
            accent-color: var(--primary);
        }

        .terms-label {
            font-size: 0.85rem;
            color: var(--gray-600);
            line-height: 1.5;
        }

        .terms-label a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .terms-label a:hover {
            text-decoration: underline;
        }

        /* Bouton d'inscription - élégant */
        .btn-register {
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

        .btn-register:hover {
            background: #0f2937;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -8px rgba(0, 0, 0, 0.1);
        }

        .btn-register i {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Lien de connexion */
        .login-link {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--gray-200);
            font-size: 0.9rem;
            color: var(--gray-600);
        }

        .login-link a {
            color: var(--gray-900);
            text-decoration: none;
            font-weight: 600;
            margin-left: 6px;
        }

        .login-link a:hover {
            color: var(--primary);
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
            .register-card {
                flex-direction: column;
                max-width: 500px;
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
                max-width: 300px;
            }
        }

        @media (max-width: 480px) {
            .register-wrapper {
                padding: 20px 15px;
            }

            .form-section {
                padding: 30px 25px;
            }

            .security-badges {
                flex-wrap: wrap;
                gap: 12px;
            }
        }

        /* Animation douce pour l'illustration */
        @keyframes subtleFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .illustration-container {
            animation: subtleFloat 6s ease-in-out infinite;
        }

        /* Effet de glow subtil sur le SVG */
        .illustration-container svg {
            filter: drop-shadow(0 8px 16px rgba(0, 102, 204, 0.04));
        }
    </style>

    <div class="register-wrapper">
        <div class="register-card">
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

                                <!-- Graphique de croissance - Ligne principale -->
                                <g class="chart-animation">
                                    <path id="trendLine"
                                          d="M80 300 L120 260 L180 240 L240 200 L300 180 L360 160 L420 150"
                                          stroke="url(#lineGradient)"
                                          stroke-width="3"
                                          fill="none"
                                          stroke-linecap="round"
                                          stroke-dasharray="600"
                                          stroke-dashoffset="600">
                                        <animate attributeName="stroke-dashoffset"
                                                 from="600"
                                                 to="0"
                                                 dur="2.5s"
                                                 fill="freeze"
                                                 repeatCount="indefinite"/>
                                    </path>

                                    <!-- Points de données -->
                                    <circle cx="80" cy="300" r="5" fill="#0066cc">
                                        <animate attributeName="r" values="5;7;5" dur="3s" repeatCount="indefinite"/>
                                    </circle>
                                    <circle cx="180" cy="240" r="5" fill="#00a86b">
                                        <animate attributeName="r" values="5;7;5" dur="3s" begin="0.5s" repeatCount="indefinite"/>
                                    </circle>
                                    <circle cx="300" cy="180" r="5" fill="#0066cc">
                                        <animate attributeName="r" values="5;7;5" dur="3s" begin="1s" repeatCount="indefinite"/>
                                    </circle>
                                    <circle cx="420" cy="150" r="5" fill="#00a86b">
                                        <animate attributeName="r" values="5;7;5" dur="3s" begin="1.5s" repeatCount="indefinite"/>
                                    </circle>
                                </g>

                                <!-- Groupe communautaire - Cercle de confiance -->
                                <g transform="translate(150, 150)">
                                    <!-- Cercle de connexion subtil -->
                                    <circle cx="150" cy="100" r="55"
                                            stroke="#0066cc"
                                            stroke-width="1.5"
                                            stroke-dasharray="8,6"
                                            fill="none"
                                            opacity="0.25">
                                        <animateTransform attributeName="transform"
                                                          type="rotate"
                                                          from="0 150 100"
                                                          to="360 150 100"
                                                          dur="30s"
                                                          repeatCount="indefinite"/>
                                    </circle>

                                    <!-- Membres - Disposition équilibrée -->
                                    <g transform="translate(150, 100)">
                                        <circle r="22" fill="#ffd6cc" opacity="0.9"/>
                                        <text text-anchor="middle" y="6" fill="#5a6874" font-family="Inter" font-size="12" font-weight="500">MA</text>
                                        <animateTransform attributeName="transform"
                                                          type="rotate"
                                                          from="0 150 100"
                                                          to="360 150 100"
                                                          dur="25s"
                                                          repeatCount="indefinite"/>
                                    </g>

                                    <g transform="translate(110, 60)">
                                        <circle r="20" fill="#ccd9ff" opacity="0.9"/>
                                        <text text-anchor="middle" y="5" fill="#5a6874" font-family="Inter" font-size="11" font-weight="500">JD</text>
                                        <animateTransform attributeName="transform"
                                                          type="rotate"
                                                          from="0 110 60"
                                                          to="360 110 60"
                                                          dur="28s"
                                                          repeatCount="indefinite"/>
                                    </g>

                                    <g transform="translate(190, 140)">
                                        <circle r="20" fill="#d4ffcc" opacity="0.9"/>
                                        <text text-anchor="middle" y="5" fill="#5a6874" font-family="Inter" font-size="11" font-weight="500">AK</text>
                                        <animateTransform attributeName="transform"
                                                          type="rotate"
                                                          from="0 190 140"
                                                          to="360 190 140"
                                                          dur="32s"
                                                          repeatCount="indefinite"/>
                                    </g>
                                </g>

                                <!-- Éléments décoratifs subtils -->
                                <g opacity="0.15">
                                    <circle cx="50" cy="50" r="25" fill="#0066cc"/>
                                    <circle cx="450" cy="380" r="35" fill="#00a86b"/>
                                    <circle cx="400" cy="80" r="15" fill="#ff6b35"/>
                                </g>

                                <!-- Texte discret -->
                                <text x="250" y="420" text-anchor="middle" fill="#1e293b" font-family="Inter" font-size="11" opacity="0.4">
                                    Épargne collective • Confiance • Croissance
                                </text>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Petit texte d'accueil subtil -->
                <div style="text-align: center; margin-top: 20px;">
                    <p style="color: var(--gray-600); font-size: 0.85rem; font-weight: 400; letter-spacing: 0.5px;">
                        <span style="color: var(--primary); font-weight: 600;">1,500+</span> membres nous font confiance
                    </p>
                </div>
            </div>

            <!-- SECTION DROITE - FORMULAIRE -->
            <div class="form-section">
                <div class="form-container">
                    <div class="form-header">
                        <h2>Créer un compte</h2>
                        <p>Rejoignez la communauté TontinePro</p>
                    </div>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <!-- Nom complet -->
                        <div class="form-group">
                            <label for="name" class="form-label">Nom complet</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user input-icon"></i>
                                <input id="name" type="text"
                                       class="form-input @error('name') is-invalid @enderror"
                                       name="name" value="{{ old('name') }}"
                                       placeholder="Jean Dupont"
                                       required autofocus>
                            </div>
                            @error('name')
                            <span style="color: #dc3545; font-size: 0.75rem; margin-top: 4px; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label for="email" class="form-label">Adresse email</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope input-icon"></i>
                                <input id="email" type="email"
                                       class="form-input @error('email') is-invalid @enderror"
                                       name="email" value="{{ old('email') }}"
                                       placeholder="contact@exemple.com"
                                       required>
                            </div>
                            @error('email')
                            <span style="color: #dc3545; font-size: 0.75rem; margin-top: 4px; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Téléphone -->
                        <div class="form-group">
                            <label for="phone" class="form-label">Téléphone</label>
                            <div class="input-wrapper">
                                <i class="fas fa-phone-alt input-icon"></i>
                                <input id="phone" type="text"
                                       class="form-input @error('phone') is-invalid @enderror"
                                       name="phone" value="{{ old('phone') }}"
                                       placeholder="6XX XXX XXX"
                                       required>
                            </div>
                            <span style="color: var(--gray-600); font-size: 0.7rem; margin-top: 4px; display: block;">
                                Format camerounais : 699123456
                            </span>
                            @error('phone')
                            <span style="color: #dc3545; font-size: 0.75rem; margin-top: 4px; display: block;">{{ $message }}</span>
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
                                       placeholder="8+ caractères"
                                       required>
                                <button type="button" class="toggle-password" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>

                            <!-- Indicateur de force discret -->
                            <div class="password-strength" id="passwordStrength" style="display: none;">
                                <div class="strength-bars">
                                    <div class="strength-bar" id="strength1"></div>
                                    <div class="strength-bar" id="strength2"></div>
                                    <div class="strength-bar" id="strength3"></div>
                                    <div class="strength-bar" id="strength4"></div>
                                </div>
                                <span class="strength-text" id="strengthText"></span>
                            </div>
                            @error('password')
                            <span style="color: #dc3545; font-size: 0.75rem; margin-top: 4px; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Confirmation -->
                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                            <div class="input-wrapper">
                                <i class="fas fa-check-circle input-icon"></i>
                                <input id="password_confirmation" type="password"
                                       class="form-input"
                                       name="password_confirmation"
                                       placeholder="Confirmez"
                                       required>
                            </div>
                        </div>

                        <!-- Conditions -->
                        <div class="terms-group">
                            <input type="checkbox" id="terms" name="terms" class="terms-checkbox" required>
                            <label for="terms" class="terms-label">
                                J'accepte les <a href="#">conditions</a> et la <a href="#">confidentialité</a>
                            </label>
                        </div>

                        <!-- Bouton -->
                        <button type="submit" class="btn-register">
                            <i class="fas fa-user-plus"></i>
                            Créer mon compte
                        </button>

                        <!-- Lien connexion -->
                        <div class="login-link">
                            Déjà membre ?
                            <a href="{{ route('login') }}">
                                Se connecter <i class="fas fa-arrow-right" style="font-size: 0.75rem;"></i>
                            </a>
                        </div>

                        <!-- Badges de sécurité -->
                        <div class="security-badges">
                            <span class="security-badge">
                                <i class="fas fa-shield-alt"></i> SSL
                            </span>
                            <span class="security-badge">
                                <i class="fas fa-lock"></i> Sécurisé
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

        // Formatage téléphone
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 8) value = value.slice(0, 8);
                if (value.length > 5) {
                    value = value.slice(0, 5) + ' ' + value.slice(5);
                }
                if (value.length > 2) {
                    value = value.slice(0, 2) + ' ' + value.slice(2);
                }
                e.target.value = value;
            });
        }

        // Force du mot de passe
        const passwordInput = document.getElementById('password');
        const strengthContainer = document.getElementById('passwordStrength');
        const strengthBars = [
            document.getElementById('strength1'),
            document.getElementById('strength2'),
            document.getElementById('strength3'),
            document.getElementById('strength4')
        ];
        const strengthText = document.getElementById('strengthText');

        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;

                if (password.length === 0) {
                    strengthContainer.style.display = 'none';
                    return;
                }

                strengthContainer.style.display = 'flex';

                // Reset bars
                strengthBars.forEach(bar => {
                    bar.classList.remove('active');
                    bar.style.background = '';
                });

                let strength = 0;

                // Longueur
                if (password.length >= 8) strength += 25;
                if (password.length >= 12) strength += 25;

                // Complexité
                if (/[a-z]/.test(password)) strength += 25;
                if (/[A-Z]/.test(password)) strength += 25;
                if (/[0-9]/.test(password)) strength += 25;
                if (/[^a-zA-Z0-9]/.test(password)) strength += 25;

                strength = Math.min(strength, 100);

                const barsActive = Math.ceil(strength / 25);
                for (let i = 0; i < barsActive; i++) {
                    strengthBars[i].classList.add('active');
                    if (strength < 30) {
                        strengthBars[i].style.background = '#dc3545';
                    } else if (strength < 50) {
                        strengthBars[i].style.background = '#ffc107';
                    } else if (strength < 75) {
                        strengthBars[i].style.background = '#17a2b8';
                    } else {
                        strengthBars[i].style.background = '#00a86b';
                    }
                }

                let text = '';
                if (strength < 30) text = 'Très faible';
                else if (strength < 50) text = 'Faible';
                else if (strength < 75) text = 'Moyen';
                else text = 'Fort';

                strengthText.textContent = text;
            });
        }
    </script>
</body>
</html>
