<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContenuInformationController;
use App\Http\Controllers\ExerciceRespirationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ========================================
// ROUTES PUBLIQUES (sans authentification)
// ========================================

// Test route
Route::get('/test', function() {
    return response()->json(['message' => 'API fonctionne !', 'timestamp' => now()]);
});

// Debug register
Route::post('/register-debug', function(Request $request) {
    return response()->json([
        'received_data' => $request->all(),
        'content_type' => $request->header('Content-Type'),
        'method' => $request->method()
    ]);
});

// Register simple pour test
Route::post('/register-simple', function(Request $request) {
    try {
        // Vérification basique
        $email = $request->email;
        $existingUser = \App\Models\Utilisateur::where('email', $email)->first();
        
        if ($existingUser) {
            return response()->json(['error' => 'Email déjà utilisé'], 422);
        }
        
        // Création utilisateur
        $user = \App\Models\Utilisateur::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'mot_de_passe' => \Illuminate\Support\Facades\Hash::make($request->mot_de_passe),
            'statut' => 'actif',
            'id_role' => 1,
            'date_creation' => now(),
        ]);

        return response()->json([
            'message' => 'Inscription réussie',
            'user_id' => $user->id_utilisateur
        ], 201);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur création utilisateur',
            'details' => $e->getMessage()
        ], 500);
    }
});

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Contenu Information (public - lecture seule)
Route::get('/contenus', [ContenuInformationController::class, 'index']);
Route::get('/contenus/{id}', [ContenuInformationController::class, 'show']);

// Exercices de respiration (public - lecture seule)
Route::get('/exercices', [ExerciceRespirationController::class, 'index']);
Route::get('/exercices/{id}', [ExerciceRespirationController::class, 'show']);

// ========================================
// ROUTES UTILISATEUR CONNECTÉ
// ========================================

Route::middleware('auth:sanctum')->group(function () {
    // Auth utilisateur
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [UserController::class, 'profile']);
    Route::put('/user', [UserController::class, 'updateProfile']); // Modifier son propre profil
    
    // Exercices - Fonctionnalités utilisateur
    Route::post('/exercices/{id}/favori', [ExerciceRespirationController::class, 'addFavori']);
    Route::delete('/exercices/{id}/favori', [ExerciceRespirationController::class, 'removeFavori']);
    Route::get('/exercices/favoris', [ExerciceRespirationController::class, 'favoris']);
    
    // Sessions de respiration
    Route::post('/sessions', [ExerciceRespirationController::class, 'startSession']);
    Route::put('/sessions/{id}/end', [ExerciceRespirationController::class, 'endSession']);
    Route::get('/sessions', [ExerciceRespirationController::class, 'userSessions']);
    Route::get('/statistiques', [ExerciceRespirationController::class, 'userStats']);
});

// ========================================
// ROUTES ADMINISTRATEUR (admin uniquement)
// ========================================

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    
    // =============
    // GESTION DES UTILISATEURS
    // =============
    Route::get('/users', [UserController::class, 'index']);           // Liste tous les utilisateurs
    Route::post('/users', [UserController::class, 'store']);          // Créer un utilisateur
    Route::put('/users/{id}', [UserController::class, 'update']);     // Modifier un utilisateur
    Route::delete('/users/{id}', [UserController::class, 'destroy']); // Supprimer un utilisateur
    
    // =============
    // GESTION DES CONTENUS
    // =============
    Route::post('/contenus', [ContenuInformationController::class, 'store']);     // Créer un contenu
    Route::put('/contenus/{id}', [ContenuInformationController::class, 'update']); // Modifier un contenu
    Route::delete('/contenus/{id}', [ContenuInformationController::class, 'destroy']); // Supprimer un contenu
    
    // =============
    // GESTION DES EXERCICES (à ajouter si nécessaire)
    // =============
    // Route::post('/exercices', [ExerciceRespirationController::class, 'store']);
    // Route::put('/exercices/{id}', [ExerciceRespirationController::class, 'update']);
    // Route::delete('/exercices/{id}', [ExerciceRespirationController::class, 'destroy']);
    
    // =============
    // STATISTIQUES ET TABLEAU DE BORD ADMIN
    // =============
    Route::get('/admin/stats', [AdminController::class, 'getStats']);           // Statistiques générales
    Route::get('/admin/activity', [AdminController::class, 'getRecentActivity']); // Activités récentes
    Route::get('/admin/charts', [AdminController::class, 'getChartData']);      // Données pour graphiques
});