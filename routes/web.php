<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModerationController;
use App\Http\Controllers\ParticipationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResultatController;
use App\Http\Controllers\SondageController;
use Illuminate\Support\Facades\Route;

// Page d'accueil
Route::get('/', function () {
    return view('welcome');
})->name('accueil');

Route::middleware(['auth', 'verified'])->group(function () {

    // Tableau de bord : sondages de l'utilisateur
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil utilisateur
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Gestion des sondages par leur créateur
    Route::prefix('sondages')->name('sondages.')->whereNumber('sondage')->group(function () {
        Route::get('/create', [SondageController::class, 'create'])->name('create');
        Route::post('/', [SondageController::class, 'store'])->name('store');
        Route::get('/{sondage}', [SondageController::class, 'show'])->name('show');
        Route::get('/{sondage}/edit', [SondageController::class, 'edit'])->name('edit');
        Route::put('/{sondage}', [SondageController::class, 'update'])->name('update');
        Route::delete('/{sondage}', [SondageController::class, 'destroy'])->name('destroy');
        Route::post('/{sondage}/ouvrir', [SondageController::class, 'open'])->name('open');
        Route::post('/{sondage}/cloturer', [SondageController::class, 'close'])->name('close');
        Route::get('/{sondage}/resultats', [ResultatController::class, 'show'])->name('resultats');
    });

    // Participation aux sondages ouverts
    Route::get('/sondages/ouverts', [ParticipationController::class, 'index'])->name('participations.index');
    Route::get('/sondages/{sondage}/repondre', [ParticipationController::class, 'create'])
        ->whereNumber('sondage')->name('participations.create');
    Route::post('/sondages/{sondage}/repondre', [ParticipationController::class, 'store'])
        ->whereNumber('sondage')->name('participations.store');

    // Validation des sondages par un administrateur
    Route::prefix('validation')->name('moderation.')->whereNumber('sondage')->group(function () {
        Route::get('/', [ModerationController::class, 'index'])->name('index');
        Route::post('/{sondage}/valider', [ModerationController::class, 'approve'])->name('approve');
        Route::post('/{sondage}/refuser', [ModerationController::class, 'reject'])->name('reject');
    });
});

require __DIR__.'/auth.php';
