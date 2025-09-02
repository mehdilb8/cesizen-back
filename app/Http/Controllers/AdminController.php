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
     * Vérifier si l'utilisateur est admin
     */
    private function checkAdmin()
    {
        $user = auth()->user();
        
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
        
        return null;
    }

    /**
     * Obtenir les statistiques pour le tableau de bord admin
     */
    public function getStats()
    {
        // Vérification admin
        $adminCheck = $this->checkAdmin();
        if ($adminCheck) return $adminCheck;
        
        try {
            $stats = [
                'utilisateurs' => [
                    'total' => Utilisateur::count(),
                    'actifs' => Utilisateur::where('statut', 'actif')->count(),
                    'inactifs' => Utilisateur::where('statut', 'inactif')->count(),
                    'admins' => Utilisateur::where('id_role', 2)->count(),
                    'utilisateurs_normaux' => Utilisateur::where('id_role', 1)->count(),
                    'nouveaux_cette_semaine' => Utilisateur::where('date_creation', '>=', now()->subWeek())->count(),
                    'nouveaux_ce_mois' => Utilisateur::where('date_creation', '>=', now()->subMonth())->count()
                ],
                'contenus' => [
                    'total' => ContenuInformation::count(),
                    'publies' => ContenuInformation::where('statut', 'publie')->count(),
                    'brouillons' => ContenuInformation::where('statut', 'brouillon')->count(),
                    'nouveaux_cette_semaine' => ContenuInformation::where('date_creation', '>=', now()->subWeek())->count(),
                    'nouveaux_ce_mois' => ContenuInformation::where('date_creation', '>=', now()->subMonth())->count()
                ],
                'exercices' => [
                    'total' => ExerciceRespiration::count(),
                    'actifs' => ExerciceRespiration::where('actif', true)->count(),
                    'inactifs' => ExerciceRespiration::where('actif', false)->count()
                ],
                'sessions' => [
                    'total' => SessionRespiration::count(),
                    'cette_semaine' => SessionRespiration::where('date_debut', '>=', now()->subWeek())->count(),
                    'ce_mois' => SessionRespiration::where('date_debut', '>=', now()->subMonth())->count(),
                    'aujourd_hui' => SessionRespiration::whereDate('date_debut', today())->count(),
                    'duree_moyenne' => round(SessionRespiration::avg('duree_reelle') ?? 0, 2),
                    'duree_totale' => SessionRespiration::sum('duree_reelle') ?? 0
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'generated_at' => now()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
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
        // Vérification admin
        $adminCheck = $this->checkAdmin();
        if ($adminCheck) return $adminCheck;
        
        try {
            $recentActivity = [
                'nouveaux_utilisateurs' => Utilisateur::select('id_utilisateur', 'nom', 'prenom', 'email', 'date_creation', 'statut')
                    ->orderBy('date_creation', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($user) {
                        return [
                            'id' => $user->id_utilisateur,
                            'nom_complet' => $user->nom . ' ' . $user->prenom,
                            'email' => $user->email,
                            'statut' => $user->statut,
                            'date_creation' => $user->date_creation,
                            'temps_ecoule' => $user->date_creation->diffForHumans()
                        ];
                    }),
                    
                'derniers_contenus' => ContenuInformation::select('id_contenu', 'titre', 'statut', 'date_creation')
                    ->orderBy('date_creation', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($contenu) {
                        return [
                            'id' => $contenu->id_contenu,
                            'titre' => $contenu->titre,
                            'statut' => $contenu->statut,
                            'date_creation' => $contenu->date_creation,
                            'temps_ecoule' => $contenu->date_creation->diffForHumans()
                        ];
                    }),
                    
                'sessions_recentes' => SessionRespiration::with(['utilisateur:id_utilisateur,nom,prenom', 'exercice:id_exercice,nom'])
                    ->select('id_session', 'id_utilisateur', 'id_exercice', 'duree_reelle', 'date_debut')
                    ->orderBy('date_debut', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(function ($session) {
                        return [
                            'id' => $session->id_session,
                            'utilisateur' => $session->utilisateur ? 
                                $session->utilisateur->nom . ' ' . $session->utilisateur->prenom : 
                                'Utilisateur supprimé',
                            'exercice' => $session->exercice ? 
                                $session->exercice->nom : 
                                'Exercice supprimé',
                            'duree_reelle' => $session->duree_reelle,
                            'date_debut' => $session->date_debut,
                            'temps_ecoule' => $session->date_debut->diffForHumans()
                        ];
                    })
            ];

            return response()->json([
                'success' => true,
                'data' => $recentActivity,
                'generated_at' => now()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
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
        // Vérification admin
        $adminCheck = $this->checkAdmin();
        if ($adminCheck) return $adminCheck;
        
        try {
            // Données pour graphique des inscriptions par jour (7 derniers jours)
            $inscriptionsParJour = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $count = Utilisateur::whereDate('date_creation', $date->format('Y-m-d'))->count();
                $inscriptionsParJour[] = [
                    'date' => $date->format('Y-m-d'),
                    'date_fr' => $date->format('d/m'),
                    'jour' => $date->format('l'),
                    'inscriptions' => $count
                ];
            }

            // Données pour graphique des sessions par jour (7 derniers jours)
            $sessionsParJour = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $count = SessionRespiration::whereDate('date_debut', $date->format('Y-m-d'))->count();
                $dureeTotal = SessionRespiration::whereDate('date_debut', $date->format('Y-m-d'))->sum('duree_reelle') ?? 0;
                $sessionsParJour[] = [
                    'date' => $date->format('Y-m-d'),
                    'date_fr' => $date->format('d/m'),
                    'jour' => $date->format('l'),
                    'sessions' => $count,
                    'duree_totale' => $dureeTotal
                ];
            }

            // Exercices les plus populaires
            $exercicesPopulaires = SessionRespiration::selectRaw('id_exercice, COUNT(*) as total_sessions, SUM(duree_reelle) as duree_totale')
                ->with('exercice:id_exercice,nom')
                ->groupBy('id_exercice')
                ->orderBy('total_sessions', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'exercice_id' => $item->id_exercice,
                        'exercice_nom' => $item->exercice ? $item->exercice->nom : 'Exercice supprimé',
                        'total_sessions' => $item->total_sessions,
                        'duree_totale' => $item->duree_totale ?? 0,
                        'duree_moyenne' => $item->total_sessions > 0 ? 
                            round(($item->duree_totale ?? 0) / $item->total_sessions, 2) : 0
                    ];
                });

            // Évolution mensuelle (12 derniers mois)
            $evolutionMensuelle = [];
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $utilisateurs = Utilisateur::whereYear('date_creation', $date->year)
                    ->whereMonth('date_creation', $date->month)
                    ->count();
                $sessions = SessionRespiration::whereYear('date_debut', $date->year)
                    ->whereMonth('date_debut', $date->month)
                    ->count();
                
                $evolutionMensuelle[] = [
                    'mois' => $date->format('Y-m'),
                    'mois_fr' => $date->format('M Y'),
                    'utilisateurs' => $utilisateurs,
                    'sessions' => $sessions
                ];
            }

            $chartData = [
                'inscriptions_par_jour' => $inscriptionsParJour,
                'sessions_par_jour' => $sessionsParJour,
                'exercices_populaires' => $exercicesPopulaires,
                'evolution_mensuelle' => $evolutionMensuelle
            ];

            return response()->json([
                'success' => true,
                'data' => $chartData,
                'generated_at' => now()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des données graphiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir un résumé rapide pour le dashboard
     */
    public function getDashboardSummary()
    {
        // Vérification admin
        $adminCheck = $this->checkAdmin();
        if ($adminCheck) return $adminCheck;
        
        try {
            $summary = [
                'utilisateurs_actifs_aujourd_hui' => SessionRespiration::whereDate('date_debut', today())
                    ->distinct('id_utilisateur')
                    ->count(),
                'sessions_aujourd_hui' => SessionRespiration::whereDate('date_debut', today())->count(),
                'duree_moyenne_aujourd_hui' => round(
                    SessionRespiration::whereDate('date_debut', today())->avg('duree_reelle') ?? 0, 
                    2
                ),
                'nouveaux_utilisateurs_cette_semaine' => Utilisateur::where('date_creation', '>=', now()->subWeek())->count(),
                'croissance_utilisateurs' => [
                    'cette_semaine' => Utilisateur::where('date_creation', '>=', now()->subWeek())->count(),
                    'semaine_precedente' => Utilisateur::whereBetween('date_creation', [
                        now()->subWeeks(2), 
                        now()->subWeek()
                    ])->count()
                ]
            ];

            // Calculer le pourcentage de croissance
            if ($summary['croissance_utilisateurs']['semaine_precedente'] > 0) {
                $summary['croissance_utilisateurs']['pourcentage'] = round(
                    (($summary['croissance_utilisateurs']['cette_semaine'] - $summary['croissance_utilisateurs']['semaine_precedente']) 
                    / $summary['croissance_utilisateurs']['semaine_precedente']) * 100, 
                    2
                );
            } else {
                $summary['croissance_utilisateurs']['pourcentage'] = $summary['croissance_utilisateurs']['cette_semaine'] > 0 ? 100 : 0;
            }

            return response()->json([
                'success' => true,
                'data' => $summary,
                'generated_at' => now()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement du résumé dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}