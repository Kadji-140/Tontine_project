<x-app-layout>
    <div class="container py-4">
        <h1>Modifier la Séance</h1>

        <div class="card mt-3" style="max-width: 600px;">
            <div class="card-body">
                <form action="{{ route('seances.update', $seance) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label>Cycle concerné</label>
                        <select name="cycle_id" class="form-select" required>
                            @foreach($cycles as $cycle)
                                <option value="{{ $cycle->id }}" {{ $seance->cycle_id == $cycle->id ? 'selected' : '' }}>
                                    {{ $cycle->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Date de la réunion</label>
                        <input type="date" name="date_seance" class="form-control" value="{{ old('date_seance', $seance->date_seance->format('Y-m-d')) }}" required>
                    </div>

                    <div class="mb-3">
                        <label>Statut</label>
                        <select name="statut" class="form-select">
                            <option value="ouverte" {{ $seance->statut == 'ouverte' ? 'selected' : '' }}>Ouverte</option>
                            <option value="fermee" {{ $seance->statut == 'fermee' ? 'selected' : '' }}>Fermée</option>
                        </select>
                        <small class="text-muted">Attention : Fermer une séance empêche d'ajouter de nouvelles cotisations.</small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-warning">Mettre à jour</button>

                        <button type="button" class="btn btn-outline-danger" onclick="if(confirm('Êtes-vous sûr ?')){ document.getElementById('delete-seance').submit(); }">
                            Supprimer la séance
                        </button>
                    </div>
                </form>

                <form id="delete-seance" action="{{ route('seances.destroy', $seance) }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
        <div class="mt-3">
            <a href="{{ route('seances.index') }}" class="btn btn-secondary">Retour à la liste</a>
        </div>
    </div>
</x-app-layout>
