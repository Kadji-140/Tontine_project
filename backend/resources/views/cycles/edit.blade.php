<x-app-layout>
    <div class="container py-4">
        <h1>Modifier le Cycle : {{ $cycle->nom }}</h1>

        <div class="card mt-3" style="max-width: 600px;">
            <div class="card-body">
                <!-- Attention : Pour modifier, l'action est update et on ajoute l'ID du cycle -->
                <form action="{{ route('cycles.update', $cycle) }}" method="POST">
                    @csrf
                    @method('PUT') <!-- Indispensable pour dire à Laravel que c'est une modification -->

                    <div class="mb-3">
                        <label>Nom du cycle</label>
                        <input type="text" name="nom" class="form-control" value="{{ old('nom', $cycle->nom) }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Date Début</label>
                            <input type="date" name="date_debut" class="form-control" value="{{ old('date_debut', $cycle->date_debut->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Date Fin</label>
                            <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin', $cycle->date_fin->format('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Montant de la part (FCFA)</label>
                            <input type="number" name="montant_part" class="form-control" value="{{ old('montant_part', $cycle->montant_part) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Montant Mange-Mille (FCFA)</label>
                            <input type="number" name="montant_mange_mille" class="form-control" value="{{ old('montant_mange_mille', $cycle->montant_mange_mille) }}" min="0" step="100">
                            <small class="text-muted">Frais déduits automatiquement à chaque cotisation</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Taux d'intérêt Prêt (%)</label>
                            <input type="number" step="0.01" name="taux_interet" class="form-control" value="{{ old('taux_interet', $cycle->taux_interet) }}">
                            <small class="text-muted">Taux appliqué aux emprunteurs</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Fréquence des versements (Gains)</label>
                            <select name="frequence_paiement" class="form-control" required>
                                <option value="mensuelle" {{ $cycle->frequence_paiement === 'mensuelle' ? 'selected' : '' }}>Mensuelle</option>
                                <option value="hebdomadaire" {{ $cycle->frequence_paiement === 'hebdomadaire' ? 'selected' : '' }}>Hebdomadaire</option>
                            </select>
                        </div>
                    </div>

                    <!-- Checkbox pour activer/désactiver manuellement -->
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="est_actif" value="1" id="actifCheck" {{ $cycle->est_actif ? 'checked' : '' }}>
                        <label class="form-check-label" for="actifCheck">Définir comme Cycle Actif en cours</label>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-warning">Mettre à jour</button>

                        <!-- Bouton Supprimer (Danger Zone) -->
                        <button type="button" class="btn btn-outline-danger" onclick="document.getElementById('delete-form').submit();">
                            Supprimer ce cycle
                        </button>
                    </div>
                </form>

                <!-- Formulaire caché pour la suppression -->
                <form id="delete-form" action="{{ route('cycles.destroy', $cycle) }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>

            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('cycles.index') }}" class="btn btn-secondary">Retour à la liste</a>
        </div>
    </div>
</x-app-layout>
