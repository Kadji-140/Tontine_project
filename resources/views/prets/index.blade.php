<x-app-layout>
    <div class="container py-4">
    <style>
        .stat-card {
            border-radius: 10px;
            padding: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .badge-pill {
            font-size: 0.85em;
            padding: 0.5em 1em;
        }
        .filter-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid #0d6efd;
        }
        .action-buttons .btn {
            margin-right: 0.3rem;
            margin-bottom: 0.3rem;
        }
        .date-modified {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding-left: 10px;
        }
        .status-badge {
            font-weight: 500;
            padding: 0.35rem 0.75rem;
        }
    </style>
</head>
<body class="bg-light">
<div class="container-fluid px-0">
    <!-- Navbar -->


    <div class="container py-4">
        <!-- Header avec titre et bouton -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
            <div>
                <h1 class="h2 fw-bold text-primary">
                    <i class="bi bi-cash-coin me-2"></i>Gestion des Prêts
                </h1>
                <p class="text-muted mb-0">Consultez et gérez les demandes de prêts</p>
            </div>
            <a href="{{ route('prets.create') }}" class="btn btn-primary mt-3 mt-md-0">
                <i class="bi bi-plus-circle me-2"></i>Nouveau Prêt
            </a>
        </div>

        <!-- Messages d'alerte -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Section Filtres -->
        <div class="filter-section">
            <h5 class="fw-bold text-primary mb-3">
                <i class="bi bi-funnel me-2"></i>Filtres de recherche
            </h5>
            <form method="GET" action="{{ route('prets.index') }}" class="row g-3">
                <!-- Filtre Statut -->
                <div class="col-md-6 col-lg-3">
                    <label class="form-label fw-bold">Statut</label>
                    <select name="statut" class="form-select">
                        <option value="tous" {{ request('statut') == 'tous' || !request('statut') ? 'selected' : '' }}>Tous les statuts</option>
                        <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                        <option value="valide" {{ request('statut') == 'valide' ? 'selected' : '' }}>Validés</option>
                        <option value="rembourse" {{ request('statut') == 'rembourse' ? 'selected' : '' }}>Remboursés</option>
                    </select>
                </div>

                <!-- Filtre Action Requise -->
                @if(Auth::user()->role !== 'membre')
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label fw-bold">Action requise</label>
                        <select name="action_requise" class="form-select">
                            <option value="">Toutes les actions</option>
                            <option value="confirmation_date" {{ request('action_requise') == 'confirmation_date' ? 'selected' : '' }}>Confirmation date en attente</option>
                        </select>
                    </div>

                    <!-- Filtre Membre -->
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label fw-bold">Membre</label>
                        <select name="membre_id" class="form-select">
                            <option value="">Tous les membres</option>
                            @foreach($membres as $membre)
                                <option value="{{ $membre->id }}" {{ request('membre_id') == $membre->id ? 'selected' : '' }}>
                                    {{ $membre->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Filtre Dates -->
                <div class="col-md-6 col-lg-3">
                    <label class="form-label fw-bold">Date début</label>
                    <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
                </div>

                <div class="col-md-6 col-lg-3">
                    <label class="form-label fw-bold">Date fin</label>
                    <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
                </div>

                <!-- Filtre Tri -->
                <div class="col-md-6 col-lg-3">
                    <label class="form-label fw-bold">Trier par</label>
                    <select name="sort_by" class="form-select">
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date création</option>
                        <option value="date_echeance" {{ request('sort_by') == 'date_echeance' ? 'selected' : '' }}>Date échéance</option>
                        <option value="montant_demande" {{ request('sort_by') == 'montant_demande' ? 'selected' : '' }}>Montant</option>
                    </select>
                </div>

                <div class="col-md-6 col-lg-3">
                    <label class="form-label fw-bold">Ordre</label>
                    <select name="sort_order" class="form-select">
                        <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Décroissant</option>
                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Croissant</option>
                    </select>
                </div>

                <!-- Boutons -->
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-2"></i>Appliquer les filtres
                        </button>
                        <a href="{{ route('prets.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Cartes de statistiques -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-primary">
                    <h6 class="text-uppercase text-white-50 mb-1">Total Prêts</h6>
                    <h2 class="fw-bold">{{ $prets->count() }}</h2>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-warning">
                    <h6 class="text-uppercase text-white-50 mb-1">En attente</h6>
                    <h2 class="fw-bold">{{ $prets->where('statut', 'en_attente')->count() }}</h2>
                </div>
            </div>
            @if(Auth::user()->role !== 'membre')
                <div class="col-md-3 mb-3">
                    <div class="stat-card bg-danger">
                        <h6 class="text-uppercase text-white-50 mb-1">À confirmer</h6>
                        <h2 class="fw-bold">{{ $prets->where('date_echeance_modifiee', true)->where('est_accepte_par_membre', false)->where('statut', 'en_attente')->count() }}</h2>
                    </div>
                </div>
            @endif
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-success">
                    <h6 class="text-uppercase text-white-50 mb-1">Validés</h6>
                    <h2 class="fw-bold">{{ $prets->where('statut', 'valide')->count() }}</h2>
                </div>
            </div>
        </div>

        <!-- Table des prêts -->
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                <tr>
                    <th>Membre</th>
                    <th>Montant</th>
                    <th>Intérêt</th>
                    <th>À Rembourser</th>
                    <th>Échéance</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($prets as $pret)
                    @php
                        $dateModifiee = $pret->date_echeance_modifiee;
                        $attendConfirmation = $dateModifiee && !$pret->est_accepte_par_membre;
                    @endphp
                    <tr class="{{ $attendConfirmation ? 'table-warning' : '' }}">
                        <td>
                            <div class="d-flex align-items-center">
                                <strong>{{ $pret->user->name }}</strong>
                                @if(Auth::user()->role !== 'membre')
                                    <button onclick="showMembreQuickView({{ $pret->user->id }}, '{{ $pret->user->name }}')"
                                            class="btn btn-sm btn-link text-primary ms-2"
                                            title="Voir la situation du membre">
                                        <i class="bi bi-info-circle"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                        <td class="fw-bold">{{ number_format($pret->montant_demande, 0, ',', ' ') }} F</td>
                        <td class="text-danger">+{{ number_format($pret->interet_total, 0, ',', ' ') }} F</td>
                        <td class="fw-bold text-primary">
                            {{ number_format($pret->montant_demande + $pret->interet_total, 0, ',', ' ') }} F
                        </td>
                        <td>
                            @if($dateModifiee && $pret->date_modification_proposee)
                                <div class="date-modified">
                                    <small class="text-warning fw-bold">
                                        <i class="bi bi-exclamation-triangle"></i> Date modifiée
                                    </small>
                                    <div class="text-danger text-decoration-line-through small">
                                        {{ $pret->getOriginal('date_echeance')->format('d/m/Y') ?? $pret->date_echeance->format('d/m/Y') }}
                                    </div>
                                    <div class="text-success fw-bold">
                                        → {{ \Carbon\Carbon::parse($pret->date_modification_proposee)->format('d/m/Y') }}
                                    </div>
                                </div>
                            @else
                                {{ $pret->date_echeance->format('d/m/Y') }}
                            @endif
                        </td>
                        <td>
                            @if($pret->statut === 'en_attente')
                                @if($pret->est_accepte_par_membre)
                                    <span class="badge bg-warning status-badge">En attente de validation</span>
                                @else
                                    @if($dateModifiee)
                                        <span class="badge bg-danger status-badge">CONFIRMATION DATE</span>
                                    @else
                                        <span class="badge bg-danger status-badge">En attente ACCORD MEMBRE</span>
                                    @endif
                                @endif
                            @elseif($pret->statut === 'valide')
                                <span class="badge bg-success status-badge">Accordé</span>
                            @elseif($pret->statut === 'rembourse')
                                <span class="badge bg-info status-badge">Remboursé</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <!-- CAS 1 : C'EST LE TRÉSORIER -->
                                @if(Auth::user()->role !== 'membre')
                                    @if($pret->statut === 'en_attente')
                                        <!-- Bouton Négocier -->
                                        <a href="{{ route('prets.edit', $pret) }}" class="btn btn-warning btn-sm mb-1">
                                            <i class="bi bi-pencil"></i> Négocier
                                        </a>

                                        <!-- Bouton Valider -->
                                        @if($pret->est_accepte_par_membre)
                                            <form action="{{ route('prets.valider', $pret) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm mb-1">
                                                    <i class="bi bi-check-circle"></i> Valider
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-secondary btn-sm mb-1" disabled>
                                                @if($dateModifiee)
                                                    <i class="bi bi-clock"></i> Date à confirmer
                                                @else
                                                    <i class="bi bi-person-waiting"></i> En attente membre
                                                @endif
                                            </button>
                                        @endif
                                    @endif
                                @endif

                                <!-- CAS 2 : C'EST LE MEMBRE -->
                                @if(Auth::user()->role === 'membre' && $pret->statut === 'en_attente')
                                    @if($dateModifiee && !$pret->est_accepte_par_membre)
                                        <form action="{{ route('prets.accepter', $pret) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-success btn-sm mb-1" title="Accepter la nouvelle date">
                                                <i class="bi bi-check-circle"></i> Accepter
                                            </button>
                                        </form>

                                        <form action="{{ route('prets.destroy', $pret) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Refuser la nouvelle date et annuler la demande ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm mb-1" title="Refuser">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    @elseif(!$pret->est_accepte_par_membre)
                                        <form action="{{ route('prets.accepter', $pret) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-success btn-sm mb-1">
                                                <i class="bi bi-check-circle me-1"></i> Accepter
                                            </button>
                                        </form>
                                    @endif
                                @endif

                                @if(Auth::user()->role === 'membre' && $pret->statut === 'en_attente' && !$dateModifiee)
                                    <form action="{{ route('prets.destroy', $pret) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm mb-1">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($prets instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="d-flex justify-content-center mt-4">
                {{ $prets->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Fonction pour afficher les détails d'un membre
    function showMembreDetails(userId) {
        const loadingHTML = `
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2 text-muted">Chargement des informations...</p>
                </div>
            `;

        Swal.fire({
            title: 'Informations du membre',
            html: loadingHTML,
            showConfirmButton: false,
            showCloseButton: true,
            width: '800px',
            didOpen: () => {
                fetch(`/membre/${userId}/details`)
                    .then(response => response.json())
                    .then(data => {
                        const html = `
                                <div>
                                    <div class="row mb-4">
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-primary">
                                                <div class="card-header bg-primary text-white">
                                                    <i class="bi bi-person-badge me-2"></i> Informations
                                                </div>
                                                <div class="card-body">
                                                    <p class="mb-2"><strong>Nom :</strong> ${data.user.name}</p>
                                                    <p class="mb-2"><strong>Email :</strong> ${data.user.email}</p>
                                                    <p class="mb-2"><strong>Téléphone :</strong> ${data.user.telephone || 'Non renseigné'}</p>
                                                    <p class="mb-0"><strong>Membre depuis :</strong> ${new Date(data.user.created_at).toLocaleDateString('fr-FR')}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="card border-warning">
                                                <div class="card-header bg-warning text-white">
                                                    <i class="bi bi-graph-up me-2"></i> Statistiques
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-6 mb-2">Total prêts: <span class="badge bg-primary">${data.stats.total_prets}</span></div>
                                                        <div class="col-6 mb-2">En attente: <span class="badge bg-warning">${data.stats.prets_en_attente}</span></div>
                                                        <div class="col-6 mb-2">Validés: <span class="badge bg-success">${data.stats.prets_valides}</span></div>
                                                        <div class="col-6 mb-2">Remboursés: <span class="badge bg-info">${data.stats.prets_rembourses}</span></div>
                                                        <div class="col-12 mb-2">
                                                            Total emprunté: <strong>${parseFloat(data.stats.montant_total_emprunte).toLocaleString('fr-FR')} F</strong>
                                                        </div>
                                                        <div class="col-12">
                                                            En retard: <span class="badge ${data.stats.prets_en_retard > 0 ? 'bg-danger' : 'bg-success'}">
                                                                ${data.stats.prets_en_retard}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <i class="bi bi-clock-history me-2"></i> 5 derniers prêts
                                        </div>
                                        <div class="card-body p-0">
                                            ${data.recent_prets.length > 0 ? `
                                                <div class="table-responsive">
                                                    <table class="table table-sm mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Montant</th>
                                                                <th>Intérêt</th>
                                                                <th>Échéance</th>
                                                                <th>Statut</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            ${data.recent_prets.map(pret => `
                                                                <tr>
                                                                    <td class="fw-bold">${pret.montant} F</td>
                                                                    <td class="text-danger">+${pret.interet} F</td>
                                                                    <td>
                                                                        ${pret.date_echeance}
                                                                        ${pret.date_echeance_modifiee ? '<br><small class="text-warning">(Modifiée)</small>' : ''}
                                                                    </td>
                                                                    <td>
                                                                        ${getStatutBadge(pret.statut, pret.est_accepte_par_membre)}
                                                                    </td>
                                                                </tr>
                                                            `).join('')}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            ` : '<p class="p-4 text-center text-muted">Aucun prêt enregistré</p>'}
                                        </div>
                                    </div>
                                </div>
                            `;
                        Swal.getHtmlContainer().innerHTML = html;
                    })
                    .catch(error => {
                        Swal.getHtmlContainer().innerHTML = `
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Erreur: ${error.message}
                                </div>
                            `;
                    });
            }
        });
    }

    // Fonction helper pour les badges de statut
    function getStatutBadge(statut, estAccepte) {
        switch(statut) {
            case 'en_attente':
                return estAccepte
                    ? '<span class="badge bg-warning">En attente trésorier</span>'
                    : '<span class="badge bg-danger">En attente membre</span>';
            case 'valide':
                return '<span class="badge bg-success">Validé</span>';
            case 'rembourse':
                return '<span class="badge bg-info">Remboursé</span>';
            default:
                return '<span class="badge bg-secondary">Inconnu</span>';
        }
    }

    // Fonction pour afficher une vue rapide
    function showMembreQuickView(userId, userName) {
        fetch(`/membre/${userId}/stats`)
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    title: `Situation de ${userName}`,
                    html: `
                            <div>
                                <div class="row g-3 mb-4">
                                    <div class="col-6">
                                        <div class="bg-primary bg-opacity-10 p-3 rounded text-center">
                                            <small class="text-muted d-block">Total</small>
                                            <h4 class="text-primary fw-bold mb-0">${data.total_prets}</h4>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-warning bg-opacity-10 p-3 rounded text-center">
                                            <small class="text-muted d-block">En attente</small>
                                            <h4 class="text-warning fw-bold mb-0">${data.prets_en_attente}</h4>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-success bg-opacity-10 p-3 rounded text-center">
                                            <small class="text-muted d-block">Validés</small>
                                            <h4 class="text-success fw-bold mb-0">${data.prets_valides}</h4>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="${data.prets_en_retard > 0 ? 'bg-danger' : 'bg-success'} bg-opacity-10 p-3 rounded text-center">
                                            <small class="text-muted d-block">En retard</small>
                                            <h4 class="${data.prets_en_retard > 0 ? 'text-danger' : 'text-success'} fw-bold mb-0">
                                                ${data.prets_en_retard}
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button onclick="showMembreDetails(${userId})" class="btn btn-outline-primary">
                                        <i class="bi bi-eye me-2"></i> Voir détails complets
                                    </button>
                                </div>
                            </div>
                        `,
                    showConfirmButton: false,
                    showCloseButton: true,
                    width: '500px'
                });
            })
            .catch(error => {
                Swal.fire('Erreur', 'Impossible de charger les statistiques', 'error');
            });
    }

    // Confirmation pour les suppressions
    document.addEventListener('DOMContentLoaded', function() {
        const deleteForms = document.querySelectorAll('form[onsubmit*="confirm"]');
        deleteForms.forEach(form => {
            form.onsubmit = function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Cette action est irréversible !",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            };
        });
    });
</script>

</div>
</x-app-layout>
