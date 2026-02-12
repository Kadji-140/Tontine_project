<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold text-primary mb-0">
            <i class="bi bi-calendar-week me-2"></i>{{ __('Liste des Séances') }}
        </h2>
    </x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
        <style>
            .status-badge {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
                font-weight: 500;
            }
            .table-responsive {
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 0 15px rgba(0,0,0,0.08);
            }
            .action-buttons .btn {
                margin-right: 0.3rem;
                margin-bottom: 0.3rem;
            }
            .table > :not(caption) > * > * {
                vertical-align: middle;
            }
        </style>
    @endpush

    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">
                    <i class="bi bi-calendar-event me-2"></i>Liste des Séances
                </h1>
                <p class="text-muted mb-0">Gérez les séances de tontine</p>
            </div>
            <a href="{{ route('seances.create') }}" class="btn btn-primary mt-3 mt-md-0">
                <i class="bi bi-plus-circle me-2"></i>Nouvelle Séance
            </a>
        </div>

        <!-- Messages d'alerte -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Table des séances -->
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead class="table-dark">
                <tr>
                    <th scope="col" class="py-3">
                        <i class="bi bi-calendar me-1"></i> Date
                    </th>
                    <th scope="col" class="py-3">
                        <i class="bi bi-arrow-repeat me-1"></i> Cycle
                    </th>
                    <th scope="col" class="py-3">
                        <i class="bi bi-info-circle me-1"></i> Statut
                    </th>
                    <th scope="col" class="py-3">
                        <i class="bi bi-cash-stack me-1"></i> Total Encaissé
                    </th>
                    <th scope="col" class="py-3 text-center">
                        <i class="bi bi-gear me-1"></i> Actions
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($seances as $seance)
                    <tr>
                        <td class="fw-bold">
                            <i class="bi bi-calendar-date text-primary me-2"></i>
                            {{ $seance->date_seance->format('d/m/Y') }}
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary me-2">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </span>
                                <span class="fw-medium">{{ $seance->cycle->nom }}</span>
                            </div>
                        </td>
                        <td>
                            @if(now()->startOfDay()->gt($seance->date_seance))
                                <span class="badge bg-secondary status-badge">
                                        <i class="bi bi-lock me-1"></i> Fermée
                                </span>
                            @else
                                <span class="badge bg-success status-badge">
                                        <i class="bi bi-unlock me-1"></i> Ouverte
                                    </span>

                            @endif
                        </td>
                        <td class="fw-bold text-primary">
                            <i class="bi bi-currency-exchange me-2"></i>
                            {{ number_format($seance->total_encaisse, 0, ',', ' ') }} FCFA
                        </td>
                        <td>
                            <div class="d-flex action-buttons">
                                <a href="{{ route('seances.show', $seance) }}"
                                   class="btn btn-primary btn-sm"
                                   title="Gérer cette séance">
                                    <i class="bi bi-gear me-1"></i> Gérer
                                </a>
                                <a href="{{ route('seances.edit', $seance) }}"
                                   class="btn btn-warning btn-sm"
                                   title="Modifier cette séance">
                                    <i class="bi bi-pencil me-1"></i> Modifier
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($seances instanceof \Illuminate\Pagination\LengthAwarePaginator && $seances->hasPages())
            <div class="d-flex justify-content-center mt-4">
                <nav aria-label="Navigation des séances">
                    {{ $seances->links('pagination::bootstrap-5') }}
                </nav>
            </div>
        @endif

        <!-- Message si pas de séances -->
        @if($seances->isEmpty())
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                </div>
                <h4 class="text-muted mb-3">Aucune séance enregistrée</h4>
                <p class="text-muted mb-4">Commencez par créer votre première séance</p>
                <a href="{{ route('seances.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Créer une séance
                </a>
            </div>
        @endif
    </div>

    @push('scripts')
        <!-- Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Scripts spécifiques à cette page
            document.addEventListener('DOMContentLoaded', function() {
                // Confirmation pour les actions importantes
                const deleteButtons = document.querySelectorAll('.btn-delete');
                deleteButtons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        if (!confirm('Êtes-vous sûr de vouloir supprimer cette séance ?')) {
                            e.preventDefault();
                        }
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
