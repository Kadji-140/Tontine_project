<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport de Séance - {{ $seance->date_seance->format('d/m/Y') }}</title>
    <style>
        @page { margin: 1cm; margin-top: 0.5cm; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 11px; 
            color: #1e293b; 
            line-height: 1.6; 
            margin: 0; 
            padding: 0;
            background-color: #ffffff;
        }
        
        /* Header */
        .header { 
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); 
            color: white; 
            padding: 20px; 
            border-radius: 12px; 
            margin-bottom: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 3px; font-weight: 800; }
        .header p { margin: 5px 0 0; opacity: 0.9; font-size: 12px; font-weight: 500; }
        
        /* ... (Rest of styles unchanged) ... */
        .info-grid { width: 100%; margin-bottom: 35px; border-collapse: collapse; }
        .info-card { background-color: #f8fafc; padding: 18px; border-radius: 10px; border: 1px solid #e2e8f0; }
        .info-label { color: #64748b; font-weight: bold; font-size: 10px; text-transform: uppercase; margin-bottom: 5px; display: block; letter-spacing: 1px; }
        .info-value { color: #0f172a; font-size: 14px; font-weight: 700; }
        
        h3 { border-left: 5px solid #3b82f6; padding-left: 12px; color: #0f172a; margin-top: 25px; margin-bottom: 15px; font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
        table.data { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 20px; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; }
        table.data th { background-color: #f1f5f9; color: #475569; font-weight: 800; text-align: left; padding: 10px 12px; font-size: 10px; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        table.data td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; background-color: #ffffff; }
        table.data tr:last-child td { border-bottom: none; }
        
        .amount { text-align: right; font-weight: 800; color: #1e293b; font-size: 12px; }
        .badge { padding: 4px 8px; border-radius: 20px; font-size: 9px; font-weight: 800; text-transform: uppercase; display: inline-block; }
        /* ... */
    </style>
</head>
<body>

<div class="header">
    <h1>TontineCam</h1>
    <p>Rapport Financier Officiel - Séance du {{ $seance->date_seance->format('d/m/Y') }}</p>
    <p style="font-size: 10px; opacity: 0.8; margin-top: 5px;">Généré automatiquement par le système de gestion.</p>
</div>

<table style="width: 100%; border-collapse: separate; border-spacing: 15px 0;">
    <tr>
        <td width="33%">
            <div class="info-card">
                <span class="info-label">Cycle de Tontine</span>
                <span class="info-value">{{ $seance->cycle->nom }}</span>
            </div>
        </td>
        <td width="33%">
            <div class="info-card">
                <span class="info-label">Status Séance</span>
                <span class="info-value" style="color: {{ $seance->statut === 'ouverte' ? '#10b981' : '#64748b' }}">
                    {{ ucfirst($seance->statut) }}
                </span>
            </div>
        </td>
        <td width="33%">
            <div class="info-card">
                <span class="info-label">Encaissement Total</span>
                <span class="info-value" style="color: #1e3a8a;">{{ number_format($seance->total_encaisse, 0, ',', ' ') }} FCFA</span>
            </div>
        </td>
    </tr>
</table>

@php $sectionIndex = 1; @endphp

<!-- 1. COTISATIONS -->
<h3>{{ $sectionIndex++ }}. DÉTAIL DES COTISATIONS</h3>
<table class="data">
    <thead>
    <tr>
        <th>Membre</th>
        <th>Type</th>
        <th>Enregistré Par</th>
        <th class="amount">Montant (FCFA)</th>
    </tr>
    </thead>
    <tbody>
    @foreach($seance->cotisations as $cotisation)
        <tr>
            <td style="font-weight: bold;">{{ $cotisation->user->name }}</td>
            <td>
                {{ ucfirst($cotisation->type) }}
            </td>
            <td style="color: #64748b; font-size: 10px;">
                {{ $cotisation->auteur ? $cotisation->auteur->name : '-' }}
            </td>
            <td class="amount">{{ number_format($cotisation->montant, 0, ',', ' ') }}</td>
        </tr>
    @endforeach
    @if($seance->cotisations->isEmpty())
        <tr><td colspan="4" style="text-align: center; padding: 30px; color: #94a3b8;">Aucune cotisation enregistrée.</td></tr>
    @endif
    </tbody>
</table>

<!-- 2. REMBOURSEMENTS DE PRÊTS (Entrées) -->
@if(!$seance->remboursements->isEmpty())
    <h3>{{ $sectionIndex++ }}. REMBOURSEMENTS DE PRÊTS</h3>
    <table class="data">
        <thead>
        <tr>
            <th>Membre</th>
            <th>Prêt Concerné</th>
            <th class="amount">Montant Remboursé</th>
        </tr>
        </thead>
        <tbody>
        @foreach($seance->remboursements as $remboursement)
            <tr>
                <td style="font-weight: bold;">{{ $remboursement->user->name }}</td>
                <td style="color: #64748b;">Prêt #{{ $remboursement->pret_id }}</td>
                <td class="amount" style="color: #059669;">+ {{ number_format($remboursement->montant, 0, ',', ' ') }} FCFA</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

<!-- 3. FONDS SPÉCIAUX (Mange-Mille, Sanctions...) -->
@if(!$seance->fonds_depenses->isEmpty())
    <h3>{{ $sectionIndex++ }}. FONDS SPÉCIAUX & MANGE-MILLE</h3>
    <table class="data">
        <thead>
        <tr>
            <th>Membre</th>
            <th>Type</th>
            <th class="amount">Montant</th>
        </tr>
        </thead>
        <tbody>
        @foreach($seance->fonds_depenses as $fonds)
            <tr>
                <td style="font-weight: bold;">{{ $fonds->user ? $fonds->user->name : 'N/A' }}</td>
                <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $fonds->type) }}</td>
                <td class="amount" style="color: #059669;">+ {{ number_format($fonds->montant, 0, ',', ' ') }} FCFA</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

<!-- 4. SORTIES (Prêts Accordés & Dépenses) -->
<div style="page-break-inside: avoid;">
    <h3>{{ $sectionIndex++ }}. SORTIES (PRÊTS & DÉPENSES)</h3>
    <table class="data">
        <thead>
        <tr>
            <th>Bénéficiaire / Motif</th>
            <th>Type</th>
            <th class="amount">Montant Sortant</th>
        </tr>
    </thead>
    <tbody>
        <!-- Prêts -->
        @foreach($seance->prets->where('statut', 'valide') as $pret)
            <tr>
                <td style="font-weight: bold;">{{ $pret->user->name }}</td>
                <td><span class="badge" style="background-color: #fef9c3; color: #854d0e;">Prêt Accordé</span></td>
                <td class="amount" style="color: #dc2626;">- {{ number_format($pret->montant_demande, 0, ',', ' ') }} FCFA</td>
            </tr>
        @endforeach
        
        <!-- Dépenses -->
        @foreach($seance->depenses->where('statut', 'validee') as $depense)
            <tr>
                <td style="font-weight: bold;">{{ $depense->motif }}</td>
                <td><span class="badge" style="background-color: #fee2e2; color: #991b1b;">Dépense</span></td>
                <td class="amount" style="color: #dc2626;">- {{ number_format($depense->montant, 0, ',', ' ') }} FCFA</td>
            </tr>
        @endforeach

        @if($seance->prets->where('statut', 'valide')->isEmpty() && $seance->depenses->where('statut', 'validee')->isEmpty())
            <tr><td colspan="3" style="text-align: center; padding: 20px; color: #94a3b8;">Aucune sortie d'argent ce jour.</td></tr>
        @endif
    </tbody>
    </table>
</div>

<!-- RÉSUMÉ -->
<div class="summary-container" style="page-break-inside: avoid;">
    <table class="summary-table">
        <tr>
            <td class="summary-label">Total Cotisations :</td>
            <td class="summary-value">+ {{ number_format($totalCotisations, 0, ',', ' ') }}</td>
        </tr>
        @if($totalRemboursements > 0)
        <tr>
            <td class="summary-label">Total Remboursements :</td>
            <td class="summary-value" style="color: #059669;">+ {{ number_format($totalRemboursements, 0, ',', ' ') }}</td>
        </tr>
        @endif
        @if($totalFonds > 0)
        <tr>
            <td class="summary-label">Total Fonds Spéciaux :</td>
            <td class="summary-value" style="color: #059669;">+ {{ number_format($totalFonds, 0, ',', ' ') }}</td>
        </tr>
        @endif
        
        <tr>
            <td class="summary-label" style="border-top: 1px dashed #cbd5e1; padding-top: 5px;">TOTAL ENTRÉES :</td>
            <td class="summary-value" style="border-top: 1px dashed #cbd5e1; color: #059669;">+ {{ number_format($totalEntrees, 0, ',', ' ') }}</td>
        </tr>

        <tr>
            <td class="summary-label">Total Sorties (Prêts/Dépenses) :</td>
            <td class="summary-value" style="color: #dc2626;">- {{ number_format($totalSorties, 0, ',', ' ') }}</td>
        </tr>
        
        <tr class="summary-total">
            <td>SOLDE EN CAISSE :</td>
            <td class="summary-value">{{ number_format($seance->total_encaisse, 0, ',', ' ') }} FCFA</td>
        </tr>
    </table>
    <div style="clear: both;"></div>
</div>

<!-- FOOTER -->
<div class="footer">
    <div class="signatures">
        <div class="sig-col">
            <div class="sig-line"></div>
            <span class="sig-title">Le Trésorier</span>
        </div>
        <div class="sig-col">
            <div class="sig-line"></div>
            <span class="sig-title">Le Censeur</span>
        </div>
        <div class="sig-col">
            <div class="sig-line"></div>
            <span class="sig-title">Le Président</span>
        </div>
    </div>
    <div style="clear: both;"></div>
    <div style="margin-top: 50px; text-align: center; color: #94a3b8; font-size: 8px;">
        Document généré numériquement par Tontine Pro le {{ now()->format('d/m/Y à H:i') }}.
    </div>
</div>

</body>
</html>
