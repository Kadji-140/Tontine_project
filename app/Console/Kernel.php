<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Vérifier les échéances de prêts tous les jours à 8h du matin
        $schedule->command('loans:check-deadlines')
                 ->dailyAt('08:00')
                 ->withoutOverlapping();

        // Fermer automatiquement les séances dont la date est passée
        $schedule->call(function () {
            \App\Models\Seance::where('statut', 'ouverte')
                ->where('date_seance', '<', now()->startOfDay())
                ->update(['statut' => 'fermee']);
        })->dailyAt('00:01');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
