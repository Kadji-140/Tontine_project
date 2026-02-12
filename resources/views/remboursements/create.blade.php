<x-app-layout>
    <div class="container py-4">
        <h1>Enregistrer un Remboursement</h1>

        <div class="card mt-3 shadow-sm" style="max-width: 600px;">
            <div class="card-header bg-success text-white">
                Détails du Prêt de {{ $pret->user->name }}
            </div>
            <div class="card-body">
                <ul class="list-group mb-4">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Capital emprunté :</span>
                        <strong>{{ number_format($pret->montant_demande, 0, ',', ' ') }} FCFA</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Intérêts totaux :</span>
                        <strong>{{ number_format($pret->interet_total, 0, ',', ' ') }} FCFA</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between bg-light">
                        <span>Déjà remboursé :</span>
                        <span class="text-success">- {{ number_format($pret->montant_rembourse, 0, ',', ' ') }} FCFA</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between fw-bold fs-5">
                        <span>Reste à payer :</span>
                        <span class="text-danger">{{ number_format($pret->reste_a_payer, 0, ',', ' ') }} FCFA</span>
                    </li>
                </ul>

                <form action="{{ route('remboursements.store', $pret) }}" method="POST">
                    @csrf
                    <input type="hidden" name="seance_id" value="{{ $seance->id }}">

                    <div class="mb-3">
                        <label class="form-label">Séance d'encaissement</label>
                        <input type="text" class="form-control" value="Séance du {{ $seance->date_seance->format('d/m/Y') }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Montant Versé (FCFA)</label>
                        <input type="number" name="montant" class="form-control form-control-lg fw-bold"
                               value="{{ $pret->reste_a_payer }}" max="{{ $pret->reste_a_payer }}" required>
                        <small class="text-muted">Vous pouvez modifier ce montant pour un remboursement partiel.</small>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">Valider le Remboursement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
