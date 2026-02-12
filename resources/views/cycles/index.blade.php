<x-app-layout>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Gestion des Cycles</h1>
            <a href="{{ route('cycles.create') }}" class="btn btn-primary">Nouveau Cycle</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm" style="overflow-x: auto">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Période</th>
                        <th>Part (FCFA)</th>
                        <th>Intérêt</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($cycles as $cycle)
                        <tr>
                            <td>{{ $cycle->nom }}</td>
                            <td>{{ $cycle->date_debut->format('d/m/Y') }} - {{ $cycle->date_fin->format('d/m/Y') }}</td>
                            <td>{{ number_format($cycle->montant_part, 0, ',', ' ') }}</td>
                            <td>{{ $cycle->taux_interet }}%</td>
                            <td>
                                @if($cycle->est_actif)
                                    <span class="badge bg-success">En cours</span>
                                @else
                                    <span class="badge bg-secondary">Clôturé</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('cycles.edit', $cycle) }}" class="btn btn-sm btn-warning">Modifier</a>
                                <a href="{{ route('cycles.membres', $cycle) }}" class="btn btn-sm btn-primary" title="Gérer l'ordre des membres">
                                    <i class="fas fa-users"></i> Membres
                                </a>
                                <a href="{{ route('cycles.distribution', $cycle) }}" class="btn btn-sm btn-info text-white" title="Voir la répartition des gains">
                                    <i class="bi bi-bank"></i> Banque
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
