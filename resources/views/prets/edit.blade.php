<x-app-layout> {{-- Ou votre layout --}}


    <div class="container py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-warning">
                <h4 class="mb-0">Modifier le prêt de {{ $pret->user->name }}</h4>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                    <form action="{{ route('prets.update', $pret) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="montant_demande" class="form-label">Montant demandé</label>
                            <input type="number" class="form-control" id="montant_demande"
                                   name="montant_demande" value="{{ $pret->montant_demande }}"
                                   min="1000" required readonly
                                   style="cursor: not-allowed; background-color: #f8f9fa;">
                            <input type="hidden" name="montant_demande" value="{{ $pret->montant_demande }}">
                            <small class="text-muted text-danger">
                                <i class="bi bi-exclamation-triangle"></i> Le montant ne peut pas être modifié
                            </small>
                        </div>

                        <div class="mb-3">
                            <label>Date Limite de Remboursement</label>
                            <input type="date" name="date_echeance" id="date_echeance"
                                   class="form-control @error('date_echeance') is-invalid @enderror"
                                   value="{{ old('date_echeance', $pret->date_echeance->format('Y-m-d')) }}"
                                   min="{{ now()->format('Y-m-d') }}"
                                   max="{{ min(now()->addMonths(3)->format('Y-m-d'), \Carbon\Carbon::parse($pret->seance->cycle->date_fin)->format('Y-m-d')) }}"
                                   required>
                            @error('date_echeance')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Maximum: {{ date('d/m/Y', strtotime('+3 months')) }} ou fin du cycle {{ \Carbon\Carbon::parse($pret->seance->cycle->date_fin)->format('d/m/Y') }}
                            </small>
                        </div>

                        <div class="mb-3">
                            <div class="alert alert-info">
                                <strong>Information :</strong><br>
                                • Date actuelle : <strong>{{ $pret->date_echeance->format('d/m/Y') }}</strong><br>
                                • Montant actuel : <strong>{{ number_format($pret->montant_demande, 0, ',', ' ') }} F</strong>
                                @if($pret->date_echeance_modifiee && !$pret->est_accepte_par_membre)
                                    <br>• <span class="text-warning">⚠️ Une modification est en attente de confirmation</span>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('prets.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>




            </div>
        </div>
    </div>


    <script>
        // Empêcher toute modification du montant
        document.getElementById('montant_demande').addEventListener('input', function(e) {
            e.preventDefault();
            alert('Le montant ne peut pas être modifié');
            this.value = {{ $pret->montant_demande }};
        });

        document.getElementById('montant_demande').addEventListener('keydown', function(e) {
            if (e.key !== 'Tab' && e.key !== 'Enter') {
                e.preventDefault();
            }
        });
    </script>
    <script>
        // Validation JS supplémentaire
        document.getElementById('date_echeance').addEventListener('change', function() {
            const dateChoisie = new Date(this.value);
            const aujourdhui = new Date();
            aujourdhui.setHours(0,0,0,0);

            if (dateChoisie < aujourdhui) {
                alert('La nouvelle date ne peut pas être antérieure à aujourd\'hui.');
                this.value = '{{ $pret->date_echeance->format("Y-m-d") }}';
            }
        });

        // Empêcher la modification du montant via JS aussi
        document.getElementById('montant_demande').addEventListener('input', function(e) {
            this.value = {{ $pret->montant_demande }};
            alert('Le montant ne peut pas être modifié');
        });

        document.getElementById('date_echeance').addEventListener('change', function() {
            const dateChoisie = new Date(this.value);
            const aujourdhui = new Date();
            aujourdhui.setHours(0,0,0,0);

            if (dateChoisie < aujourdhui) {
                alert('La nouvelle date ne peut pas être antérieure à aujourd\'hui.');
                this.value = '';
            }
        });
    </script>

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateEcheanceInput = document.getElementById('date_echeance');
            const dateInfoSpan = document.getElementById('date-info');
            const ancienneDate = new Date('{{ $pret->date_echeance->format('Y-m-d') }}');

            if (dateEcheanceInput) {
                // Définir les limites
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                // Date minimum : aujourd'hui
                const minDate = today.toISOString().split('T')[0];
                dateEcheanceInput.min = minDate;

                // Écouter les changements
                dateEcheanceInput.addEventListener('change', function() {
                    const selectedDate = new Date(this.value);
                    selectedDate.setHours(0, 0, 0, 0);

                    // Comparer avec l'ancienne date
                    if (selectedDate < ancienneDate) {
                        // Montrer un message d'erreur
                        dateInfoSpan.innerHTML = `<span class="text-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    La date ne peut pas être antérieure au ${ancienneDate.toLocaleDateString('fr-FR')}
                </span>`;

                        // Réinitialiser à l'ancienne date
                        this.value = ancienneDate.toISOString().split('T')[0];

                        // Empêcher la soumission
                        this.setCustomValidity('Date antérieure non autorisée');
                    } else {
                        // C'est bon, on valide
                        dateInfoSpan.innerHTML = `<span class="text-success">
                    <i class="bi bi-check-circle"></i>
                    Date valide
                </span>`;
                        this.setCustomValidity('');

                        // Vérifier aussi la limite des 3 mois
                        const threeMonthsFromNow = new Date();
                        threeMonthsFromNow.setMonth(threeMonthsFromNow.getMonth() + 3);

                        if (selectedDate > threeMonthsFromNow) {
                            dateInfoSpan.innerHTML += `<br><span class="text-warning">
                        <i class="bi bi-info-circle"></i>
                        La date dépasse 3 mois. Le membre devra confirmer.
                    </span>`;
                        }
                    }
                });

                // Validation initiale
                if (dateEcheanceInput.value) {
                    dateEcheanceInput.dispatchEvent(new Event('change'));
                }
            }

            // Empêcher la soumission si date invalide
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const selectedDate = new Date(dateEcheanceInput.value);
                selectedDate.setHours(0, 0, 0, 0);

                if (selectedDate < ancienneDate) {
                    e.preventDefault();
                    alert('Erreur : Vous ne pouvez pas sélectionner une date antérieure à la date actuelle.');
                    return false;
                }

                if (selectedDate < today) {
                    e.preventDefault();
                    alert('Erreur : La date d\'échéance ne peut pas être dans le passé.');
                    return false;
                }

                return true;
            });
        });
    </script>
@endsection

</x-app-layout>
