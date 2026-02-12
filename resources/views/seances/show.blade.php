<x-app-layout>
    <div class="container py-4">

        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <a href="{{ route('seances.index') }}" class="text-decoration-none text-muted">&larr; Retour</a>
                <h1 class="mb-0 mt-2">Séance du {{ $seance->date_seance->format('d/m/Y') }}</h1>

                <div class="mt-2 d-flex flex-wrap gap-2">
                    <span class="badge bg-info text-dark">{{ $seance->cycle->nom }}</span>
                    @if($seance->statut == 'fermee')
                        <span class="badge bg-danger">CLÔTURÉE</span>
                    @else
                        <span class="badge bg-success">OUVERTE</span>
                    @endif
                </div>
            </div>
                
            <div class="text-end">
                <h6 class="text-muted text-uppercase small mb-1">Total En Caisse</h6>
                <h2 class="fw-bold text-primary mb-3">{{ number_format($seance->total_encaisse, 0, ',', ' ') }} FCFA</h2>

                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <button class="btn btn-warning btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRemboursement">
                        <i class="fas fa-hand-holding-usd me-1"></i> Remboursement
                    </button>
                    <a href="{{ route('seances.rapport', $seance) }}" class="btn btn-danger btn-sm shadow-sm" target="_blank">
                        <i class="bi bi-file-pdf me-1"></i> Rapport PDF
                    </a>
                </div>
            </div>  
        </div>

        <div class="row mb-4 g-3">
            <!-- INFO CAISSE -->
             <!--
            <!-- INFO CAISSE SUPPRIMÉE DEMANDE CLIENT -->

            <!-- ACTIONS SEANCE -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column justify-content-center text-end">
                        <h6 class="text-muted text-uppercase small mb-2">Argent Physique (Sur table)</h6>
                        <h3 class="fw-bold text-primary mb-3">{{ number_format($seance->total_encaisse, 0, ',', ' ') }} FCFA</h3>
                        <div class="d-flex justify-content-end flex-wrap gap-2">
                            <button class="btn btn-outline-warning shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRemboursement">
                                <i class="fas fa-hand-holding-usd me-1"></i> Rembourser Prêt
                            </button>
                            <a href="{{ route('seances.rapport', $seance) }}" class="btn btn-outline-danger shadow-sm" target="_blank">
                                <i class="bi bi-file-pdf me-1"></i> Télécharger PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-start border-success border-4" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-start border-danger border-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-start border-danger border-4" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            <!-- COLONNE GAUCHE : Formulaire d'ajout (Seulement si ouverte) -->
            <div class="col-lg-4">
                @if($seance->statut == 'ouverte')
                    <div class="card shadow-sm mb-4 border-primary">
                        <div class="card-header bg-primary text-white py-3">
                            <h5 class="card-title mb-0">Nouvelle Cotisation</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('cotisations.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="seance_id" value="{{ $seance->id }}">

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Membre</label>
                                    <select name="user_id" id="cotisationUserSelect" class="form-select" required onchange="updateRestantTontine()">
                                        <option value="">-- Sélectionner --</option>
                                        @foreach($membres as $membre)
                                            <option value="{{ $membre->id }}" 
                                                data-deja-paye="{{ $cotisationsTontineParMembre[$membre->id] ?? 0 }}">
                                                {{ $membre->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="restantInfo" class="mt-2 text-danger small fw-bold d-none">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        Déjà payé : <span id="dejaPayeDisplay">0</span> F 
                                        / Reste : <span id="resteDisplay">0</span> F
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Type</label>
                                    <div class="d-grid gap-2">
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="type" id="tontine" value="tontine" checked onchange="toggleRestantInfo()">
                                            <label class="btn btn-outline-primary" for="tontine">Tontine (Épargne)</label>

                                            <input type="radio" class="btn-check" name="type" id="secours" value="secours" onchange="toggleRestantInfo()">
                                            <label class="btn btn-outline-warning" for="secours">Secours (Caisse)</label>

                                            <input type="radio" class="btn-check" name="type" id="banque" value="banque" onchange="toggleRestantInfo()">
                                            <label class="btn btn-outline-info" for="banque">Banque (Placement)</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Montant (FCFA)</label>
                                    <input type="number" name="montant" id="montantInput" class="form-control form-control-lg fw-bold" placeholder="Ex: 10000" min="500" step="500" required>
                                </div>

                                <script>
                                    const montantPartFixe = {{ $seance->cycle->montant_part }};
                                    
                                    function updateRestantTontine() {
                                        const select = document.getElementById('cotisationUserSelect');
                                        const selectedOption = select.options[select.selectedIndex];
                                        const dejaPaye = parseFloat(selectedOption.getAttribute('data-deja-paye')) || 0;
                                        const restantDiv = document.getElementById('restantInfo');
                                        const montantInput = document.getElementById('montantInput');
                                        const typeTontine = document.getElementById('tontine').checked;

                                        if (select.value && typeTontine) {
                                            const reste = Math.max(0, montantPartFixe - dejaPaye);
                                            
                                            if (reste > 0 || dejaPaye > 0) {
                                                document.getElementById('dejaPayeDisplay').textContent = new Intl.NumberFormat().format(dejaPaye);
                                                document.getElementById('resteDisplay').textContent = new Intl.NumberFormat().format(reste);
                                                restantDiv.classList.remove('d-none');
                                                
                                                // Pré-remplir avec le reste si > 0
                                                if (reste > 0) {
                                                    montantInput.value = reste;
                                                }
                                            } else {
                                                restantDiv.classList.add('d-none');
                                                montantInput.value = montantPartFixe;
                                            }
                                        } else {
                                            restantDiv.classList.add('d-none');
                                        }
                                    }

                                    function toggleRestantInfo() {
                                        updateRestantTontine();
                                    }
                                </script>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg fw-bold py-2">Encaisser</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- BLOC DÉPENSES -->
                    <div class="card shadow-sm mb-4 border-danger">
                        <div class="card-header bg-danger text-white py-3">
                            <h5 class="card-title mb-0">Enregistrer une Dépense</h5>
                            <small class="opacity-75">(Collation, Transport, Achats...)</small>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('depenses.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="seance_id" value="{{ $seance->id }}">

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Motif</label>
                                    <input type="text" name="motif" class="form-control" placeholder="Ex: Boissons réunion" required>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Montant (FCFA)</label>
                                    <div class="input-group">
                                        <span class="input-group-text text-danger fw-bold">-</span>
                                        <input type="number" name="montant" class="form-control fw-bold" placeholder="0" min="100" step="500" required>
                                        <span class="input-group-text">FCFA</span>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-outline-danger btn-lg fw-bold py-2">Sortir l'argent</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- BLOC VERSEMENT DES LOTS (NOUVEAU) -->
                    <div class="card shadow-sm border-success mb-4">
                        <div class="card-header bg-success text-white py-3">
                            <h5 class="card-title mb-0">Autoriser Gain Tontine (Versement)</h5>
                            <small class="opacity-75">Payez le lot à un membre bénéficiaire</small>
                        </div>
                        <div class="card-body">
                            @if($paiementRecentBloque)
                                <div class="alert alert-warning border-warning mb-0">
                                    <i class="fas fa-lock me-2"></i>
                                    <strong>Versement bloqué temporairement</strong>
                                    <p class="mb-2 mt-2">Un lot a déjà été versé pour cette période le {{ $dernierPaiement->date_paiement->format('d/m/Y') }}.</p>
                                    <p class="mb-0">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        Prochain versement possible à partir du : <strong>{{ $prochainPaiementDate->format('d/m/Y') }}</strong>
                                    </p>
                                </div>
                            @else
                                <form action="{{ route('paiements-lots.store') }}" method="POST" onsubmit="return confirm('Confirmer le versement du lot ? Cette action débitera la caisse.');">
                                    @csrf
                                    <input type="hidden" name="seance_id" value="{{ $seance->id }}">

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Bénéficiaire</label>
                                        <select name="user_id" class="form-select" required>
                                            <option value="">-- Choisir qui reçoit --</option>
                                            @foreach($membres as $membre)
                                                @php
                                                    $dejaPaye = \App\Models\PaiementLot::where('cycle_id', $seance->cycle_id)->where('user_id', $membre->id)->exists();
                                                    $isSelected = (isset($beneficiaireAttendu) && $beneficiaireAttendu->id == $membre->id && !$dejaPaye);
                                                @endphp
                                                <option value="{{ $membre->id }}" {{ $dejaPaye ? 'disabled' : ($isSelected ? 'selected' : '') }}>
                                                    {{ $membre->name }} 
                                                    {{ $isSelected ? '(Bénéficiaire prévu ce mois)' : '' }}
                                                    {{ $dejaPaye ? '(Déjà payé dans ce cycle)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Montant à verser (FCFA)</label>
                                        <input type="number" name="montant" class="form-control form-control-lg fw-bold border-success text-success" 
                                               value="{{ $seance->cycle->montant_part * $seance->cycle->membres->count() }}" required>
                                        <small class="text-muted">Valeur suggérée : (Nombre de membres × Parts)</small>
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-success btn-lg fw-bold py-2">
                                            <i class="bi bi-cash-coin me-2"></i>Effectuer le Versement
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning border-warning">
                        <i class="fas fa-lock me-2"></i>
                        Cette séance est fermée. Vous ne pouvez plus ajouter de cotisations.
                    </div>
                @endif
            </div>

            <!-- COLONNE DROITE : Historique des transactions -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold">Historique des transactions de la séance</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Membre</th>
                                    <th>Type</th>
                                    <th>Heure</th>
                                    <th class="text-end pe-3">Montant</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($seance->cotisations->sortByDesc('created_at') as $cotisation)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center">
                                                <x-user-avatar :user="$cotisation->user" size="30" class="me-2" />
                                                <div class="fw-semibold">{{ $cotisation->user->name }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($cotisation->type == 'tontine')
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">Tontine</span>
                                            @elseif($cotisation->type == 'secours')
                                                <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25">Secours</span>
                                            @else
                                                <span class="badge bg-info bg-opacity-10 text-info-emphasis border border-info border-opacity-25">Banque</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">
                                            {{ $cotisation->created_at->format('H:i') }}
                                            @if($cotisation->auteur)
                                                <span class="badge bg-light text-secondary border ms-1" title="Enregistré par">
                                                        <i class="small">Par : {{ $cotisation->auteur->name }}</i>
                                                    </span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold pe-3">
                                            {{ number_format($cotisation->montant, 0, ',', ' ') }} FCFA
                                        </td>
                                        <td class="text-end pe-3">
                                            @if($seance->statut == 'ouverte')
                                                <form action="{{ route('cotisations.destroy', $cotisation) }}" method="POST" onsubmit="return confirm('Annuler cette cotisation ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Annuler">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-3 opacity-25"></i>
                                            <p class="mb-0">Aucune cotisation enregistrée pour le moment.</p>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- LISTE DES DÉPENSES -->
                @if($seance->depenses->count() > 0)
                    <div class="card shadow-sm border-0 mt-4">
                        <div class="card-header bg-light border-bottom py-3">
                            <h6 class="mb-0 fw-bold text-danger">
                                <i class="fas fa-arrow-down me-2"></i> Dépenses de fonctionnement
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <tbody>
                                    @foreach($seance->depenses as $depense)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-semibold">{{ $depense->motif }}</div>
                                                <small class="text-muted">
                                                    Par : {{ $depense->auteur->name }}
                                                </small>
                                            </td>
                                            <td class="text-end text-danger fw-bold pe-3">
                                                - {{ number_format($depense->montant, 0, ',', ' ') }} FCFA
                                                <br>
                                                @if($depense->statut == 'validee')
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Validée</span>
                                                @elseif($depense->statut == 'rejetee')
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">Rejetée</span>
                                                @else
                                                    <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25">En attente</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-3">
                                                @if($depense->statut === 'en_attente' && auth()->user()->role === 'admin')
                                                    <div class="btn-group btn-group-sm">
                                                        <form action="{{ route('depenses.valider', $depense) }}" method="POST">
                                                            @csrf @method('PUT')
                                                            <button class="btn btn-outline-success" title="Valider">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('depenses.rejeter', $depense) }}" method="POST">
                                                            @csrf @method('PUT')
                                                            <button class="btn btn-outline-secondary" title="Rejeter">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif

                                                @if($seance->statut == 'ouverte' && $depense->statut !== 'validee')
                                                    <form action="{{ route('depenses.destroy', $depense) }}" method="POST" onsubmit="return confirm('Annuler cette dépense ?')" class="d-inline ms-2">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-link text-danger p-0">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Preuve de versement -->
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-file-invoice-dollar me-2 text-primary"></i> Preuve de Versement Bancaire
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <!-- Colonne Info / Statut -->
                            <div class="col-md-6 mb-3 mb-md-0">
                                @if($seance->preuve_versement)
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="me-3">
                                            @if(Str::endsWith($seance->preuve_versement, '.pdf'))
                                                <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                            @else
                                                <img src="{{ asset('storage/' . $seance->preuve_versement) }}" class="rounded shadow-sm" style="width: 80px; height: 80px; object-fit: cover;" alt="Preuve">
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1">Document téléversé</h6>
                                            <div class="mb-2">
                                                @if($seance->etat_versement == 'valide')
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                                        <i class="fas fa-check-circle me-1"></i> Validé par l'Admin
                                                    </span>
                                                @elseif($seance->etat_versement == 'rejete')
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                                        <i class="fas fa-times-circle me-1"></i> Rejeté
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25">
                                                        <i class="fas fa-clock me-1"></i> En attente de validation
                                                    </span>
                                                @endif
                                            </div>
                                            <a href="{{ asset('storage/' . $seance->preuve_versement) }}" target="_blank" class="small text-primary text-decoration-none d-inline-block">
                                                <i class="fas fa-external-link-alt me-1"></i> Voir le document complet
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Actions Admin -->
                                    @if($seance->etat_versement == 'en_attente' && auth()->user()->role === 'admin')
                                        <div class="mt-3 p-3 bg-light rounded">
                                            <p class="small text-muted mb-2">En tant qu'administrateur, validez la conformité de ce document :</p>
                                            <div class="d-flex gap-2">
                                                <form action="{{ route('seances.valider_preuve', $seance) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <button class="btn btn-sm btn-success">
                                                        <i class="fas fa-check me-1"></i> Valider
                                                    </button>
                                                </form>
                                                <form action="{{ route('seances.rejeter_preuve', $seance) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment rejeter ce document ?');">
                                                    @csrf @method('PUT')
                                                    <button class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-times me-1"></i> Rejeter
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif

                                @else
                                    <div class="text-muted text-center py-4">
                                        <i class="fas fa-cloud-upload-alt fa-3x mb-3 opacity-50"></i>
                                        <p class="mb-0">Aucune preuve de versement n'a été ajoutée pour cette séance.</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Colonne Upload -->
                            <div class="col-md-6">
                                @if(!$seance->preuve_versement || $seance->etat_versement == 'rejete')
                                    <div class="bg-light p-4 rounded-3 border border-dashed">
                                        <h6 class="fw-bold mb-3">Ajouter un reçu (Banque / Mobile Money)</h6>
                                        <form action="{{ route('seances.upload_preuve', $seance) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="input-group mb-3">
                                                <input type="file" name="preuve" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                                            </div>
                                            <div class="text-end">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-upload me-1"></i> Envoyer la preuve
                                                </button>
                                            </div>
                                            <small class="text-muted d-block mt-3" style="font-size: 0.75rem;">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Formats acceptés : JPG, PNG, PDF. Max 4Mo.
                                            </small>
                                        </form>
                                    </div>
                                @elseif($seance->etat_versement == 'valide')
                                    <div class="alert alert-success border-0 bg-success bg-opacity-10 mb-0 p-3">
                                        <h6 class="alert-heading fw-bold text-success mb-1">
                                            <i class="fas fa-shield-alt me-2"></i> Document Sécurisé
                                        </h6>
                                        <p class="mb-0 small text-success">
                                            Ce versement a été vérifié et validé. Il ne peut plus être modifié.
                                        </p>
                                    </div>
                                @elseif($seance->etat_versement == 'en_attente')
                                    <div class="alert alert-warning border-0 bg-warning bg-opacity-10 mb-0 p-3">
                                        <p class="mb-0 small text-warning-emphasis">
                                            <i class="fas fa-spinner fa-spin me-2"></i>
                                            Le document est en cours d'analyse par les administrateurs.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Remboursement -->
    <div class="modal fade" id="modalRemboursement" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning bg-opacity-10 border-bottom py-3">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fas fa-hand-holding-usd me-2"></i> Remboursement de Prêt
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('remboursements.store') }}" method="POST" id="formRemboursement">
                        @csrf
                        <input type="hidden" name="seance_id" value="{{ $seance->id }}">

                        <div class="mb-4">
                            <label class="form-label fw-bold">Rechercher un membre endetté</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="searchMember" placeholder="Tapez un nom...">
                            </div>
                            <ul class="list-group mt-2" id="resultsMember" style="max-height: 200px; overflow-y: auto; display: none;">
                                <!-- Résultats Ajax -->
                            </ul>
                        </div>

                        <!-- Info Membre sélectionné -->
                        <div id="selectedMemberInfo" class="d-none alert alert-light border rounded p-3 mb-4">
                            <h6 class="fw-bold mb-2" id="memberName"></h6>
                            <input type="hidden" name="user_id" id="selectedUserId">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Choisir le prêt à rembourser :</label>
                                <select name="pret_id" id="pretSelect" class="form-select" required>
                                    <!-- Options remplies via JS -->
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Montant à Rembourser (FCFA)</label>
                            <input type="number" name="montant" class="form-control form-control-lg fw-bold" placeholder="0" min="500" step="100" required>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-info-circle me-1"></i>
                                Le montant sera ajouté à la caisse de la séance.
                            </small>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning btn-lg fw-bold py-3">Enregistrer le Remboursement</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    const searchInput = document.getElementById('searchMember');
    const resultsList = document.getElementById('resultsMember');
    const memberInfo = document.getElementById('selectedMemberInfo');
    const memberName = document.getElementById('memberName');
    const userIdInput = document.getElementById('selectedUserId');
    const pretSelect = document.getElementById('pretSelect');

    let timeout = null;

    searchInput.addEventListener('keyup', function() {
        clearTimeout(timeout);
        const query = this.value;

        if (query.length < 2) {
            resultsList.style.display = 'none';
            return;
        }

        timeout = setTimeout(() => {
            fetch(`{{ route('remboursements.search', $seance) }}?q=${query}`)
                .then(response => response.json())
                .then(data => {
                    resultsList.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(user => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item list-group-item-action cursor-pointer';
                            li.innerHTML = `<div><strong>${user.text}</strong></div>`;
                            li.onclick = () => selectMember(user);
                            resultsList.appendChild(li);
                        });
                        resultsList.style.display = 'block';
                    } else {
                        resultsList.innerHTML = '<li class="list-group-item text-muted">Aucun membre trouvé avec des dettes.</li>';
                        resultsList.style.display = 'block';
                    }
                });
        }, 300);
    });

    function selectMember(user) {
        // Cacher la liste
        resultsList.style.display = 'none';
        searchInput.value = ''; // Reset search

        // Afficher les infos
        memberInfo.classList.remove('d-none');
        memberName.textContent = user.text;
        userIdInput.value = user.id;

        // Remplir le select des prêts
        pretSelect.innerHTML = '';
        user.prets.forEach(pret => {
            const option = document.createElement('option');
            option.value = pret.pret_id;
            option.text = `#${pret.pret_id} - Reste: ${new Intl.NumberFormat().format(pret.reste)} F (Total: ${new Intl.NumberFormat().format(pret.montant_du)} F)`;
            pretSelect.appendChild(option);
        });
    }
</script>
