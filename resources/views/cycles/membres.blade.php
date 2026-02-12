<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold text-primary mb-0">
            <i class="bi bi-people me-2"></i>{{ __('Gestion des Membres') }} : {{ $cycle->nom }}
        </h2>
    </x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
        <style>
            /* Styles améliorés pour le drag & drop */
            .sortable-row {
                cursor: move;
                transition: all 0.2s ease;
                border-left: 3px solid transparent;
            }
            .sortable-row:hover {
                background-color: rgba(13, 110, 253, 0.05) !important;
                border-left-color: #0d6efd;
            }
            .sortable-row.sortable-ghost {
                opacity: 0.4;
                background-color: #f8f9fa;
                border: 1px dashed #0d6efd;
            }
            .sortable-row.sortable-chosen {
                background-color: rgba(255, 193, 7, 0.15) !important;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
            .sortable-row.sortable-drag {
                opacity: 0.8;
                transform: rotate(1deg);
            }

            /* Colonnes compactes */
            .drag-column {
                width: 40px !important;
                min-width: 40px;
                padding: 8px 4px !important;
            }
            .rank-column {
                width: 60px !important;
                min-width: 60px;
                padding: 8px 4px !important;
            }
            .avatar-column {
                width: 50px !important;
                min-width: 50px;
                padding: 8px !important;
            }
            .actions-column {
                width: 80px !important;
                min-width: 80px;
                padding: 8px !important;
            }

            /* Style du rang */
            .rank-badge {
                display: inline-block;
                width: 32px;
                height: 32px;
                line-height: 32px;
                border-radius: 50%;
                background: linear-gradient(135deg, #6f42c1, #0d6efd);
                color: white;
                font-weight: 700;
                font-size: 14px;
                text-align: center;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            /* Avatar compact */
            .compact-avatar {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                object-fit: cover;
                border: 2px solid #e9ecef;
                background-color: #f8f9fa;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .compact-avatar i {
                font-size: 18px;
                color: #6c757d;
            }

            /* Poignée de drag étendue */
            .drag-handle-area {
                cursor: grab;
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 4px;
                transition: background-color 0.2s;
            }
            .drag-handle-area:hover {
                background-color: rgba(13, 110, 253, 0.1);
            }
            .drag-handle-area:active {
                cursor: grabbing;
            }
            .drag-icon {
                color: #6c757d;
                font-size: 20px;
                transition: transform 0.2s;
            }
            .drag-handle-area:hover .drag-icon {
                color: #0d6efd;
                transform: scale(1.1);
            }

            /* Style des inputs cachés */
            .rank-input {
                display: none;
            }

            /* Animation de rang */
            @keyframes rankUpdate {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }
            .rank-updated {
                animation: rankUpdate 0.3s ease;
            }

            /* En-tête de table compact */
            .compact-header th {
                padding: 10px 8px !important;
                font-size: 0.85rem;
            }
            .compact-header .drag-header {
                text-align: center;
                width: 40px;
            }
            .compact-header .rank-header {
                text-align: center;
                width: 60px;
            }

            /* Info au survol */
            .row-hint {
                position: absolute;
                top: -25px;
                left: 50%;
                transform: translateX(-50%);
                background: #0d6efd;
                color: white;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 12px;
                opacity: 0;
                transition: opacity 0.3s;
                pointer-events: none;
                white-space: nowrap;
            }
            .sortable-row:hover .row-hint {
                opacity: 1;
            }

            /* Table compact */
            .compact-table {
                font-size: 0.9rem;
            }
            .compact-table td {
                vertical-align: middle;
                padding: 10px 8px !important;
            }
            .compact-table .user-name {
                font-weight: 600;
                font-size: 0.95rem;
                color: #212529;
            }
            .compact-table .user-email {
                font-size: 0.8rem;
                color: #6c757d;
            }
        </style>
    @endpush

    <div class="container py-4">
        <!-- ... Header et messages existants ... -->

        <div class="row">
            <!-- Liste des Membres avec Ordre - SECTION AMÉLIORÉE -->
            <div class="{{ Auth::user()->role === 'membre' ? 'col-lg-12' : 'col-lg-8' }} mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                        <div>
                            <h5 class="card-title mb-0 text-primary">
                                <i class="bi bi-sort-numeric-down me-2"></i>Ordre de Passage
                            </h5>
                            @if(Auth::user()->role !== 'membre')
                            <p class="text-muted small mb-0 mt-1">Glissez-déposez n'importe où sur la ligne pour réorganiser</p>
                            @endif
                        </div>
                        @if(Auth::user()->role !== 'membre')
                        @php
                            $cycleLocked = \App\Models\PaiementLot::where('cycle_id', $cycle->id)->exists();
                        @endphp
                        @if(!$cycleLocked)
                        <form action="{{ route('cycles.membres.randomize', $cycle) }}" method="POST"
                              onsubmit="return confirm('Voulez-vous vraiment mélanger l\'ordre ? Cette action est irréversible.');">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning btn-sm">
                                <i class="bi bi-shuffle me-1"></i>Mélanger
                            </button>
                        </form>
                        @endif
                        @endif
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card shadow-sm border-0">
                        @if($membres->isEmpty())
                            <div class="text-center py-5">
                                <p class="text-muted">Aucun membre dans ce cycle.</p>
                            </div>
                        @else
                            <form action="{{ route('cycles.membres.order', $cycle) }}" method="POST" id="orderForm">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 compact-table">
                                        <thead class="table-light compact-header">
                                        <tr>
                                            @if(Auth::user()->role !== 'membre')
                                            <th scope="col" class="drag-header">
                                                <div class="text-center">
                                                    <i class="bi bi-arrows-move text-muted"></i>
                                                </div>
                                            </th>
                                            @endif
                                            <th scope="col" class="rank-header">Rang</th>
                                            <th scope="col">Membre</th>
                                            <th scope="col">Prévision Gain</th>
                                            @if(Auth::user()->role !== 'membre')
                                            <th scope="col" class="actions-column text-center">Actions</th>
                                            @endif
                                        </tr>
                                        </thead>
                                        <tbody id="sortable-members">
                                        @php
                                            $startDate = $cycle->date_debut;
                                            $freq = $cycle->frequence_paiement ?? 'mensuelle';
                                        @endphp
                                        @foreach($membres as $membre)
                                            @php
                                                $rank = $membre->pivot->rang;
                                                $interval = $freq === 'hebdomadaire' ? ($rank - 1) . ' weeks' : ($rank - 1) . ' months';
                                                $estimatedDate = $startDate->copy()->add($interval);

                                                // Vérifier si déjà payé pour ce cycle
                                                $dejaPaye = \App\Models\PaiementLot::where('cycle_id', $cycle->id)
                                                    ->where('user_id', $membre->id)
                                                    ->where('statut', 'confirme')
                                                    ->exists();
                                            @endphp
                                            <tr data-id="{{ $membre->id }}"
                                                data-rank="{{ $membre->pivot->rang }}"
                                                class="{{ Auth::user()->role !== 'membre' ? 'sortable-row' : '' }} position-relative {{ $membre->id === Auth::id() ? 'table-primary' : '' }}">

                                                @if(Auth::user()->role !== 'membre')
                                                <!-- Colonne Drag Handle -->
                                                <td class="drag-column">
                                                    <div class="drag-handle-area">
                                                        <i class="bi bi-grip-vertical drag-icon"></i>
                                                    </div>
                                                    <div class="row-hint">Glisser pour réorganiser</div>
                                                    <input type="hidden"
                                                           name="rangs[{{ $membre->id }}]"
                                                           value="{{ $membre->pivot->rang }}"
                                                           class="rank-input">
                                                </td>
                                                @endif

                                                <!-- Colonne Rang -->
                                                <td class="rank-column text-center">
                                                    <div class="rank-badge" data-rank="{{ $membre->pivot->rang }}">
                                                        {{ $membre->pivot->rang }}
                                                    </div>
                                                </td>

                                                <!-- Colonne Membre -->
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="compact-avatar me-3">
                                                            @if(isset($membre->avatar))
                                                                <img src="{{ asset($membre->avatar) }}"
                                                                     alt="{{ $membre->name }}"
                                                                     class="rounded-circle w-100 h-100">
                                                            @else
                                                                <i class="bi bi-person"></i>
                                                            @endif
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="user-name {{ $membre->id === Auth::id() ? 'text-primary fw-bold' : '' }}">
                                                                {{ $membre->name }}
                                                                @if($membre->id === Auth::id()) (Vous) @endif
                                                            </div>
                                                            <div class="user-email">{{ $membre->email }}</div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Colonne Prévision Gain -->
                                                <td>
                                                    @if($dejaPaye)
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle me-1"></i>Déjà reçu
                                                        </span>
                                                    @else
                                                        <div class="fw-bold text-dark">
                                                            {{ $estimatedDate->translatedFormat('d F Y') }}
                                                        </div>
                                                        <div class="small text-muted">
                                                             {{ $estimatedDate->diffForHumans() }}
                                                        </div>
                                                    @endif
                                                </td>

                                                @if(Auth::user()->role !== 'membre')
                                                <!-- Colonne Actions -->
                                                <td class="actions-column text-center">
                                                <td class="actions-column text-center">
                                                    @php
                                                        // On vérifie si le cycle a démarré (s'il y a déjà eu des paiements)
                                                        $paiementsExistants = \App\Models\PaiementLot::where('cycle_id', $cycle->id)->exists();
                                                    @endphp

                                                    @if(!$paiementsExistants)
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-danger"
                                                                title="Retirer du cycle"
                                                                onclick="confirmDeleteMember('{{ route('cycles.membres.destroy', [$cycle, $membre]) }}')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @else
                                                        <span class="text-muted small" title="Cycle démarré"><i class="bi bi-lock"></i></span>
                                                    @endif
                                                </td>
                                                </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if(Auth::user()->role !== 'membre' && !$paiementsExistants)
                                <!-- Indicateur de rang actuel -->
                                <div class="bg-light border-top p-3">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="d-flex align-items-center">
                                                <div class="rank-badge me-3" style="background: linear-gradient(135deg, #20c997, #198754);">
                                                    1
                                                </div>
                                                <div class="small text-muted">
                                                    <strong>Rang 1</strong> sera le premier à recevoir le fonds de tontine
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-save me-2"></i>Enregistrer l'Ordre
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </form>
                        @endif
                    </div>

                </div>

                @if(Auth::user()->role !== 'membre')
                <!-- Légende -->
                <div class="mt-3">
                    <div class="d-flex align-items-center justify-content-center text-muted small">
                        <div class="me-4 d-flex align-items-center">
                            <div class="rank-badge me-2" style="width: 20px; height: 20px; line-height: 20px; font-size: 10px; background: linear-gradient(135deg, #6f42c1, #0d6efd);">
                                1
                            </div>
                            <span>Position dans l'ordre</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-grip-vertical text-muted me-2"></i>
                            <span>Glissez-déposez pour réorganiser</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            @if(Auth::user()->role !== 'membre')
            <!-- Ajouter un Membre -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 text-success">
                            <i class="bi bi-person-plus me-2"></i>Ajouter un Membre
                        </h5>
                    </div>

                    <div class="card-body">
                        @if($usersDisponibles->isEmpty())
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="bi bi-check-circle text-success" style="font-size: 2.5rem;"></i>
                                </div>
                                <p class="text-muted mb-0">Tous les membres actifs sont déjà dans ce cycle.</p>
                            </div>
                        @else
                            <form action="{{ route('cycles.membres.store', $cycle) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="user_id" class="form-label fw-bold">Sélectionner un membre</label>
                                    <select name="user_id" id="user_id" class="form-select" required>
                                        <option value="">-- Choisir un membre --</option>
                                        @foreach($usersDisponibles as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Membres disponibles : {{ $usersDisponibles->count() }}
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-plus-circle me-2"></i>Ajouter au cycle
                                </button>
                            </form>
                        @endif

                        <!-- Informations -->
                        <div class="alert alert-info mt-4 info-box">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="bi bi-lightbulb fs-4 text-info"></i>
                                </div>
                                <div>
                                    <h6 class="alert-heading fw-bold mb-2">Comment modifier l'ordre ?</h6>
                                    <ul class="mb-0 ps-3 small">
                                        <li class="mb-1">Glissez-déposez n'importe où sur la ligne pour réorganiser</li>
                                        <li class="mb-1">Les rangs se mettent à jour automatiquement</li>
                                        <li>Cliquez sur "Enregistrer l'Ordre" pour sauvegarder</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Statistiques -->
                        <div class="mt-3 p-3 bg-light rounded">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-bar-chart me-2"></i>Statistiques du cycle
                            </h6>
                            <div class="row">
                                <div class="col-6">
                                    <div class="text-center p-2">
                                        <div class="text-primary fw-bold fs-4">{{ $membres->count() }}</div>
                                        <div class="text-muted small">Membres dans le cycle</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-2">
                                        <div class="text-success fw-bold fs-4">{{ $usersDisponibles->count() }}</div>
                                        <div class="text-muted small">Membres disponibles</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>


    <!-- Formulaire global pour supprimer un membre -->
    <form id="deleteMemberForm" method="POST" action="" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    @push('scripts')
        <!-- Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <!-- SortableJS -->
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

        <script>
            function confirmDeleteMember(url) {
                if(confirm('Voulez-vous vraiment retirer ce membre du cycle ?')) {
                    const form = document.getElementById('deleteMemberForm');
                    form.action = url;
                    form.submit();
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                const sortableList = document.getElementById('sortable-members');

                @if(!$paiementsExistants)
                if (sortableList) {
                    // Initialiser Sortable sur TOUTE la ligne
                    const sortable = new Sortable(sortableList, {

                        animation: 150,
                        handle: '.sortable-row', // TOUTE la ligne est draggable
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        onStart: function(evt) {
                            // Ajouter une classe pendant le drag
                            evt.item.classList.add('sortable-drag');
                        },
                        onEnd: function(evt) {
                            // Retirer la classe après le drag
                            evt.item.classList.remove('sortable-drag');
                            // Mettre à jour les rangs
                            updateRanks();
                        }
                    });

                    // Fonction pour mettre à jour les rangs
                    function updateRanks() {
                        const rows = document.querySelectorAll('#sortable-members tr');
                        rows.forEach((row, index) => {
                            const newRank = index + 1;

                            // Mettre à jour l'input caché
                            const rankInput = row.querySelector('.rank-input');
                            if (rankInput) {
                                rankInput.value = newRank;
                            }

                            // Mettre à jour le badge avec animation
                            const rankBadge = row.querySelector('.rank-badge');
                            if (rankBadge) {
                                rankBadge.textContent = newRank;
                                rankBadge.classList.add('rank-updated');

                                // Changer la couleur en fonction du rang
                                if (newRank === 1) {
                                    rankBadge.style.background = 'linear-gradient(135deg, #20c997, #198754)';
                                } else if (newRank <= 3) {
                                    rankBadge.style.background = 'linear-gradient(135deg, #0d6efd, #6f42c1)';
                                } else {
                                    rankBadge.style.background = 'linear-gradient(135deg, #6c757d, #495057)';
                                }

                                // Retirer l'animation après un délai
                                setTimeout(() => {
                                    rankBadge.classList.remove('rank-updated');
                                }, 300);
                            }

                            // Mettre à jour l'attribut data-rank
                            row.setAttribute('data-rank', newRank);
                        });

                        // Afficher un feedback
                        showRankUpdateFeedback();
                    }

                    // Fonction pour afficher un feedback
                    function showRankUpdateFeedback() {
                        const feedback = document.createElement('div');
                        feedback.className = 'position-fixed bottom-0 end-0 m-3';
                        feedback.innerHTML = `
                            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                                <i class="bi bi-check-circle me-2"></i>
                                Ordre mis à jour - Cliquez sur "Enregistrer" pour sauvegarder
                                <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
                            </div>
                        `;
                        document.body.appendChild(feedback);

                        // Supprimer après 3 secondes
                        setTimeout(() => {
                            feedback.remove();
                        }, 3000);
                    }

                    // Initialiser les rangs au chargement
                    updateRanks();

                    // Permettre de cliquer sur toute la ligne pour la sélection
                    sortableList.addEventListener('click', function(e) {
                        // Empêcher le clic sur les boutons d'action
                        if (e.target.closest('button') || e.target.tagName === 'BUTTON') {
                            return;
                        }

                        // Trouver la ligne cliquée
                        const row = e.target.closest('.sortable-row');
                        if (row && !e.target.closest('.drag-handle-area')) {
                            // Animation de sélection
                            row.style.backgroundColor = 'rgba(13, 110, 253, 0.08)';
                            setTimeout(() => {
                                row.style.backgroundColor = '';
                            }, 300);
                        }
                    });
                }
                @endif

                // Améliorer l'expérience de drag avec feedback visuel
                document.addEventListener('mousedown', function(e) {
                    if (e.target.closest('.sortable-row')) {
                        const row = e.target.closest('.sortable-row');
                        row.style.cursor = 'grabbing';
                    }
                });

                document.addEventListener('mouseup', function(e) {
                    const rows = document.querySelectorAll('.sortable-row');
                    rows.forEach(row => {
                        row.style.cursor = 'move';
                    });
                });

                // Confirmation pour les suppressions
                const deleteForms = document.querySelectorAll('form[onsubmit*="confirm"]');
                deleteForms.forEach(form => {
                    form.onsubmit = function(e) {
                        if (!confirm('Êtes-vous sûr de vouloir retirer ce membre du cycle ?')) {
                            e.preventDefault();
                            return false;
                        }
                        return true;
                    };
                });
            });
        </script>
    @endpush
</x-app-layout>
