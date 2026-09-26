# 🪙 TontinePro - Plateforme SaaS Collaborative de Gestion de Tontines

Bienvenue sur le projet **TontinePro**, une solution moderne, sécurisée et évolutive conçue pour digitaliser et simplifier la gestion collaborative des tontines, associations d'épargne rotative et cercles financiers.

---

## 🏛️ Architecture du Projet

Le projet a été restructuré en une architecture complètement découplée :

```
Tontine_project/
├── backend/                  # API REST Laravel 10 + PostgreSQL + Laravel Sanctum
│   ├── app/
│   │   ├── Http/Controllers/Api/  # Contrôleurs REST purs (JSON)
│   │   ├── Http/Middleware/       # Sécurité, Rôles, Multi-Tenant, Sanctum
│   │   ├── Models/                # Modèles Eloquent métier & Tenant
│   │   └── Traits/                # Trait AppartientAuTenant (Scoping Global)
│   ├── config/                    # CORS (credentials activés), Sanctum stateful
│   ├── database/migrations/       # Schéma PostgreSQL (avec isolation Multi-Tenant)
│   └── routes/api.php             # 64 routes REST documentées
│
└── frontend/                 # Single Page Application (SPA) React 18
    ├── src/
    │   ├── components/            # Composants UI modernes (Bouton, Carte, SelecteurTheme...)
    │   ├── contexts/              # Thème (Clair, Sombre, Système sans flash)
    │   ├── lib/                   # Client HTTP Axios + interceptor CSRF Sanctum
    │   ├── pages/                 # Écrans fonctionnels (Dashboard, Prêts, Séances, Membres...)
    │   ├── stores/                # Gestion d'état Zustand (Auth, Session, Rôles)
    │   ├── types/                 # Définitions TypeScript complètes en français
    │   ├── App.tsx                # Configuration du routage sécurisé
    │   └── main.tsx               # Point d'entrée React 18
    ├── vite.config.ts             # Configuration Vite + Plugin PWA
    └── tailwind.config.js         # Configuration Tailwind CSS + Dark Mode (classe 'dark')
```

---

## 🚀 Démarrage Rapide

### 1. Prérequis
- **PHP 8.2+** avec extensions `pdo_pgsql`, `pgsql`
- **PostgreSQL 14+** (ex: base `tontine_project`, port `5432`)
- **Node.js 18+** et **npm**
- **Composer 2+**

---

### 2. Démarrage du Backend (Laravel API)

```bash
# Se placer dans le dossier backend
cd backend

# Vérifier la configuration .env (DB_CONNECTION=pgsql, DB_DATABASE=tontine_project...)
# Lancer les migrations
php artisan migrate

# Lancer les tests unitaires et fonctionnels
php artisan test

# Démarrer le serveur API
php artisan serve
# L'API est accessible sur http://127.0.0.1:8000
```

---

### 3. Démarrage du Frontend (React + Vite SPA)

```bash
# Dans un second terminal, se placer dans le dossier frontend
cd frontend

# Installer les dépendances (si ce n'est pas déjà fait)
npm install

# Lancer le serveur de développement Vite
npm run dev
# L'application est accessible sur http://localhost:5173
```

---

## 💼 Comptes Démo de Test

Après exécution des seeders (`php artisan db:seed` dans `backend/`), vous pouvez tester les rôles suivants (Mot de passe universel : `password`) :

| Rôle | Email | Droits & Accès |
|---|---|---|
| **Administrateur** | `admin@tontine.test` | Gestion globale, approbation des membres, cycles, séances, dépenses |
| **Trésorier** | `tresorier@tontine.test` | Enregistrement cotisations, validation prêts, gestion de la caisse |
| **Membre Actif** | `membre@tontine.test` | Demande de prêt, consultation épargne, historique des gains |
| **Compte Inactif** | `nouveau@tontine.test` | Redirigé automatiquement vers le **Sas d'attente** (`/compte-inactif`) |

---

## ✨ Fonctionnalités Clés

1. **Règles Métier Intactes** :
   - Déduction automatique du **Mange-Mille** lors de la première cotisation d'un cycle.
   - Ajustement automatique de l'échéance des prêts à **3 mois max** ou à la date de fin du cycle.
   - Répartition dynamique des intérêts bancaires au prorata des épargnes à la clôture.
   - Sas d'attente (`/compte-inactif`) pour les comptes nouvellement inscrits non encore validés.

2. **Interface Moderne & Évolutive** :
   - **Mode Sombre / Clair / Système** ultra-fluide avec persistance `localStorage` et zéro flash au chargement.
   - Graphiques financiers interactifs (Recharts).
   - Support **Progressive Web App (PWA)** avec service worker préconfiguré (`vite-plugin-pwa`).
   - 100% typé TypeScript en français pour une maintenabilité optimale.

3. **Fondations SaaS Multi-Tenant** :
   - Table `tenants` avec slug unique (sous-domaines ou en-tête `X-Tenant-Slug`).
   - Trait Eloquent `AppartientAuTenant` avec injection transparente du Global Scope.
   - Middleware `IdentifierTenant` garantissant l'étanchéité des données entre organisations.
