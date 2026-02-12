<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold text-primary mb-0">
            <i class="bi bi-sort-numeric-down me-2"></i>{{ __('Ordre de Passage') }} : {{ $cycle->nom }}
        </h2>
        @if(Auth::user()->role === 'admin' || Auth::user()->role === 'tresorier')
            <a href="{{ route('cycles.membres', $cycle) }}" class="btn btn-outline-primary btn-sm ms-3">
                <i class="bi bi-pencil-square me-1"></i> Gérer / Modifier
            </a>
        @endif
    </x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
        <style>
            .timeline-card {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                border-left: 5px solid transparent;
            }
            .timeline-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
            }
            .timeline-card.active-turn {
                border-left-color: #198754; /* Success color */
                background-color: #f8fff9;
            }
            .timeline-card.past-turn {
                border-left-color: #0d6efd; /* Primary color */
                background-color: #f8f9fa;
                opacity: 0.8;
            }
            .timeline-card.future-turn {
                border-left-color: #ffc107; /* Warning color */
            }
            
            .rank-circle {
                width: 50px;
                height: 50px;
                line-height: 50px;
                border-radius: 50%;
                text-align: center;
                font-weight: 800;
                font-size: 1.2rem;
                color: white;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            
            .rank-past { background: linear-gradient(135deg, #0d6efd, #0a58ca); }
            .rank-active { background: linear-gradient(135deg, #198754, #146c43); transform: scale(1.1); }
            .rank-future { background: linear-gradient(135deg, #ffc107, #ffca2c); color: #333; }
            
            .date-badge {
                font-size: 0.85rem;
                padding: 5px 10px;
                border-radius: 20px;
                background-color: #e9ecef;
                color: #495057;
                font-weight: 600;
            }
            .date-badge.active {
                background-color: #d1e7dd;
                color: #0f5132;
            }
        </style>
    @endpush

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <div class="text-center mb-5">
                    <h3 class="fw-bold text-dark mb-2">Planning des Réceptions</h3>
                    <p class="text-muted">Suivez l'ordre de passage pour le cycle en cours.</p>
                </div>

                <div class="d-flex flex-column gap-3">
                    @php
                        $startDate = $cycle->date_debut;
                        $freq = $cycle->frequence_paiement ?? 'mensuelle';
                        $now = now();
                    @endphp

                    @foreach($membres as $membre)
                        @php
                            $rank = $membre->pivot->rang;
                            $interval = $freq === 'hebdomadaire' ? ($rank - 1) . ' weeks' : ($rank - 1) . ' months';
                            $estimatedDate = $startDate->copy()->add($interval);
                            
                            $dejaPaye = \App\Models\PaiementLot::where('cycle_id', $cycle->id)
                                ->where('user_id', $membre->id)
                                ->where('statut', 'confirme')
                                ->exists();

                            // Determine status
                            $statusClass = '';
                            $badgeClass = '';
                            if ($dejaPaye) {
                                $statusClass = 'past-turn';
                                $badgeClass = 'rank-past';
                            } elseif ($estimatedDate->isPast() && !$dejaPaye) {
                                // C'est probablement le tour actuel si non payé mais date passée ou proche
                                $statusClass = 'active-turn';
                                $badgeClass = 'rank-active';
                            } else {
                                $statusClass = 'future-turn';
                                $badgeClass = 'rank-future';
                            }
                        @endphp

                        <div class="card shadow-sm border-0 timeline-card {{ $statusClass }} p-3">
                            <div class="d-flex align-items-center">
                                <!-- Rang -->
                                <div class="me-4">
                                    <div class="rank-circle {{ $badgeClass }}">
                                        {{ $rank }}
                                    </div>
                                </div>

                                <!-- Infos Membre -->
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                                        <div>
                                            <h5 class="mb-1 fw-bold {{ $membre->id === Auth::id() ? 'text-primary' : 'text-dark' }}">
                                                {{ $membre->name }}
                                                @if($membre->id === Auth::id())
                                                    <span class="badge bg-primary ms-2" style="font-size: 0.7rem;">VOUS</span>
                                                @endif
                                            </h5>
                                            <p class="mb-0 text-muted small">
                                                <i class="bi bi-envelope me-1"></i> {{ $membre->email }}
                                            </p>
                                        </div>
                                        
                                        <div class="text-end mt-2 mt-md-0">
                                            @if($dejaPaye)
                                                <div class="badge bg-success mb-1">
                                                    <i class="bi bi-check-lg me-1"></i> Reçu
                                                </div>
                                                <div class="small text-muted">A déjà bénéficié</div>
                                            @else
                                                <div class="date-badge mb-1 {{ $statusClass === 'active-turn' ? 'active' : '' }}">
                                                    <i class="bi bi-calendar-event me-1"></i>
                                                    {{ $estimatedDate->translatedFormat('d M Y') }}
                                                </div>
                                                <div class="small text-muted">
                                                    {{ $estimatedDate->diffForHumans() }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 text-center">
                    <div class="d-inline-flex gap-4">
                        <div class="d-flex align-items-center">
                            <span class="d-inline-block rounded-circle me-2" style="width: 12px; height: 12px; background: #0d6efd;"></span>
                            <span class="small text-muted">Passé</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="d-inline-block rounded-circle me-2" style="width: 12px; height: 12px; background: #198754;"></span>
                            <span class="small text-muted">En cours</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="d-inline-block rounded-circle me-2" style="width: 12px; height: 12px; background: #ffc107;"></span>
                            <span class="small text-muted">Futur</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
