<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SondageController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Page d'accueil
Route::get('/', function () {
    return view('welcome');
});


Route::middleware(['auth', 'verified'])->group(function () {

    // Tableau de bord (
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gestion du profil utilisateur
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Gestion des sondages
    Route::prefix('sondages')->name('sondages.')->group(function () {
        
        
        Route::get('/create', [SondageController::class, 'create'])->name('create');
        
        
        Route::post('/', [SondageController::class, 'store'])->name('store');
        
        
        Route::get('/{sondage}', [SondageController::class, 'show'])->name('show');
        
        
        Route::get('/{sondage}/edit', [SondageController::class, 'edit'])->name('edit');
        Route::put('/{sondage}', [SondageController::class, 'update'])->name('update');
        
        
        Route::delete('/{sondage}', [SondageController::class, 'destroy'])->name('destroy');
    });
});


require __DIR__.'/auth.php';

