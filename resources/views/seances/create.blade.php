<x-app-layout>
    <div class="container py-4">
        <h1>Planifier une Séance</h1>

        <div class="card mt-3" style="max-width: 600px;">
            <div class="card-body">
                <form action="{{ route('seances.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label>Cycle concerné</label>
                        <select name="cycle_id" class="form-select" required>
                            @foreach($cycles as $cycle)
                                <option value="{{ $cycle->id }}">{{ $cycle->nom }}</option>
                            @endforeach
                        </select>
                        @if($cycles->isEmpty())
                            <small class="text-danger">Attention : Aucun cycle actif trouvé.</small>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label>Date de la réunion</label>
                        <input type="date" name="date_seance" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label>Statut initial</label>
                        <select name="statut" class="form-select">
                            <option value="ouverte">Ouverte (Prête à saisir)</option>
                            <option value="fermee">Fermée (Planifiée pour plus tard)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">Enregistrer</button>
                    <a href="{{ route('seances.index') }}" class="btn btn-secondary">Annuler</a>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
