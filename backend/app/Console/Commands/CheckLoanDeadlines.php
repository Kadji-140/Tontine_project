<?php

namespace App\Console\Commands;

use App\Models\Pret;
use App\Models\Annonce;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckLoanDeadlines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:check-deadlines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifier les échéances de prêts et envoyer des alertes automatiques';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $alertsSent = 0;
        
        $this->info('🔍 Vérification des échéances de prêts...');
        
        // 1. Prêts validés non remboursés (échéance future ou aujourd'hui)
        $pretsActifs = Pret::where('statut', 'valide')
            ->whereDate('date_echeance', '>=', $today)
            ->get();

        foreach ($pretsActifs as $pret) {
            $daysRemaining = $today->diffInDays($pret->date_echeance, false);
            
            // Alertes selon les jours restants: 7, 3, ou 0 jours
            if (in_array($daysRemaining, [7, 3, 0])) {
                if ($this->sendDeadlineAlert($pret, $daysRemaining)) {
                    $alertsSent++;
                }
            }
        }
        
        // 2. Prêts en retard
        $pretsEnRetard = Pret::where('statut', 'valide')
            ->whereDate('date_echeance', '<', $today)
            ->get();
            
        foreach ($pretsEnRetard as $pret) {
            if ($this->sendOverdueAlert($pret)) {
                $alertsSent++;
            }
        }
        
        $this->info("✅ {$alertsSent} alerte(s) envoyée(s)");
        
        return Command::SUCCESS;
    }
    
    /**
     * Envoyer une alerte pour une échéance approchante
     */
    private function sendDeadlineAlert($pret, $days)
    {
        // Vérifier si alerte déjà envoyée aujourd'hui pour ce prêt
        $existingAlert = Annonce::where('user_id', 1) // System user (admin)
            ->whereDate('created_at', Carbon::today())
            ->where('titre', 'LIKE', "%Échéance prêt #{$pret->id}%")
            ->exists();
            
        if ($existingAlert) {
            $this->line("⏭️  Alerte déjà envoyée pour le prêt #{$pret->id}");
            return false;
        }
        
        $montantTotal = $pret->montant_demande + $pret->interet_total;
        
        $messages = [
            7 => "⏰ **Rappel Important**\n\nVotre prêt de **" . number_format($montantTotal, 0, ',', ' ') . " FCFA** arrive à échéance dans **7 jours**.\n\n📅 Date limite : " . $pret->date_echeance->format('d/m/Y') . "\n\nPensez à préparer le remboursement pour éviter toute sanction.",
            
            3 => "⚠️ **URGENT - 3 jours restants**\n\nVotre prêt arrive à échéance dans **3 jours seulement** !\n\n💰 Montant à rembourser : **" . number_format($montantTotal, 0, ',', ' ') . " FCFA**\n📅 Date limite : " . $pret->date_echeance->format('d/m/Y') . "\n\n🚨 Remboursez rapidement pour éviter des sanctions.",
            
            0 => "🚨 **AUJOURD'HUI - Échéance de votre prêt**\n\nC'est aujourd'hui la date limite de remboursement de votre prêt !\n\n💰 Montant à rembourser : **" . number_format($montantTotal, 0, ',', ' ') . " FCFA**\n\n⚠️ Remboursez immédiatement pour éviter des pénalités et sanctions."
        ];
        
        $annonce = Annonce::create([
            'titre' => "Échéance prêt #{$pret->id} - J-{$days}",
            'message' => $messages[$days],
            'user_id' => 1, // System
        ]);
        
        $annonce->readers()->attach($pret->user_id);
        
        $this->info("📧 Alerte J-{$days} envoyée à {$pret->user->name} (Prêt #{$pret->id})");
        
        return true;
    }
    
    /**
     * Envoyer une alerte pour un prêt en retard
     */
    private function sendOverdueAlert($pret)
    {
        $daysLate = Carbon::today()->diffInDays($pret->date_echeance);
        
        // Alerte tous les 3 jours de retard (jour 1, 4, 7, 10, etc.)
        if ($daysLate % 3 !== 1) {
            return false;
        }
        
        // Vérifier si alerte déjà envoyée aujourd'hui
        $existingAlert = Annonce::where('user_id', 1)
            ->whereDate('created_at', Carbon::today())
            ->where('titre', 'LIKE', "%RETARD%Prêt #{$pret->id}%")
            ->exists();
            
        if ($existingAlert) {
            return false;
        }
        
        $montantTotal = $pret->montant_demande + $pret->interet_total;
        
        $annonce = Annonce::create([
            'titre' => "🔴 RETARD - Prêt #{$pret->id} ({$daysLate} jours)",
            'message' => "🔴 **PRÊT EN RETARD**\n\nVotre prêt est en retard de **{$daysLate} jours** !\n\n💰 Montant dû : **" . number_format($montantTotal, 0, ',', ' ') . " FCFA**\n📅 Date d'échéance dépassée : " . $pret->date_echeance->format('d/m/Y') . "\n\n⚠️ **Remboursez IMMÉDIATEMENT** pour éviter des sanctions sévères et des pénalités supplémentaires.\n\n🚨 Plus le retard persiste, plus les conséquences seront importantes.",
            'user_id' => 1,
        ]);
        
        $annonce->readers()->attach($pret->user_id);
        
        $this->error("🚨 Alerte RETARD ({$daysLate}j) envoyée à {$pret->user->name} (Prêt #{$pret->id})");
        
        return true;
    }
}
