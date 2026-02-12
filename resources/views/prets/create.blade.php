<x-app-layout>
    <div class="container py-4">
        <h1>Demander un Prêt</h1>

        <div class="card mt-3" style="max-width: 600px;">
            <div class="card-body">
                <form action="{{ route('prets.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="seance_id" value="{{ $seance->id }}">
                    <input type="hidden" id="taux_interet" value="{{ $seance->cycle->taux_interet }}">
                    <input type="hidden" id="date_fin_cycle" value="{{ $seance->cycle->date_fin }}">
                    <input type="hidden" id="aujourdhui" value="{{ now()->format('Y-m-d') }}">

                    <!-- CONDITION D'AFFICHAGE -->
                    @if(Auth::user()->role === 'membre')
                        @php
                            $accountAge = \Carbon\Carbon::parse(Auth::user()->created_at)->diffInMonths(now());
                            $isEligible = $accountAge >= 3;
                        @endphp
                        
                        @if(!$isEligible)
                            <div class="alert alert-warning border-warning">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-exclamation-triangle text-warning me-3 mt-1" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <h6 class="alert-heading mb-2">❌ Non éligible pour un prêt</h6>
                                        <p class="mb-1">Vous devez avoir au moins <strong>3 mois d'ancienneté</strong> pour demander un prêt.</p>
                                        <hr class="my-2">
                                        <p class="mb-1 small">
                                            <i class="fas fa-calendar-alt me-1"></i> Ancienneté actuelle : <strong>{{ $accountAge }} mois</strong><br>
                                            <i class="fas fa-clock me-1"></i> Éligible dans : <strong>{{ 3 - $accountAge }} mois</strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                    @else
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        <div class="mb-3">
                            <label>Membre Demandeur (Mode Trésorier)</label>
                            <select name="user_id" class="form-select" id="user_select" required>
                                <option value="">-- Choisir --</option>
                                @foreach($membres as $membre)
                                    @php
                                        $membreAge = \Carbon\Carbon::parse($membre->created_at)->diffInMonths(now());
                                        $membreEligible = $membreAge >= 3;
                                    @endphp
                                    <option value="{{ $membre->id }}" {{ !$membreEligible ? 'disabled' : '' }}>
                                        {{ $membre->name }} 
                                        @if(!$membreEligible)
                                            (❌ Non éligible - {{ $membreAge }} mois)
                                        @else
                                            (✅ {{ $membreAge }} mois)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Seuls les membres avec 3+ mois d'ancienneté peuvent emprunter</small>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label>Montant Demandé (FCFA)</label>
                        <input type="number" name="montant_demande" id="montant" class="form-control" min="1000" step="500" required>
                    </div>

                    <!-- Affichage du calcul -->
                    <div class="alert alert-info">
                        <p>Intérêt ({{ $seance->cycle->taux_interet }}%) : <strong id="txt_interet">0</strong> FCFA</p>
                        <p>Total à rembourser : <strong id="txt_total">0</strong> FCFA</p>
                    </div>

                    <div class="mb-3">
                        <label>Date Limite de Remboursement</label>
                        <input type="date" name="date_echeance" id="date_echeance" class="form-control" required>
                        <small class="text-muted">
                            Maximum: {{ date('d/m/Y', strtotime('+3 months')) }} (3 mois) ou fin du cycle {{ \Carbon\Carbon::parse($seance->cycle->date_fin)->format('d/m/Y') }}
                        </small>
                        <div id="date_error" class="text-danger mt-1" style="display: none;"></div>
                    </div>


                    <button type="submit" id="submit_btn" class="btn btn-primary w-100" {{ (Auth::user()->role === 'membre' && !$isEligible) ? 'disabled' : '' }}>
                        @if(Auth::user()->role === 'membre' && !$isEligible)
                            <i class="fas fa-lock me-2"></i>Non éligible (3 mois requis)
                        @else
                            Envoyer la demande
                        @endif
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const montantInput = document.getElementById('montant');
        const taux = parseFloat(document.getElementById('taux_interet').value);
        const dateEcheanceInput = document.getElementById('date_echeance');
        const dateFinCycle = document.getElementById('date_fin_cycle').value;
        const aujourdhui = document.getElementById('aujourdhui').value;
        const submitBtn = document.getElementById('submit_btn');
        const dateError = document.getElementById('date_error');

        // Calculer la date maximum autorisée (3 mois OU fin de cycle)
        const dateActuelle = new Date(aujourdhui);
        const date3Mois = new Date(dateActuelle);
        date3Mois.setMonth(date3Mois.getMonth() + 3);

        const dateFinCycleObj = new Date(dateFinCycle);
        const dateMaximum = date3Mois < dateFinCycleObj ? date3Mois : dateFinCycleObj;

        // Définir min et max pour l'input date
        dateEcheanceInput.min = aujourdhui;
        dateEcheanceInput.max = dateMaximum.toISOString().split('T')[0];

        // Calcul des intérêts
        montantInput.addEventListener('input', function() {
            let montant = parseFloat(this.value) || 0;
            let interet = montant * (taux / 100);
            let total = montant + interet;

            document.getElementById('txt_interet').innerText = interet.toLocaleString('fr-FR');
            document.getElementById('txt_total').innerText = total.toLocaleString('fr-FR');
        });

        // Validation en temps réel de la date
        dateEcheanceInput.addEventListener('change', function() {
            const dateChoisie = new Date(this.value);
            const dateLimiteMsg = dateMaximum.toLocaleDateString('fr-FR');

            if (dateChoisie < dateActuelle) {
                dateError.textContent = "❌ La date ne peut pas être antérieure à aujourd'hui.";
                dateError.style.display = 'block';
                submitBtn.disabled = true;
            } else if (dateChoisie > dateMaximum) {
                dateError.textContent = `❌ La date ne peut pas dépasser ${dateLimiteMsg} (3 mois maximum et avant fin du cycle).`;
                dateError.style.display = 'block';
                submitBtn.disabled = true;
            } else {
                dateError.style.display = 'none';
                submitBtn.disabled = false;
            }
        });

        // Validation à la soumission
        document.querySelector('form').addEventListener('submit', function(e) {
            const dateChoisie = new Date(dateEcheanceInput.value);

            if (dateChoisie < dateActuelle) {
                e.preventDefault();
                alert("Erreur : La date d'échéance ne peut pas être antérieure à aujourd'hui.");
                return false;
            }

            if (dateChoisie > dateMaximum) {
                e.preventDefault();
                alert(`Erreur : La date d'échéance ne peut pas dépasser ${dateMaximum.toLocaleDateString('fr-FR')} (3 mois maximum et avant fin du cycle).`);
                return false;
            }
        });
    </script>
</x-app-layout>
