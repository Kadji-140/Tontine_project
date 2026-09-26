<?php

use App\Http\Controllers\Api\AnnonceApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CotisationApiController;
use App\Http\Controllers\Api\CycleApiController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepenseApiController;
use App\Http\Controllers\Api\FileAttenteController;
use App\Http\Controllers\Api\PaiementLotApiController;
use App\Http\Controllers\Api\PretApiController;
use App\Http\Controllers\Api\ProfilApiController;
use App\Http\Controllers\Api\RemboursementApiController;
use App\Http\Controllers\Api\SanctionApiController;
use App\Http\Controllers\Api\SeanceApiController;
use App\Http\Controllers\Api\UtilisateurApiController;
use App\Http\Controllers\Api\SuperAdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes API REST (Tontine App)
|--------------------------------------------------------------------------
*/

// --- AUTHENTIFICATION PUBLIQUE ---
Route::post('/login', [AuthController::class, 'connexion'])->name('api.login');
Route::post('/register', [AuthController::class, 'inscription'])->name('api.register');

// --- ROUTES AUTHENTIFIÉES (Tous membres actifs) ---
Route::middleware(['auth:sanctum'])->group(function () {

    // Utilisateur courant & Déconnexion
    Route::get('/utilisateur', [AuthController::class, 'utilisateurActuel'])->name('api.user');
    Route::post('/logout', [AuthController::class, 'deconnexion'])->name('api.logout');

    // Profil
    Route::get('/profil', [ProfilApiController::class, 'afficher'])->name('api.profil.show');
    Route::post('/profil', [ProfilApiController::class, 'mettreAJour'])->name('api.profil.update');
    Route::put('/profil/mot-de-passe', [ProfilApiController::class, 'changerMotDePasse'])->name('api.profil.password');
    Route::delete('/profil', [ProfilApiController::class, 'supprimer'])->name('api.profil.destroy');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('api.dashboard');
    Route::get('/dashboard/graphique', [DashboardController::class, 'donneesGraphique'])->name('api.dashboard.graph');

    // Cycles (Lecture)
    Route::get('/cycles', [CycleApiController::class, 'index'])->name('api.cycles.index');
    Route::get('/cycles/{id}', [CycleApiController::class, 'show'])->name('api.cycles.show');
    Route::get('/cycles/{id}/membres', [CycleApiController::class, 'membres'])->name('api.cycles.membres');
    Route::get('/cycles/{id}/ordre', [CycleApiController::class, 'ordrePublic'])->name('api.cycles.ordre');
    Route::get('/cycles/{id}/file-attente', [FileAttenteController::class, 'index'])->name('api.cycles.queue');
    Route::get('/cycles/{id}/distribution', [CycleApiController::class, 'distribution'])->name('api.cycles.distribution');

    // Séances (Lecture)
    Route::get('/seances', [SeanceApiController::class, 'index'])->name('api.seances.index');
    Route::get('/seances/{id}', [SeanceApiController::class, 'show'])->name('api.seances.show');
    Route::get('/seances/{id}/rapport-pdf', [SeanceApiController::class, 'telechargerRapport'])->name('api.seances.pdf');

    // Prêts (Tous membres pour leurs demandes)
    Route::get('/prets', [PretApiController::class, 'index'])->name('api.prets.index');
    Route::post('/prets', [PretApiController::class, 'store'])->name('api.prets.store');
    Route::put('/prets/{id}', [PretApiController::class, 'update'])->name('api.prets.update');
    Route::put('/prets/{id}/accepter', [PretApiController::class, 'accepterModification'])->name('api.prets.accepter');
    Route::delete('/prets/{id}', [PretApiController::class, 'destroy'])->name('api.prets.destroy');

    // Remboursements (Recherche dettes)
    Route::get('/seances/{seance}/dettes', [RemboursementApiController::class, 'rechercherDettes'])->name('api.remboursements.search');

    // Sanctions (Consultation)
    Route::get('/sanctions', [SanctionApiController::class, 'index'])->name('api.sanctions.index');

    // Annonces
    Route::get('/annonces', [AnnonceApiController::class, 'index'])->name('api.annonces.index');
    Route::post('/annonces/{id}/lue', [AnnonceApiController::class, 'marquerLue'])->name('api.annonces.lue');

    // Gains (Historique membre et confirmation)
    Route::get('/mes-gains', [PaiementLotApiController::class, 'historique'])->name('api.gains.index');
    Route::put('/gains/{id}/confirmer', [PaiementLotApiController::class, 'confirmer'])->name('api.gains.confirmer');

    // --- ZONE BUREAU (Trésorier et Administrateur) ---
    Route::middleware(['role.bureau'])->group(function () {

        // Gestion des Cycles
        Route::post('/cycles', [CycleApiController::class, 'store'])->name('api.cycles.store');
        Route::put('/cycles/{id}', [CycleApiController::class, 'update'])->name('api.cycles.update');
        Route::delete('/cycles/{id}', [CycleApiController::class, 'destroy'])->name('api.cycles.destroy');
        Route::post('/cycles/{id}/activer', [CycleApiController::class, 'activer'])->name('api.cycles.activer');
        Route::post('/cycles/{id}/membres', [CycleApiController::class, 'ajouterMembre'])->name('api.cycles.membres.store');
        Route::delete('/cycles/{id}/membres/{user}', [CycleApiController::class, 'retirerMembre'])->name('api.cycles.membres.destroy');
        Route::post('/cycles/{id}/membres/aleatoire', [CycleApiController::class, 'melangerRangs'])->name('api.cycles.membres.randomize');
        Route::post('/cycles/{id}/membres/ordre', [CycleApiController::class, 'mettreAJourRangs'])->name('api.cycles.membres.order');

        // Gestion des Séances
        Route::post('/seances', [SeanceApiController::class, 'store'])->name('api.seances.store');
        Route::put('/seances/{id}', [SeanceApiController::class, 'update'])->name('api.seances.update');
        Route::delete('/seances/{id}', [SeanceApiController::class, 'destroy'])->name('api.seances.destroy');
        Route::post('/seances/{id}/preuve', [SeanceApiController::class, 'uploaderPreuve'])->name('api.seances.preuve');
        Route::put('/seances/{id}/valider-preuve', [SeanceApiController::class, 'validerPreuve'])->name('api.seances.valider_preuve');
        Route::put('/seances/{id}/rejeter-preuve', [SeanceApiController::class, 'rejeterPreuve'])->name('api.seances.rejeter_preuve');

        // Cotisations
        Route::post('/cotisations', [CotisationApiController::class, 'store'])->name('api.cotisations.store');
        Route::delete('/cotisations/{id}', [CotisationApiController::class, 'destroy'])->name('api.cotisations.destroy');

        // Prêts (Validation décaissement)
        Route::post('/prets/{id}/valider', [PretApiController::class, 'valider'])->name('api.prets.valider');

        // Remboursements (Enregistrement)
        Route::post('/remboursements', [RemboursementApiController::class, 'store'])->name('api.remboursements.store');

        // Sanctions (Création, encaissement, suppression)
        Route::post('/sanctions', [SanctionApiController::class, 'store'])->name('api.sanctions.store');
        Route::put('/sanctions/{id}/payer', [SanctionApiController::class, 'marquerPayee'])->name('api.sanctions.payer');
        Route::delete('/sanctions/{id}', [SanctionApiController::class, 'destroy'])->name('api.sanctions.destroy');

        // Dépenses
        Route::post('/depenses', [DepenseApiController::class, 'store'])->name('api.depenses.store');
        Route::put('/depenses/{id}/valider', [DepenseApiController::class, 'valider'])->name('api.depenses.valider');
        Route::put('/depenses/{id}/rejeter', [DepenseApiController::class, 'rejeter'])->name('api.depenses.rejeter');
        Route::delete('/depenses/{id}', [DepenseApiController::class, 'destroy'])->name('api.depenses.destroy');

        // Paiements Lots & File d'attente
        Route::post('/paiements-lots', [PaiementLotApiController::class, 'store'])->name('api.lots.store');
        Route::post('/cycles/{id}/file-attente/{user}/payer', [FileAttenteController::class, 'marquerPaye'])->name('api.queue.pay');

        // Annonces
        Route::post('/annonces', [AnnonceApiController::class, 'store'])->name('api.annonces.store');
        Route::delete('/annonces/{id}', [AnnonceApiController::class, 'destroy'])->name('api.annonces.destroy');

        // Utilisateurs
        Route::get('/utilisateurs', [UtilisateurApiController::class, 'index'])->name('api.users.index');
        Route::patch('/utilisateurs/{id}/toggle', [UtilisateurApiController::class, 'basculerStatut'])->name('api.users.toggle');
        Route::get('/utilisateurs/{id}/stats-prets', [UtilisateurApiController::class, 'statsPrets'])->name('api.users.stats');
        Route::get('/utilisateurs/{id}/details', [UtilisateurApiController::class, 'details'])->name('api.users.details');
        Route::delete('/utilisateurs/{id}', [UtilisateurApiController::class, 'destroy'])->name('api.users.destroy');
    });

    // =========================================================================
    // 4. MODULES SUPER-ADMINISTRATEUR (SUPERVISION SAAS PLATEFORME)
    // =========================================================================
    Route::middleware(['role.super_admin'])->prefix('super-admin')->group(function () {
        Route::get('/statistiques', [SuperAdminController::class, 'statistiquesGlobales'])->name('api.superadmin.stats');
        Route::get('/tenants', [SuperAdminController::class, 'listeTenants'])->name('api.superadmin.tenants.index');
        Route::post('/tenants', [SuperAdminController::class, 'creerTenant'])->name('api.superadmin.tenants.store');
        Route::get('/tenants/{id}', [SuperAdminController::class, 'detailsTenant'])->name('api.superadmin.tenants.show');
        Route::patch('/tenants/{id}/statut', [SuperAdminController::class, 'basculerStatutTenant'])->name('api.superadmin.tenants.statut');
        Route::get('/utilisateurs', [SuperAdminController::class, 'listeUtilisateurs'])->name('api.superadmin.users.index');
        Route::post('/utilisateurs/{id}/promouvoir', [SuperAdminController::class, 'promouvoirSuperAdmin'])->name('api.superadmin.users.promote');
        Route::patch('/utilisateurs/{id}', [SuperAdminController::class, 'modifierUtilisateur'])->name('api.superadmin.users.update');
    });
});
