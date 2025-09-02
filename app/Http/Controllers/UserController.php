<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Vérifier si l'utilisateur est admin
     */
    private function checkAdmin()
    {
        $user = Auth::user();
        
        if (!$user || $user->id_role !== 2) {
            return response()->json([
                'error' => 'Accès refusé',
                'message' => 'Droits administrateur requis'
            ], 403);
        }
        
        if ($user->statut !== 'actif') {
            return response()->json([
                'error' => 'Compte inactif',
                'message' => 'Votre compte administrateur est inactif'
            ], 403);
        }
        
        return null; // Pas d'erreur
    }

    /**
     * Récupérer le profil de l'utilisateur connecté
     */
    public function profile()
    {
        return response()->json(Auth::user());
    }

    /**
     * Mettre à jour le profil de l'utilisateur connecté
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'email' => 'required|email|unique:utilisateur,email,' . $user->id_utilisateur . ',id_utilisateur',
            'mot_de_passe' => 'nullable|string|min:6|confirmed',
            'date_naissance' => 'nullable|date'
        ], [
            'nom.required' => 'Le nom est obligatoire',
            'nom.max' => 'Le nom ne doit pas dépasser 50 caractères',
            'prenom.required' => 'Le prénom est obligatoire',
            'prenom.max' => 'Le prénom ne doit pas dépasser 50 caractères',
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'Format d\'email invalide',
            'email.unique' => 'Cet email est déjà utilisé',
            'mot_de_passe.min' => 'Le mot de passe doit contenir au moins 6 caractères',
            'mot_de_passe.confirmed' => 'La confirmation du mot de passe ne correspond pas',
            'date_naissance.date' => 'Format de date invalide'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = [
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
            ];

            // Ajouter la date de naissance si fournie
            if ($request->has('date_naissance') && !empty($request->date_naissance)) {
                $updateData['date_naissance'] = $request->date_naissance;
            }

            // Mettre à jour le mot de passe seulement s'il est fourni
            if (!empty($request->mot_de_passe)) {
                $updateData['mot_de_passe'] = Hash::make($request->mot_de_passe);
            }

            $user->update($updateData);

            // Retourner l'utilisateur mis à jour (sans le mot de passe)
            $updatedUser = $user->fresh();
            unset($updatedUser->mot_de_passe);

            return response()->json([
                'message' => 'Profil mis à jour avec succès',
                'user' => $updatedUser
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher la liste de tous les utilisateurs (admin uniquement)
     */
    public function index()
    {
        // Vérification admin
        $adminCheck = $this->checkAdmin();
        if ($adminCheck) return $adminCheck;
        
        try {
            $users = Utilisateur::select('id_utilisateur', 'nom', 'prenom', 'email', 'id_role', 'statut', 'date_creation')
                        ->orderBy('date_creation', 'desc')
                        ->get();
            
            return response()->json($users);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des utilisateurs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un nouveau utilisateur (admin uniquement)
     */
    public function store(Request $request)
    {
        // Vérification admin
        $adminCheck = $this->checkAdmin();
        if ($adminCheck) return $adminCheck;
        
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:utilisateur,email',
            'mot_de_passe' => 'required|string|min:6',
            'id_role' => 'required|integer|in:1,2',
            'statut' => 'required|string|in:actif,inactif'
        ], [
            'nom.required' => 'Le nom est obligatoire',
            'prenom.required' => 'Le prénom est obligatoire',
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'Format d\'email invalide',
            'email.unique' => 'Cet email est déjà utilisé',
            'mot_de_passe.required' => 'Le mot de passe est obligatoire',
            'mot_de_passe.min' => 'Le mot de passe doit contenir au moins 6 caractères',
            'id_role.required' => 'Le rôle est obligatoire',
            'id_role.in' => 'Le rôle doit être 1 (utilisateur) ou 2 (admin)',
            'statut.required' => 'Le statut est obligatoire',
            'statut.in' => 'Le statut doit être actif ou inactif'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Utilisateur::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'mot_de_passe' => Hash::make($request->mot_de_passe),
                'id_role' => $request->id_role,
                'statut' => $request->statut,
                'date_creation' => now()
            ]);

            // Retourner l'utilisateur sans le mot de passe
            $userResponse = $user->toArray();
            unset($userResponse['mot_de_passe']);

            return response()->json([
                'message' => 'Utilisateur créé avec succès',
                'user' => $userResponse
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un utilisateur (admin uniquement)
     */
    public function update(Request $request, $id)
    {
        // Vérification admin
        $adminCheck = $this->checkAdmin();
        if ($adminCheck) return $adminCheck;
        
        $user = Utilisateur::find($id);
        
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:utilisateur,email,' . $id . ',id_utilisateur',
            'mot_de_passe' => 'nullable|string|min:6',
            'id_role' => 'required|integer|in:1,2',
            'statut' => 'required|string|in:actif,inactif'
        ], [
            'nom.required' => 'Le nom est obligatoire',
            'prenom.required' => 'Le prénom est obligatoire',
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'Format d\'email invalide',
            'email.unique' => 'Cet email est déjà utilisé',
            'mot_de_passe.min' => 'Le mot de passe doit contenir au moins 6 caractères',
            'id_role.in' => 'Le rôle doit être 1 (utilisateur) ou 2 (admin)',
            'statut.in' => 'Le statut doit être actif ou inactif'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = [
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'id_role' => $request->id_role,
                'statut' => $request->statut
            ];

            // Mettre à jour le mot de passe seulement s'il est fourni
            if (!empty($request->mot_de_passe)) {
                $updateData['mot_de_passe'] = Hash::make($request->mot_de_passe);
            }

            $user->update($updateData);

            // Retourner l'utilisateur mis à jour sans le mot de passe
            $userResponse = $user->fresh()->toArray();
            unset($userResponse['mot_de_passe']);

            return response()->json([
                'message' => 'Utilisateur mis à jour avec succès',
                'user' => $userResponse
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un utilisateur (admin uniquement)
     */
    public function destroy($id)
    {
        // Vérification admin
        $adminCheck = $this->checkAdmin();
        if ($adminCheck) return $adminCheck;
        
        $user = Utilisateur::find($id);
        
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        // Empêcher la suppression de son propre compte
        if (auth()->user()->id_utilisateur == $id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas supprimer votre propre compte'
            ], 403);
        }

        try {
            $userName = $user->nom . ' ' . $user->prenom;
            $user->delete();

            return response()->json([
                'message' => 'Utilisateur ' . $userName . ' supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
