<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold text-primary mb-0">
            <i class="fas fa-list-ol me-2"></i>{{ __('Ordre de Passage') }} : {{ $cycle->nom }}
        </h2>
    </x-slot>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">
                    <i class="fas fa-money-bill-wave me-2"></i>Calendrier des Paiements
                </h1>
                <p class="text-muted mb-0">
                    Cycle : <strong>{{ $cycle->nom }}</strong> | Part : <strong>{{ number_format($cycle->montant_part, 0, ',', ' ') }} FCFA</strong>
                </p>
            </div>
            <a href="{{ route('cycles.membres', $cycle) }}" class="btn btn-outline-secondary">
                <i class="fas fa-users me-2"></i>Gérer les membres
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Principe :</strong> L'ordre est déterminé par le rang des membres. 
                    Lorsqu'un membre reçoit sa part, il passe automatiquement à la fin de la liste d'attente.
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="80">#</th>
                                <th>Date Estimée</th>
                                <th>Bénéficiaire</th>
                                <th>Montant Estimé</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($queue as $index => $item)
                                <tr class="{{ $item->is_current_user ? 'table-info border-start border-4 border-info' : '' }}">
                                    <td class="text-center fw-bold">
                                        <span class="badge bg-secondary rounded-circle">{{ $item->rang }}</span>
                                    </td>
                                    <td>
                                        @if($item->date_prevue)
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary bg-opacity-10 p-2 rounded me-2 text-primary">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $item->date_prevue->format('d/m/Y') }}</div>
                                                    <small class="text-muted">{{ $item->date_prevue->diffForHumans() }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="badge bg-warning text-dark">Date à définir</span>
                                            <small class="d-block text-muted">En attente de séance</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->is_current_user)
                                                <div class="me-2">
                                                    <i class="fas fa-user-circle fs-4 text-info"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-info">Vous ({{ $item->membre->name }})</div>
                                                </div>
                                            @else
                                                <div class="me-2">
                                                    <i class="fas fa-user-circle fs-4 text-secondary"></i>
                                                </div>
                                                <div class="fw-bold">{{ $item->membre->name }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">
                                            {{ number_format($item->montant_estime, 0, ',', ' ') }} FCFA
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if($index === 0 && (Auth::user()->role === 'admin' || Auth::user()->role === 'tresorier'))
                                            <form action="{{ route('payment-queue.pay', [$cycle, $item->membre->id]) }}" method="POST" onsubmit="return confirm('Confirmer que {{ $item->membre->name }} a reçu sa part ? \n\nCela déplacera le membre à la fin de la liste.');">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check-double me-1"></i>A reçu sa part
                                                </button>
                                            </form>
                                        @elseif($index === 0)
                                            <span class="badge bg-warning text-dark">Prochain</span>
                                        @else
                                            <span class="text-muted small">En attente</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="mb-3">
                                            <i class="fas fa-users-slash text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="text-muted">Aucun membre dans la liste</h5>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light">
                <small class="text-muted">
                    <i class="fas fa-sync-alt me-1"></i>
                    Les dates sont calculées automatiquement en fonction des séances disponibles et de l'ordre des membres.
                </small>
            </div>
        </div>
    </div>
</x-app-layout>
