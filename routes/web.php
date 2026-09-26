<?php

use App\Http\Controllers\AnnonceController;
use App\Http\Controllers\CotisationController;
use App\Http\Controllers\CycleController;
use App\Http\Controllers\DepenseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PretController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RemboursementController;
use App\Http\Controllers\SanctionController;
use App\Http\Controllers\SeanceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('home');
});



Route::get('/inactive', function () {
    return view('auth.inactive');
})->name('inactive');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {
    // Route::resource crée automatiquement les 7 routes (index, create, store, show, edit, update, destroy)
    // Routes accessibles à tous les membres (Lecture seule par exemple)
    Route::get('/cycles', [CycleController::class, 'index'])->name('cycles.index');
    // ...
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [HomeController::class, 'getChartData'])->name('dashboard.chart-data');

    // --- ROUTES ACCESSIBLES À TOUS (Membres & Trésoriers) ---
    // Liste des prêts (Le contrôleur filtrera : un membre ne voit que SES prêts)
    Route::get('/prets', [PretController::class, 'index'])->name('prets.index');
    // Formulaire de demande
    Route::get('/prets/create', [PretController::class, 'create'])->name('prets.create');
    // Enregistrement de la demande
    Route::post('/prets', [PretController::class, 'store'])->name('prets.store');
    // Annuler sa propre demande (Destroy)
    Route::delete('/prets/{pret}', [PretController::class, 'destroy'])->name('prets.destroy');
    Route::get('/prets/{pret}/edit', [PretController::class, 'edit'])->name('prets.edit');
    Route::put('/prets/{pret}', [PretController::class, 'update'])->name('prets.update');

    Route::put('/prets/{pret}/accepter', [PretController::class, 'accepterModification'])->name('prets.accepter');

    // Routes Remboursements
    Route::get('/seances/{seance}/dettes/search', [App\Http\Controllers\RemboursementController::class, 'searchDettes'])->name('remboursements.search');
    Route::post('/remboursements', [App\Http\Controllers\RemboursementController::class, 'store'])->name('remboursements.store');

    Route::get('/annonces/{id}/read', [AnnonceController::class, 'markAsRead'])->name('annonces.read');

    Route::get('/cycles/{cycle}/payment-queue', [App\Http\Controllers\PaymentQueueController::class, 'index'])->name('payment-queue.index');
    
    // Accès public (authentifié) à la liste des membres du cycle pour voir l'ordre (Gestion)
    Route::get('cycles/{cycle}/membres', [CycleController::class, 'membres'])->name('cycles.membres');
    
    // NOUVELLE ROUTE : Vue publique de l'ordre de passage (Timeline)
    Route::get('cycles/{cycle}/ordre-public', [CycleController::class, 'publicOrder'])->name('cycles.public');

    Route::post('/cycles/{cycle}/payment-queue/{user}/pay', [App\Http\Controllers\PaymentQueueController::class, 'markAsPaid'])->name('payment-queue.pay');

    // Routes Gains / Versements Lots
    Route::get('/mes-gains', [\App\Http\Controllers\PaiementLotController::class, 'historique'])->name('gains.historique');
    Route::put('/gains/{paiement}/confirmer', [\App\Http\Controllers\PaiementLotController::class, 'confirmer'])->name('gains.confirmer');

    // Routes pour les informations membres (trésorier seulement)
    Route::get('/membre/{user}/stats', [UserController::class, 'getPretStats'])
        ->name('membre.stats')
        ->middleware('is_tresorier');

    Route::get('/membre/{user}/details', [UserController::class, 'getDetailsMembre'])
        ->name('membre.details')
        ->middleware('is_tresorier');

    // Gestion Globale des Utilisateurs (pour valider les inscriptions)
    Route::get('/utilisateurs', [UserController::class, 'index'])->name('users.index')->middleware('is_tresorier');
    Route::patch('/utilisateurs/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle')->middleware('is_tresorier');
    Route::delete('/utilisateurs/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('is_tresorier');

    // --- ZONE SÉCURISÉE (BUREAU SEULEMENT) ---
        Route::middleware(['is_tresorier'])->group(function () {

            // Gestion complète des Cycles (Création/Modif)
            Route::get('cycles/{cycle}/distribution', [CycleController::class, 'showDistribution'])->name('cycles.distribution');
            Route::resource('cycles', CycleController::class)->except(['index', 'show']);

            // Gestion des membres du cycle (Sécurisé dans le contrôleur)
            Route::post('cycles/{cycle}/membres', [CycleController::class, 'storeMembre'])->name('cycles.membres.store');
            Route::post('cycles/{cycle}/membres/randomize', [CycleController::class, 'randomizeRanks'])->name('cycles.membres.randomize');
            Route::post('cycles/{cycle}/membres/order', [CycleController::class, 'updateRanks'])->name('cycles.membres.order');
            Route::delete('cycles/{cycle}/membres/{user}', [CycleController::class, 'destroyMembre'])->name('cycles.membres.destroy');

            // Gestion des Séances
            Route::resource('seances', SeanceController::class);

            // Enregistrement de l'argent
            Route::post('cotisations', [CotisationController::class, 'store'])->name('cotisations.store');
            Route::delete('cotisations/{cotisation}', [CotisationController::class, 'destroy'])->name('cotisations.destroy');

            //Route::resource('prets', PretController::class);

            // Route spéciale pour valider (donner l'argent)
            Route::post('/prets/{pret}/valider', [PretController::class, 'valider'])->name('prets.valider');

            Route::resource('sanctions', SanctionController::class)->except(['show']);

            // Route pour marquer une sanction comme payée (l'argent rentre en caisse)
            Route::put('/sanctions/{sanction}/payer', [SanctionController::class, 'markAsPaid'])->name('sanctions.payer');

            Route::post('/depenses', [DepenseController::class, 'store'])->name('depenses.store');
            Route::delete('/depenses/{depense}', [DepenseController::class, 'destroy'])->name('depenses.destroy');
            Route::put('/depenses/{depense}/valider', [DepenseController::class, 'valider'])->name('depenses.valider');
            Route::put('/depenses/{depense}/rejeter', [DepenseController::class, 'rejeter'])->name('depenses.rejeter');

            Route::post('/paiements-lots', [\App\Http\Controllers\PaiementLotController::class, 'store'])->name('paiements-lots.store');
            Route::get('/paiements-lots', function() { return redirect()->route('dashboard'); });

            Route::post('/annonces', [AnnonceController::class, 'store'])->name('annonces.store');
            Route::delete('/annonces/{annonce}', [AnnonceController::class, 'destroy'])->name('annonces.destroy');

            // Gestion de la preuve de versement (Seance)
            Route::post('/seances/{seance}/upload-preuve', [SeanceController::class, 'uploadPreuve'])->name('seances.upload_preuve');
            Route::put('/seances/{seance}/valider-preuve', [SeanceController::class, 'validerPreuve'])->name('seances.valider_preuve');
            Route::put('/seances/{seance}/rejeter-preuve', [SeanceController::class, 'rejeterPreuve'])->name('seances.rejeter_preuve');
        });

    // Route pour générer le PDF d'une séance spécifique
    Route::get('/seances/{seance}/rapport', [SeanceController::class, 'downloadReport'])->name('seances.rapport');
});
