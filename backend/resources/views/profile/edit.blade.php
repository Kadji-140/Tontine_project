<x-app-layout>
    <x-slot name="header">
        <div class="container-fluid py-2">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Mon Profil') }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-light">
        <div class="container">
            <div class="row g-4">
                <!-- COLONNE GAUCHE : Résumé Profil & Navigation -->
                <div class="col-lg-4">
                    <!-- Carte Identité Visuelle -->
                    <div class="card shadow-sm border-0 mb-4 overflow-hidden">
                        <div class="card-body text-center p-0">
                            <div class="bg-primary pt-5 pb-5 text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
                                <div class="avatar-circle mx-auto mb-3 text-2xl font-bold bg-white text-primary d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; border-radius: 50%; border: 4px solid rgba(255,255,255,0.3);">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                </div>
                                <h5 class="mb-0 fw-bold">{{ Auth::user()->name }}</h5>
                                <p class="opacity-75 mb-0">{{ Auth::user()->email }}</p>
                                <span class="badge bg-white text-primary mt-2 rounded-pill px-3">{{ ucfirst(Auth::user()->role) }}</span>
                            </div>
                            <div class="p-4 bg-white">
                                <div class="d-flex justify-content-around text-center">
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">{{ Auth::user()->cotisations->count() }}</h6>
                                        <small class="text-muted">Cotisations</small>
                                    </div>
                                    <div class="border-start"></div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">{{ Auth::user()->prets->count() }}</h6>
                                        <small class="text-muted">Prêts</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Carte Navigation Rapide (Ancres) -->
                    <div class="list-group shadow-sm border-0 mb-4 sticky-top" style="top: 20px; z-index: 1;">
                        <a href="#infos-perso" class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center active-nav-item">
                            <i class="fas fa-user-circle me-3 text-primary"></i> Informations Personnelles
                        </a>
                        <a href="#infos-contact" class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center">
                            <i class="fas fa-address-card me-3 text-info"></i> Coordonnées & État Civil
                        </a>
                        <a href="#securite" class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center">
                            <i class="fas fa-shield-alt me-3 text-warning"></i> Sécurité (Mot de passe)
                        </a>
                        <a href="#danger-zone" class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center text-danger">
                            <i class="fas fa-trash-alt me-3"></i> Zone de Danger
                        </a>
                    </div>
                </div>

                <!-- COLONNE DROITE : Formulaires -->
                <div class="col-lg-8">
                    
                    <!-- 1. Informations Personnelles & Contact -->
                    <div id="infos-perso" class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <h5 class="mb-0 fw-bold text-dark border-start border-4 border-primary ps-3">Mettre à jour le profil</h5>
                        </div>
                        <div class="card-body p-4">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>

                    <!-- 2. Sécurité -->
                    <div id="securite" class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <h5 class="mb-0 fw-bold text-dark border-start border-4 border-warning ps-3">Modifier le mot de passe</h5>
                        </div>
                        <div class="card-body p-4">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>

                    <!-- 3. Danger Zone -->
                    <div id="danger-zone" class="card shadow-sm border-0 border-start border-danger border-4">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <h5 class="mb-0 fw-bold text-danger ps-2">Supprimer le compte</h5>
                        </div>
                        <div class="card-body p-4">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <style>
        .active-nav-item {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #1e3a8a;
            border-left: 4px solid #1e3a8a !important;
        }
        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }
        .btn-primary {
            background-color: #1e3a8a;
            border-color: #1e3a8a;
        }
        .btn-primary:hover {
            background-color: #1e40af;
            border-color: #1e40af;
        }
    </style>
</x-app-layout>
