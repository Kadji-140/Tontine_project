<x-app-layout>
    <div class="container py-4">
        <h1>Créer un nouveau Cycle</h1>

        <div class="card mt-3" style="max-width: 600px;">
            <div class="card-body">
                <form action="{{ route('cycles.store') }}" method="POST">
                    @csrf <!-- Protection obligatoire Laravel -->

                    <div class="mb-3">
                        <label>Nom du cycle</label>
                        <input type="text" name="nom" class="form-control" placeholder="Ex: Exercice 2026" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Date Début</label>
                            <input type="date" name="date_debut" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Date Fin</label>
                            <input type="date" name="date_fin" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Montant de la part (FCFA)</label>
                            <input type="number" name="montant_part" class="form-control" value="10000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Montant Mange-Mille (FCFA)</label>
                            <input type="number" name="montant_mange_mille" class="form-control" value="1000" min="0" step="100">
                            <small class="text-muted">Frais déduits automatiquement à chaque cotisation</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Taux d'intérêt Prêt (%)</label>
                            <input type="number" step="0.01" name="taux_interet" class="form-control" value="10">
                            <small class="text-muted">Taux appliqué aux emprunteurs</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Fréquence des versements (Gains)</label>
                            <select name="frequence_paiement" class="form-control" required>
                                <option value="mensuelle">Mensuelle (Par défaut)</option>
                                <option value="hebdomadaire">Hebdomadaire</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success">Enregistrer le Cycle</button>
                    <a href="{{ route('cycles.index') }}" class="btn btn-secondary">Annuler</a>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
