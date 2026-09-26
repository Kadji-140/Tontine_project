<x-app-layout>

    <!-- Définition du titre de la page pour le layout principal -->
    <div class="tab_de_bord">
        @section('page-title', 'Tableau de Bord')
        @section('page-subtitle', 'Vue d\'ensemble de vos activités')
    </div>


    <!-- STYLES SPÉCIFIQUES AU DASHBOARD -->
    @push('styles')
        <style>
            /* Bannière de bienvenue "Vivid Blue" */
            .welcome-banner {
                background: linear-gradient(120deg, #2563eb 0%, #06b6d4 100%); /* Bleu vif vers Cyan */
                color: white;
                border-radius: 16px;
                padding: 30px;
                position: relative;
                overflow: hidden;
                box-shadow: 0 10px 25px rgba(37, 99, 235, 0.25);
                margin-bottom: 30px;
            }

            /* Effet de cercles décoratifs sur la bannière */
            .welcome-banner::before {
                content: '';
                position: absolute;
                top: -50%;
                right: -10%;
                width: 300px;
                height: 300px;
                background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
                border-radius: 50%;
            }

            .welcome-banner::after {
                content: '';
                position: absolute;
                bottom: -30%;
                left: 5%;
                width: 200px;
                height: 200px;
                background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);
                border-radius: 50%;
            }

            /* Cartes de statistiques améliorées */
            .stat-card-modern {
                background: var(--card-bg);
                border-radius: 16px;
                padding: 24px;
                border: 1px solid var(--border-color);
                box-shadow: var(--shadow-sm);
                transition: all 0.3s ease;
                height: 100%;
                position: relative;
                overflow: hidden;
            }

            .stat-card-modern:hover {
                transform: translateY(-5px);
                box-shadow: var(--shadow);
                border-color: #2563eb;
            }

            .stat-icon-wrapper {
                width: 54px;
                height: 54px;
                border-radius: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                margin-bottom: 16px;
            }

            /* Graphiques Container */
            .chart-card {
                background: var(--card-bg);
                border-radius: 16px;
                padding: 24px;
                box-shadow: var(--shadow-sm);
                border: 1px solid var(--border-color);
                height: 100%;
            }
        </style>
    @endpush

    <!-- === CONTENU DU DASHBOARD === -->

    <!-- 1. Bannière de Bienvenue (Le Header "Super Beau") -->
    <div class="welcome-banner d-flex align-items-center justify-content-between">
        <div style="position: relative; z-index: 2;" class="">
            <h2 class="fw-bold mb-2">Bonjour, {{ Auth::user()->name }} ! 👋</h2>
            <p class="mb-0 opacity-90" style="font-size: 1.05rem;">
                @if(isset($cycleActif) && $cycleActif)
                    Le cycle <strong>{{ $cycleActif->nom }}</strong> est actuellement en cours.
                @else
                    Bienvenue sur votre espace de gestion Tontine Pro.
                @endif
            </p>
        </div>
        <div class="d-none d-md-block" style="position: relative; z-index: 2;">
            <a href="{{ route('profile.edit') }}" class="btn btn-light text-primary fw-bold px-4 py-2" style="border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <i class="fas fa-user-circle me-2"></i> Mon Compte
            </a>
        </div>
    </div>

    <!-- 2. Cartes de Statistiques (Logique Admin vs Membre conservée) -->
    <div class="row g-4 mb-5">
        @if(Auth::user()->role === 'admin' || Auth::user()->role === 'tresorier')
            <!-- === STATS ADMIN / TRÉSORIER === -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card-modern">
                    <div class="stat-icon-wrapper bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <h6 class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Solde Caisse</h6>
                    <h3 class="fw-bold mb-1">{{ number_format($soldeCaisse ?? 0, 0, ',', ' ') }} <small class="fs-6">FCFA</small></h3>
                    <div class="d-flex align-items-center mt-2">
                        <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill">
                            <i class="fas fa-check-circle me-1"></i> Disponible
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card-modern">
                    <div class="stat-icon-wrapper bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <h6 class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Crédits Dehors</h6>
                    <h3 class="fw-bold mb-1">{{ number_format($argentDehors ?? 0, 0, ',', ' ') }} <small class="fs-6">FCFA</small></h3>
                    <div class="d-flex align-items-center mt-2">
                        <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 rounded-pill">
                            <i class="fas fa-clock me-1"></i> En attente
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card-modern">
                    <div class="stat-icon-wrapper bg-info bg-opacity-10 text-info">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h6 class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Prochaine Séance</h6>
                    @if(isset($prochaineSeance) && $prochaineSeance)
                        <h3 class="fw-bold mb-1">{{ $prochaineSeance->date_seance->format('d/m') }}</h3>
                        <div class="text-muted small mt-1">
                            {{ $prochaineSeance->statut == 'ouverte' ? 'Actuellement ouverte' : 'Prévue bientôt' }}
                        </div>
                    @else
                        <h3 class="fw-bold mb-1">--/--</h3>
                        <div class="text-muted small mt-1">Non planifiée</div>
                    @endif
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card-modern">
                    <div class="stat-icon-wrapper bg-success bg-opacity-10 text-success">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h6 class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Activités (7j)</h6>
                    <h3 class="fw-bold mb-1">{{ isset($activites) ? $activites->count() : 0 }}</h3>
                    <div class="text-muted small mt-1">
                        Transactions récentes
                    </div>
                </div>
            </div>

        @else
            <!-- === STATS MEMBRE === -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card-modern">
                    <div class="stat-icon-wrapper bg-success bg-opacity-10 text-success">
                        <i class="fas fa-piggy-bank"></i>
                    </div>
                    <h6 class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Mon Épargne</h6>
                    <h3 class="fw-bold mb-1">{{ number_format($monEpargne ?? 0, 0, ',', ' ') }} <small class="fs-6">FCFA</small></h3>
                    <span class="text-muted small">Total cotisé</span>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card-modern">
                    <div class="stat-icon-wrapper bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h6 class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Mes Dettes</h6>
                    <h3 class="fw-bold mb-1">{{ number_format($mesDettes ?? 0, 0, ',', ' ') }} <small class="fs-6">FCFA</small></h3>
                    <span class="text-muted small">Reste à payer</span>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card-modern">
                    <div class="stat-icon-wrapper bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h6 class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Sanctions</h6>
                    <h3 class="fw-bold mb-1">{{ number_format($mesSanctions ?? 0, 0, ',', ' ') }} <small class="fs-6">FCFA</small></h3>
                    <span class="text-muted small">Amendes impayées</span>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card-modern">
                    <div class="stat-icon-wrapper bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <h6 class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Proch. Séance</h6>
                    @if(isset($prochaineSeance) && $prochaineSeance)
                        <h3 class="fw-bold mb-1">{{ $prochaineSeance->date_seance->format('d/m') }}</h3>
                        <span class="badge bg-primary">Bientôt</span>
                    @else
                        <h3 class="fw-bold mb-1">--/--</h3>
                        <span class="badge bg-secondary">Inconnue</span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- 3. Section Graphiques (Visible pour tous, données dynamiques) -->
    <div class="row g-4 mb-5">
        <!-- Graphique Évolution -->
        <div class="col-lg-8">
            <div class="chart-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Évolution de la Caisse</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-light text-dark border dropdown-toggle" type="button" data-bs-toggle="dropdown" id="chartFilterLabel">
                            Ce mois
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item chart-filter" href="#" data-filter="today">Aujourd'hui / 7j</a></li>
                            <li><a class="dropdown-item chart-filter" href="#" data-filter="month">Ce mois</a></li>
                            <li><a class="dropdown-item chart-filter" href="#" data-filter="year">Cette année</a></li>
                            <!-- <li><a class="dropdown-item chart-filter" href="#" data-filter="2months">2 derniers mois</a></li> -->
                        </ul>
                    </div>
                </div>
                <div style="height: 300px; position: relative;">
                    <canvas id="evolutionChart"></canvas>
                    <!-- Fallback si vide -->
                    <div id="noDataEvolution" class="text-center text-muted d-none" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                        <i class="fas fa-chart-area fa-2x mb-2 opacity-50"></i>
                        <p>Pas assez de données</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphique Répartition -->
        <div class="col-lg-4">
            <div class="chart-card">
                <h5 class="fw-bold mb-4">Répartition des Fonds</h5>
                <div style="height: 220px; position: relative; margin-bottom: 20px;">
                    <canvas id="repartitionChart"></canvas>
                    <!-- Fallback si vide -->
                    <div id="noDataRepartition" class="text-center text-muted d-none" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                        <i class="fas fa-chart-pie fa-2x mb-2 opacity-50"></i>
                        <p>Aucune donnée</p>
                    </div>
                </div>
                <!-- Légende personnalisée -->
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded hover-bg-light">
                        <div class="d-flex align-items-center">
                            <span style="width: 10px; height: 10px; background: #2563eb; border-radius: 50%; margin-right: 10px;"></span>
                            <span class="small fw-medium">Tontine (Épargne)</span>
                        </div>
                        <span class="fw-bold text-main">{{ number_format($totalTontine ?? 0, 0, ',', ' ') }} F</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-2 rounded hover-bg-light">
                        <div class="d-flex align-items-center">
                            <span style="width: 10px; height: 10px; background: #f59e0b; border-radius: 50%; margin-right: 10px;"></span>
                            <span class="small fw-medium">Secours (Caisse)</span>
                        </div>
                        <span class="fw-bold text-main">{{ number_format($totalSecours ?? 0, 0, ',', ' ') }} F</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Activités Récentes ou Actions Rapides -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px; background: var(--card-bg);">
                <div class="card-header border-bottom-0 py-3 px-4 d-flex justify-content-between align-items-center" style="border-radius: 16px 16px 0 0; background: transparent;">
                    <h5 class="fw-bold mb-0">
                        @if(Auth::user()->role === 'admin' || Auth::user()->role === 'tresorier')
                            Dernières Transactions
                        @else
                            Mes Actions Rapides
                        @endif
                    </h5>
                    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'tresorier')
                        <a href="{{ route('seances.index') }}" class="btn btn-sm btn-primary px-3 rounded-pill">Voir tout</a>
                    @endif
                </div>

                <div class="card-body p-0">
                    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'tresorier')
                        <!-- TABLEAU ADMIN -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0 text-muted small fw-bold text-uppercase">Membre</th>
                                    <th class="py-3 border-0 text-muted small fw-bold text-uppercase">Type</th>
                                    <th class="py-3 border-0 text-muted small fw-bold text-uppercase">Montant</th>
                                    <th class="py-3 border-0 text-muted small fw-bold text-uppercase">Date</th>
                                    <th class="pe-4 py-3 border-0 text-muted small fw-bold text-uppercase text-end">Statut</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($activites ?? [] as $activite)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <x-user-avatar :user="$activite->user" size="35" class="me-2" />
                                                <div>
                                                    <h6 class="mb-0 small fw-bold text-main">{{ $activite->user->name ?? 'Utilisateur' }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-main border">{{ ucfirst($activite->type) }}</span></td>
                                        <td class="fw-bold text-success">+{{ number_format($activite->montant, 0, ',', ' ') }} F</td>
                                        <td class="text-muted small">{{ $activite->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="pe-4 text-end"><i class="fas fa-check-circle text-success"></i></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-3 opacity-25"></i>
                                            <p>Aucune activité récente à afficher</p>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- ACTIONS MEMBRE -->
                        <div class="p-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <a href="{{ route('prets.create') }}" class="btn btn-outline-primary w-100 p-3 h-100 d-flex flex-column align-items-center justify-content-center border-2 rounded-3 hover-scale">
                                        <i class="fas fa-hand-holding-usd fa-2x mb-2"></i>
                                        <span class="fw-bold">Demander un prêt</span>
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="{{ route('prets.index') }}" class="btn btn-outline-dark w-100 p-3 h-100 d-flex flex-column align-items-center justify-content-center border-2 rounded-3 hover-scale">
                                        <i class="fas fa-list-ul fa-2x mb-2"></i>
                                        <span class="fw-bold">Mes Historiques</span>
                                    </a>
                                </div>
                                <!-- Autres boutons membres... -->
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS GRAPHIQUES -->
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Configuration commune
                Chart.defaults.font.family = "'Inter', sans-serif";
                Chart.defaults.color = '#64748b';

                // 1. Graphique Évolution (DYNAMIQUE)
                const ctxEvol = document.getElementById('evolutionChart');
                let evolutionChart = null;

                // Fonction pour charger les données
                function loadChartData(filter) {
                    fetch(`{{ route('dashboard.chart-data') }}?filter=${filter}`)
                        .then(response => response.json())
                        .then(data => {
                            if(evolutionChart) {
                                evolutionChart.destroy();
                            }

                            // Masquer le fallback
                            const fallback = document.getElementById('noDataEvolution');
                            if(fallback) fallback.classList.add('d-none');

                            if(data.labels.length > 0) {
                                evolutionChart = new Chart(ctxEvol, {
                                    type: 'line',
                                    data: {
                                        labels: data.labels,
                                        datasets: [
                                            {
                                                label: 'Encaisse (Entrées)',
                                                data: data.encaisse,
                                                borderColor: '#10b981', // Vert
                                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                                borderWidth: 2,
                                                tension: 0.3,
                                                fill: true
                                            },
                                            {
                                                label: 'Dépenses (Sorties)',
                                                data: data.depenses,
                                                borderColor: '#ef4444', // Rouge
                                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                                borderWidth: 2,
                                                tension: 0.3,
                                                fill: true
                                            },
                                            {
                                                label: 'Solde Banque',
                                                data: data.banque,
                                                borderColor: '#3b82f6', // Bleu
                                                borderDash: [5, 5],
                                                borderWidth: 2,
                                                tension: 0.3,
                                                fill: false
                                            }
                                        ]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        interaction: {
                                            mode: 'index',
                                            intersect: false,
                                        },
                                        plugins: {
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context) {
                                                        return context.dataset.label + ': ' + context.parsed.y.toLocaleString() + ' FCFA';
                                                    }
                                                }
                                            }
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                grid: { borderDash: [2, 4], color: '#f1f5f9' },
                                                ticks: { callback: value => value >= 1000 ? (value/1000) + 'k' : value }
                                            }
                                        }
                                    }
                                });
                            } else {
                                if(fallback) fallback.classList.remove('d-none');
                            }
                        })
                        .catch(error => console.error('Erreur chargement graph:', error));
                }

                // Initialiser avec 'month' par défaut
                if(ctxEvol) {
                    loadChartData('month');
                }

                // Écouter les boutons de filtre (à ajouter dans le HTML)
                document.querySelectorAll('.chart-filter').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const filter = this.dataset.filter;
                        // Mettre à jour le texte du bouton dropdown
                        document.getElementById('chartFilterLabel').innerText = this.innerText;
                        loadChartData(filter);
                    });
                });

                // 2. Graphique Répartition
                const ctxRep = document.getElementById('repartitionChart');
                if(ctxRep) {
                    const tontine = {{ $totalTontine ?? 0 }};
                    const secours = {{ $totalSecours ?? 0 }};

                    if(tontine > 0 || secours > 0) {
                        new Chart(ctxRep, {
                            type: 'doughnut',
                            data: {
                                labels: ['Tontine', 'Secours'],
                                datasets: [{
                                    data: [tontine, secours],
                                    backgroundColor: ['#2563eb', '#f59e0b'],
                                    borderWidth: 0,
                                    hoverOffset: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '75%',
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                let val = context.raw;
                                                return val.toLocaleString() + ' FCFA';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        document.getElementById('noDataRepartition').classList.remove('d-none');
                    }
                }
            });
        </script>
    @endpush
</x-app-layout>
