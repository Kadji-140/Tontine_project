<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold text-primary mb-0">
            <i class="bi bi-bank me-2"></i>{{ __('Répartition des Intérêts - Banque') }} : {{ $cycle->nom }}
        </h2>
    </x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
        <style>
            .stat-card {
                border-radius: 10px;
                padding: 1.5rem;
                color: white;
                margin-bottom: 1rem;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .table-responsive {
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 0 20px rgba(0,0,0,0.08);
            }
            .table th {
                font-weight: 600;
                background-color: #f8f9fa;
            }
            .table tfoot td {
                background-color: #e9ecef;
                font-weight: bold;
                font-size: 1.05rem;
            }
            .highlight-cell {
                background-color: rgba(25, 135, 84, 0.1) !important;
                border-left: 3px solid #198754;
            }
            .total-cell {
                background-color: #f8f9fa !important;
                font-weight: bold;
            }
            .info-box {
                border-left: 4px solid #0dcaf0;
                background-color: #f8f9fa;
            }
        </style>
    @endpush

    <div class="container py-4">
        <!-- Bouton Retour -->
        <div class="mb-4">
            <a href="{{ route('cycles.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Retour aux cycles
            </a>
        </div>

        <!-- Résumé Global -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-primary">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-cash-stack fs-4 me-2"></i>
                        <h6 class="mb-0 text-uppercase fw-bold opacity-75">Total Épargne (Assiette)</h6>
                    </div>
                    <h3 class="fw-bold mb-1">{{ number_format($totalAssiette, 0, ',', ' ') }} FCFA</h3>
                    <small class="opacity-75">Somme des cotisations "Banque"</small>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="stat-card bg-success">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-graph-up-arrow fs-4 me-2"></i>
                        <h6 class="mb-0 text-uppercase fw-bold opacity-75">Total Intérêts (Bénéfice)</h6>
                    </div>
                    <h3 class="fw-bold mb-1">{{ number_format($totalBenefice, 0, ',', ' ') }} FCFA</h3>
                    <small class="opacity-75">Générés par les prêts remboursés</small>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="stat-card bg-info">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-percent fs-4 me-2"></i>
                        <h6 class="mb-0 text-uppercase fw-bold opacity-75">Rendement Réel</h6>
                    </div>
                    <h3 class="fw-bold mb-1">
                        @if($totalAssiette > 0)
                            {{ number_format(($totalBenefice / $totalAssiette) * 100, 2, ',', ' ') }} %
                        @else
                            0 %
                        @endif
                    </h3>
                    <small class="opacity-75">Gain effectif pour les membres</small>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="stat-card bg-purple">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-percent fs-4 me-2"></i>
                        <h6 class="mb-0 text-uppercase fw-bold opacity-75">Taux Rendement Calculé</h6>
                    </div>
                    <h3 class="fw-bold mb-1">{{ number_format($tauxRendementReel * 100, 2, ',', ' ') }} %</h3>
                    <small class="opacity-75">Basé sur les intérêts générés</small>
                </div>
            </div>
        </div>

        <!-- Tableau de Répartition -->
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <h4 class="card-title mb-3 mb-md-0">
                        <i class="bi bi-table me-2"></i>Tableau de répartition
                    </h4>

                    <div class="d-flex gap-2 w-100 w-md-auto">
                        <!-- Barre de recherche -->
                        <div class="position-relative w-100 me-2">
                            <div class="position-absolute top-50 start-0 translate-middle-y ps-3">
                                <i class="bi bi-search text-muted"></i>
                            </div>
                            <input type="text" id="tableSearch" class="form-control ps-5" placeholder="Rechercher un membre...">
                        </div>

                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="bi bi-printer me-2"></i>Imprimer
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="distributionTable">
                        <thead class="table-light">
                        <tr>
                            <th scope="col" class="py-3 ps-4">
                                <i class="bi bi-person me-1"></i> Membre
                            </th>
                            <th scope="col" class="py-3 text-end">Montant Cotisé (F)</th>
                            <th scope="col" class="py-3 text-center">Part (%)</th>
                            <th scope="col" class="py-3 text-end text-success">Gain / Intérêts (F)</th>
                            <th scope="col" class="py-3 text-end pe-4 fw-bold">Total à Percevoir (F)</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($repartition as $ligne)
                            <tr class="search-row">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        @if(isset($ligne['user']->avatar))
                                            <img src="{{ asset($ligne['user']->avatar) }}"
                                                 alt="{{ $ligne['user']->name }}"
                                                 class="rounded-circle me-3"
                                                 width="40"
                                                 height="40">
                                        @else
                                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                                 style="width: 40px; height: 40px;">
                                                <i class="bi bi-person fs-5"></i>
                                            </div>
                                        @endif
                                        <span class="search-name fw-medium">{{ $ligne['user']->name }}</span>
                                    </div>
                                </td>
                                <td class="text-end">{{ number_format($ligne['apport'], 0, ',', ' ') }}</td>
                                <td class="text-center">
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            {{ number_format($ligne['pourcentage'], 2, ',', ' ') }} %
                                        </span>
                                </td>
                                <td class="text-end text-success fw-bold">
                                    <i class="bi bi-plus-circle me-1"></i>{{ number_format($ligne['gain'], 0, ',', ' ') }}
                                </td>
                                <td class="text-end pe-4 fw-bold bg-light">
                                    {{ number_format($ligne['total_a_percevoir'], 0, ',', ' ') }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot class="table-active">
                        <tr>
                            <td class="ps-4 fw-bold">TOTAL</td>
                            <td class="text-end fw-bold">{{ number_format($totalAssiette, 0, ',', ' ') }}</td>
                            <td class="text-center fw-bold">100 %</td>
                            <td class="text-end fw-bold text-success">
                                <i class="bi bi-plus-circle me-1"></i>{{ number_format($totalBenefice, 0, ',', ' ') }}
                            </td>
                            <td class="text-end pe-4 fw-bold bg-light">
                                {{ number_format($totalAssiette + $totalBenefice, 0, ',', ' ') }}
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Information sur le calcul -->
        <div class="alert alert-info mt-4 info-box">
            <div class="d-flex">
                <div class="me-3">
                    <i class="bi bi-info-circle-fill fs-4 text-info"></i>
                </div>
                <div>
                    <h5 class="alert-heading fw-bold mb-2">Information sur le calcul :</h5>
                    <ul class="mb-0 ps-3">
                        <li class="mb-2">Les intérêts proviennent des prêts remboursés ({{ number_format($totalBenefice, 0, ',', ' ') }} FCFA).</li>
                        <li class="mb-2">Le taux de rendement réel est calculé : <strong>{{ number_format($tauxRendementReel * 100, 2, ',', ' ') }}%</strong></li>
                        <li class="mb-2">
                            Formule : <strong>Total_déposé × x = Total_déposé + Total_intérêts</strong>
                        </li>
                        <li>Chaque membre reçoit : <strong>Son_dépôt × (1 + {{ number_format($tauxRendementReel * 100, 2, ',', ' ') }}%)</strong></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Résumé final -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card border-success">
                    <div class="card-body">
                        <h5 class="card-title text-success">
                            <i class="bi bi-check-circle me-2"></i>Résumé Final
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <tbody>
                                <tr>
                                    <td class="fw-bold">Total cotisations membres :</td>
                                    <td class="text-end fw-bold">{{ number_format($totalAssiette, 0, ',', ' ') }} FCFA</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Total intérêts générés :</td>
                                    <td class="text-end fw-bold text-success">+{{ number_format($totalBenefice, 0, ',', ' ') }} FCFA</td>
                                </tr>
                                <tr class="table-active">
                                    <td class="fw-bold">Montant total à distribuer :</td>
                                    <td class="text-end fw-bold text-primary">{{ number_format($totalAssiette + $totalBenefice, 0, ',', ' ') }} FCFA</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-primary">
                    <div class="card-body">
                        <h5 class="card-title text-primary">
                            <i class="bi bi-lightbulb me-2"></i>À savoir
                        </h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="bi bi-check text-success me-2"></i>
                                Cette répartition est provisoire jusqu'à la fin du cycle
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check text-success me-2"></i>
                                Les intérêts peuvent augmenter avec de nouveaux remboursements
                            </li>
                            <li>
                                <i class="bi bi-check text-success me-2"></i>
                                La distribution finale aura lieu à la clôture du cycle
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <!-- Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('tableSearch');
                const tableRows = document.querySelectorAll('#distributionTable tbody tr.search-row');

                if(searchInput) {
                    searchInput.addEventListener('keyup', function(e) {
                        const term = e.target.value.toLowerCase().trim();

                        tableRows.forEach(row => {
                            const nameEl = row.querySelector('.search-name');
                            if(nameEl) {
                                const name = nameEl.textContent.toLowerCase();
                                if(term === '' || name.includes(term)) {
                                    row.style.display = '';
                                } else {
                                    row.style.display = 'none';
                                }
                            }
                        });
                    });
                }

                // Ajout d'un style pour l'impression
                const style = document.createElement('style');
                style.textContent = `
                    @media print {
                        .btn, .form-control, .alert, .card:not(.border-success):not(.border-primary) {
                            display: none !important;
                        }
                        .container {
                            max-width: 100% !important;
                            padding: 0 !important;
                        }
                        .table-responsive {
                            overflow: visible !important;
                        }
                        .card {
                            border: none !important;
                            box-shadow: none !important;
                        }
                        .card-body {
                            padding: 0 !important;
                        }
                        .stat-card {
                            break-inside: avoid;
                        }
                    }
                `;
                document.head.appendChild(style);
            });
        </script>
    @endpush
</x-app-layout>
