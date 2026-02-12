<x-app-layout>
    <div class="container py-4">
        <h1>Infliger une Sanction</h1>

        <div class="card mt-3" style="max-width: 600px;">
            <div class="card-body">
                <form action="{{ route('sanctions.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label>Membre concerné</label>
                        <select name="user_id" class="form-select" required>
                            @foreach($membres as $membre)
                                <option value="{{ $membre->id }}">{{ $membre->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Motif de la sanction</label>
                        <input type="text" name="motif" class="form-control" placeholder="Ex: Retard, Absence, Non respect du règlement..." required>
                        <small class="text-muted">Soyez précis pour éviter les litiges.</small>
                    </div>

                    <div class="mb-3">
                        <label>Montant de l'amende (FCFA)</label>
                        <input type="number" name="montant" class="form-control" min="100" step="100" value="500" required>
                    </div>

                    <button type="submit" class="btn btn-danger">Enregistrer la sanction</button>
                    <a href="{{ route('sanctions.index') }}" class="btn btn-secondary">Annuler</a>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
