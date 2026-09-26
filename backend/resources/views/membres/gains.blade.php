<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold text-primary mb-0">
            <i class="bi bi-gift me-2"></i>{{ __('Mes Gains de Cotisations') }}
        </h2>
    </x-slot>

    <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Historique des lots reçus</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Cycle</th>
                                <th>Date du Versement</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($gains as $gain)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $gain->cycle->nom }}</div>
                                        <div class="small text-muted">Séance du {{ $gain->seance->date_seance->format('d/m/Y') }}</div>
                                    </td>
                                    <td>{{ $gain->date_paiement->format('d/m/Y') }}</td>
                                    <td class="fw-bold text-success">{{ number_format($gain->montant) }} FCFA</td>
                                    <td>
                                        @if($gain->statut === 'confirme')
                                            <span class="badge bg-success">Confirmé</span>
                                        @elseif($gain->statut === 'rejete')
                                            <span class="badge bg-danger">Rejeté</span>
                                        @else
                                            <span class="badge bg-warning text-dark">En attente de confirmation</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($gain->statut === 'en_attente')
                                            <form action="{{ route('gains.confirmer', $gain) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    Confirmer la réception
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">Aucune action requise</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
                                            Vous n'avez pas encore reçu de gain de tontine pour les cycles en cours.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
