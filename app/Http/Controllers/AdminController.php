<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use App\Models\ContenuInformation;
use App\Models\ExerciceRespiration;
use App\Models\SessionRespiration;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Obtenir les statistiques pour le tableau de bord admin
     */
    public function getStats()
    {
        try {
            $stats = [
                'utilisateurs' => [
                    'total' => Utilisateur::count(),
                    'actifs' => Utilisateur::where('statut', 'actif')->count(),
                    'admins' => Utilisateur::where('id_role', 2)->count(),
                    'nouveaux_cette_semaine' => Utilisateur::where('date_creation', '>=', now()->subWeek())->count()
                ],
                'contenus' => [
                    'total' => ContenuInformation::count(),
                    'publies' => ContenuInformation::where('statut', 'publie')->count(),
                    'brouillons' => ContenuInformation::where('statut', 'brouillon')->count(),
                    'nouveaux_cette_semaine' => ContenuInformation::where('date_creation', '>=', now()->subWeek())->count()
                ],
                'exercices' => [
                    'total' => ExerciceRespiration::count(),
                    'actifs' => ExerciceRespiration::where('actif', true)->count()
                ],
                'sessions' => [
                    'total' => SessionRespiration::count(),
                    'cette_semaine' => SessionRespiration::where('date_debut', '>=', now()->subWeek())->count(),
                    'ce_mois' => SessionRespiration::where('date_debut', '>=', now()->subMonth())->count(),
                    'duree_moyenne' => SessionRespiration::avg('duree_reelle') ?? 0
                ]
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du chargement des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les dernières activités pour le dashboard
     */
    public function getRecentActivity()
    {
        try {
            $recentActivity = [
                'nouveaux_utilisateurs' => Utilisateur::select('nom', 'prenom', 'email', 'date_creation')
                    ->orderBy('date_creation', 'desc')
                    ->limit(5)
                    ->get(),
                    
                'derniers_contenus' => ContenuInformation::select('titre', 'statut', 'date_creation')
                    ->orderBy('date_creation', 'desc')
                    ->limit(5)
                    ->get(),
                    
                'sessions_recentes' => SessionRespiration::with(['utilisateur:id_utilisateur,nom,prenom', 'exercice:id_exercice,nom'])
                    ->select('id_utilisateur', 'id_exercice', 'duree_reelle', 'date_debut')
                    ->orderBy('date_debut', 'desc')
                    ->limit(10)
                    ->get()
            ];

            return response()->json($recentActivity);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du chargement des activités récentes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les données pour les graphiques du dashboard
     */
    public function getChartData()
    {
        try {
            // Données pour graphique des inscriptions par jour (7 derniers jours)
            $inscriptionsParJour = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $count = Utilisateur::whereDate('date_creation', $date->format('Y-m-d'))->count();
                $inscriptionsParJour[] = [
                    'date' => $date->format('Y-m-d'),
                    'inscriptions' => $count
                ];
            }

            // Données pour graphique des sessions par jour (7 derniers jours)
            $sessionsParJour = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $count = SessionRespiration::whereDate('date_debut', $date->format('Y-m-d'))->count();
                $sessionsParJour[] = [
                    'date' => $date->format('Y-m-d'),
                    'sessions' => $count
                ];
            }

            // Exercices les plus populaires
            $exercicesPopulaires = SessionRespiration::selectRaw('id_exercice, COUNT(*) as total_sessions')
                ->with('exercice:id_exercice,nom')
                ->groupBy('id_exercice')
                ->orderBy('total_sessions', 'desc')
                ->limit(5)
                ->get();

            $chartData = [
                'inscriptions_par_jour' => $inscriptionsParJour,
                'sessions_par_jour' => $sessionsParJour,
                'exercices_populaires' => $exercicesPopulaires
            ];

            return response()->json($chartData);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du chargement des données graphiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}