<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContenuInformation;
use Illuminate\Support\Facades\Validator;

class ContenuInformationController extends Controller
{
    /**
     * Lister tous les contenus publiés (public)
     */
    public function index()
    {
        try {
            $contenus = ContenuInformation::where('statut', 'publie')
                ->orderBy('ordre_affichage')
                ->orderBy('date_creation', 'desc')
                ->get();
            
            return response()->json($contenus);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du chargement des contenus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un contenu spécifique (public)
     */
    public function show($id)
    {
        try {
            $contenu = ContenuInformation::find($id);
            
            if (!$contenu) {
                return response()->json(['message' => 'Contenu non trouvé'], 404);
            }
            
            return response()->json($contenu);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du chargement du contenu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un nouveau contenu (admin uniquement)
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'titre' => 'required|string|max:200',
                'contenu' => 'required|string',
                'type_contenu' => 'required|in:page,article',
                'meta_description' => 'nullable|string|max:160',
                'statut' => 'required|in:publie,brouillon,archive',
                'ordre_affichage' => 'nullable|integer|min:0',
            ], [
                'titre.required' => 'Le titre est obligatoire',
                'titre.max' => 'Le titre ne doit pas dépasser 200 caractères',
                'contenu.required' => 'Le contenu est obligatoire',
                'type_contenu.required' => 'Le type de contenu est obligatoire',
                'type_contenu.in' => 'Le type doit être "page" ou "article"',
                'statut.required' => 'Le statut est obligatoire',
                'statut.in' => 'Le statut doit être "publie", "brouillon" ou "archive"',
                'meta_description.max' => 'La méta-description ne doit pas dépasser 160 caractères',
                'ordre_affichage.integer' => 'L\'ordre d\'affichage doit être un nombre entier',
                'ordre_affichage.min' => 'L\'ordre d\'affichage doit être positif'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Préparer les données avec date_creation automatique
            $data = $validator->validated();
            $data['date_creation'] = now();

            $contenu = ContenuInformation::create($data);

            return response()->json([
                'message' => 'Contenu créé avec succès',
                'contenu' => $contenu
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du contenu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Modifier un contenu existant (admin uniquement)
     */
    public function update(Request $request, $id)
    {
        try {
            $contenu = ContenuInformation::find($id);
            
            if (!$contenu) {
                return response()->json(['message' => 'Contenu non trouvé'], 404);
            }

            $validator = Validator::make($request->all(), [
                'titre' => 'required|string|max:200',
                'contenu' => 'required|string',
                'type_contenu' => 'required|in:page,article',
                'meta_description' => 'nullable|string|max:160',
                'statut' => 'required|in:publie,brouillon,archive',
                'ordre_affichage' => 'nullable|integer|min:0',
            ], [
                'titre.required' => 'Le titre est obligatoire',
                'titre.max' => 'Le titre ne doit pas dépasser 200 caractères',
                'contenu.required' => 'Le contenu est obligatoire',
                'type_contenu.required' => 'Le type de contenu est obligatoire',
                'type_contenu.in' => 'Le type doit être "page" ou "article"',
                'statut.required' => 'Le statut est obligatoire',
                'statut.in' => 'Le statut doit être "publie", "brouillon" ou "archive"',
                'meta_description.max' => 'La méta-description ne doit pas dépasser 160 caractères',
                'ordre_affichage.integer' => 'L\'ordre d\'affichage doit être un nombre entier',
                'ordre_affichage.min' => 'L\'ordre d\'affichage doit être positif'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Préparer les données avec date_modification automatique
            $data = $validator->validated();
            $data['date_modification'] = now();

            $contenu->update($data);

            return response()->json([
                'message' => 'Contenu mis à jour avec succès',
                'contenu' => $contenu->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du contenu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un contenu (admin uniquement)
     */
    public function destroy($id)
    {
        try {
            $contenu = ContenuInformation::find($id);
            
            if (!$contenu) {
                return response()->json(['message' => 'Contenu non trouvé'], 404);
            }

            $contenu->delete();

            return response()->json([
                'message' => 'Contenu supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du contenu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
