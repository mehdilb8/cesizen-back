<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExerciceRespiration;
use App\Models\FavoriExercice;
use App\Models\SessionRespiration;
use App\Models\StatistiqueMensuelle;


class ExerciceRespirationController extends Controller
{
    public function index()
    {
        return ExerciceRespiration::where('statut', 'actif')->get();
    }

    public function show($id)
    {
        return ExerciceRespiration::findOrFail($id);
    }

    public function addFavori(Request $request, $id)
    {
        $favori = FavoriExercice::firstOrCreate([
            'id_utilisateur' => $request->user()->id_utilisateur,
            'id_exercice' => $id,
        ]);
        return response()->json(['message' => 'Exercice ajouté aux favoris']);
    }

    public function removeFavori(Request $request, $id)
    {
        FavoriExercice::where('id_utilisateur', $request->user()->id_utilisateur)
            ->where('id_exercice', $id)
            ->delete();
        return response()->json(['message' => 'Exercice retiré des favoris']);
    }

    public function favoris(Request $request)
    {
        return $request->user()->favoris()->with('exercice')->get();
    }

    public function startSession(Request $request)
{
    $request->validate([
        'id_exercice' => 'required|exists:exercice_respiration,id_exercice',
        'nb_cycles_realises' => 'nullable|integer',
        'ressenti_avant' => 'nullable|integer'
    ]);
    $session = SessionRespiration::create([
        'id_utilisateur' => auth()->id(),
        'id_exercice' => $request->id_exercice,
        'date_debut' => now(),
        'nb_cycles_realises' => $request->nb_cycles_realises,
        'ressenti_avant' => $request->ressenti_avant,
        'session_completee' => false
    ]);
    return response()->json($session, 201);
}

    public function endSession(Request $request, $id)
{
    $session = SessionRespiration::where('id_session', $id)
        ->where('id_utilisateur', auth()->id())
        ->firstOrFail();
    $session->update([
        'date_fin' => now(),
        'ressenti_apres' => $request->ressenti_apres,
        'commentaire' => $request->commentaire,
        'session_completee' => true,
        'duree_totale_secondes' => $session->date_debut ? now()->diffInSeconds($session->date_debut) : null
    ]);
    return response()->json($session);
}
    

    /**
     * Récupérer les sessions de respiration de l'utilisateur connecté
     */
    public function userSessions(Request $request)
    {
        try {
            $userId = auth()->user()->id_utilisateur;
            
            // Paramètres de pagination et filtrage
            $perPage = $request->get('per_page', 15);
            $sortBy = $request->get('sort_by', 'date_debut');
            $sortOrder = $request->get('sort_order', 'desc');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $exerciceId = $request->get('exercice_id');
            $sessionCompletee = $request->get('session_completee');

            // Query de base SANS eager loading problématique
            $query = \App\Models\SessionRespiration::where('id_utilisateur', $userId);

            // Filtres
            if ($dateFrom) {
                $query->whereDate('date_debut', '>=', $dateFrom);
            }
            
            if ($dateTo) {
                $query->whereDate('date_debut', '<=', $dateTo);
            }
            
            if ($exerciceId) {
                $query->where('id_exercice', $exerciceId);
            }
            
            if ($sessionCompletee !== null) {
                $query->where('session_completee', $sessionCompletee == 'true' || $sessionCompletee == '1');
            }

            // Tri
            $allowedSorts = ['date_debut', 'date_fin', 'duree_totale_secondes', 'nb_cycles_realises'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('date_debut', 'desc');
            }

            // Pagination
            $sessions = $query->paginate($perPage);

            // Transformation des données
            $sessionsData = $sessions->map(function ($session) {
                // Récupérer l'exercice manuellement pour éviter l'erreur
                $exercice = null;
                if ($session->id_exercice) {
                    try {
                        $exercice = \App\Models\ExerciceRespiration::find($session->id_exercice);
                    } catch (\Exception $e) {
                        // Ignorer si erreur
                    }
                }

                return [
                    'id_session' => $session->id_session,
                    'id_exercice' => $session->id_exercice,
                    'exercice' => [
                        'id_exercice' => $exercice?->id_exercice,
                        'nom_exercice' => $exercice ? $this->getExerciceName($exercice) : 'Exercice supprimé',
                        'details' => $exercice ? $this->getExerciceDetails($exercice) : null
                    ],
                    'date_debut' => $session->date_debut,
                    'date_debut_format' => $session->date_debut ? 
                        \Carbon\Carbon::parse($session->date_debut)->format('d/m/Y H:i') : null,
                    'date_fin' => $session->date_fin,
                    'date_fin_format' => $session->date_fin ? 
                        \Carbon\Carbon::parse($session->date_fin)->format('d/m/Y H:i') : null,
                    'duree_totale_secondes' => $session->duree_totale_secondes,
                    'duree_totale_minutes' => $session->duree_totale_secondes ? 
                        round($session->duree_totale_secondes / 60, 2) : 0,
                    'duree_format' => $this->formatDuree($session->duree_totale_secondes),
                    'nb_cycles_realises' => $session->nb_cycles_realises,
                    'session_completee' => $session->session_completee,
                    'ressenti_avant' => $session->ressenti_avant,
                    'ressenti_apres' => $session->ressenti_apres,
                    'commentaire' => $session->commentaire,
                    'temps_ecoule' => $session->date_debut ? 
                        \Carbon\Carbon::parse($session->date_debut)->diffForHumans() : null
                ];
            });

            // Statistiques rapides
            $stats = [
                'total_sessions' => \App\Models\SessionRespiration::where('id_utilisateur', $userId)->count(),
                'sessions_completees' => \App\Models\SessionRespiration::where('id_utilisateur', $userId)
                    ->where('session_completee', true)->count(),
                'temps_total_minutes' => round(
                    \App\Models\SessionRespiration::where('id_utilisateur', $userId)
                        ->sum('duree_totale_secondes') / 60, 2
                ),
                'moyenne_cycles' => round(
                    \App\Models\SessionRespiration::where('id_utilisateur', $userId)
                        ->avg('nb_cycles_realises'), 2
                ),
                'cette_semaine' => \App\Models\SessionRespiration::where('id_utilisateur', $userId)
                    ->where('date_debut', '>=', now()->startOfWeek())->count(),
                'ce_mois' => \App\Models\SessionRespiration::where('id_utilisateur', $userId)
                    ->where('date_debut', '>=', now()->startOfMonth())->count()
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'sessions' => $sessionsData,
                    'pagination' => [
                        'current_page' => $sessions->currentPage(),
                        'last_page' => $sessions->lastPage(),
                        'per_page' => $sessions->perPage(),
                        'total' => $sessions->total(),
                        'from' => $sessions->firstItem(),
                        'to' => $sessions->lastItem()
                    ],
                    'statistiques' => $stats,
                    'filtres_appliques' => [
                        'date_from' => $dateFrom,
                        'date_to' => $dateTo,
                        'exercice_id' => $exerciceId,
                        'session_completee' => $sessionCompletee,
                        'sort_by' => $sortBy,
                        'sort_order' => $sortOrder
                    ]
                ],
                'message' => 'Sessions récupérées avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des sessions',
                'error' => $e->getMessage(),
                'debug' => [
                    'user_id' => auth()->user()->id_utilisateur ?? 'non connecté',
                    'line' => $e->getLine(),
                    'file' => basename($e->getFile())
                ]
            ], 500);
        }
    }

    /**
     * Obtenir le nom de l'exercice selon la structure de votre table
     */
    private function getExerciceName($exercice)
    {
        // Essayer différents noms de colonnes possibles
        if (isset($exercice->nom)) {
            return $exercice->nom;
        } elseif (isset($exercice->nom_exercice)) {
            return $exercice->nom_exercice;
        } elseif (isset($exercice->titre)) {
            return $exercice->titre;
        } elseif (isset($exercice->libelle)) {
            return $exercice->libelle;
        } else {
            return 'Exercice #' . $exercice->id_exercice;
        }
    }

    /**
     * Obtenir les détails de l'exercice
     */
    private function getExerciceDetails($exercice)
    {
        $details = [];
        
        // Collecter toutes les propriétés disponibles
        $attributes = $exercice->getAttributes();
        
        foreach ($attributes as $key => $value) {
            if (!in_array($key, ['id_exercice', 'created_at', 'updated_at'])) {
                $details[$key] = $value;
            }
        }
        
        return $details;
    }

    /**
     * Formater la durée en secondes en format lisible
     */
    private function formatDuree($secondes)
    {
        if (!$secondes || $secondes <= 0) {
            return '0 sec';
        }

        $heures = floor($secondes / 3600);
        $minutes = floor(($secondes % 3600) / 60);
        $sec = $secondes % 60;

        $format = [];
        
        if ($heures > 0) {
            $format[] = $heures . 'h';
        }
        
        if ($minutes > 0) {
            $format[] = $minutes . 'min';
        }
        
        if ($sec > 0 || empty($format)) {
            $format[] = $sec . 'sec';
        }

        return implode(' ', $format);
    }
public function sessionsParExercice($id_exercice)
{
    $sessions = SessionRespiration::where('id_utilisateur', auth()->id())
        ->where('id_exercice', $id_exercice)
        ->orderBy('date_debut', 'desc')
        ->get();
    return response()->json($sessions);
}

    public function userStats(Request $request)
    {
        return $request->user()->statistiques;
    }

    /**
     * Récupérer les statistiques mensuelles de l'utilisateur connecté
     */
    public function monthlyStats()
    {
        try {
            $userId = auth()->user()->id_utilisateur;
            
            // Récupérer les statistiques mensuelles de l'utilisateur
            $monthlyStats = \App\Models\StatistiqueMensuelle::where('id_utilisateur', $userId)
                ->orderBy('mois_annee', 'desc')
                ->get()
                ->map(function ($stat) {
                    return [
                        'id_statistique' => $stat->id_statistique,
                        'mois_annee' => $stat->mois_annee,
                        'mois_format' => $this->formatMoisAnnee($stat->mois_annee),
                        'nb_sessions' => $stat->nb_sessions,
                        'temps_total_minutes' => $stat->temps_total_minutes,
                        'temps_total_heures' => round($stat->temps_total_minutes / 60, 2),
                        'progression_score' => $stat->progression_score,
                        'date_calcul' => $stat->date_calcul,
                        'moyenne_session' => $stat->nb_sessions > 0 ? 
                            round($stat->temps_total_minutes / $stat->nb_sessions, 2) : 0
                    ];
                });

            // Calculer les totaux
            $totaux = [
                'total_sessions' => $monthlyStats->sum('nb_sessions'),
                'total_minutes' => $monthlyStats->sum('temps_total_minutes'),
                'total_heures' => round($monthlyStats->sum('temps_total_minutes') / 60, 2),
                'score_moyen' => $monthlyStats->avg('progression_score') ? 
                    round($monthlyStats->avg('progression_score'), 2) : 0,
                'nombre_mois' => $monthlyStats->count()
            ];

            // Statistiques de progression
            $progression = $this->calculateProgression($monthlyStats);

            return response()->json([
                'success' => true,
                'data' => [
                    'statistiques_mensuelles' => $monthlyStats,
                    'totaux' => $totaux,
                    'progression' => $progression,
                    'derniere_mise_a_jour' => $monthlyStats->first()?->date_calcul ?? null
                ],
                'message' => 'Statistiques mensuelles récupérées avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques mensuelles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formater le mois-année pour l'affichage
     */
    private function formatMoisAnnee($moisAnnee)
    {
        try {
            // Créer une date à partir du format YYYY-MM
            $date = \Carbon\Carbon::createFromFormat('Y-m', $moisAnnee);
            
            $moisFrancais = [
                1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
                5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
                9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre'
            ];
            
            return [
                'mois_nom' => $date->format('F'),
                'mois_nom_fr' => $moisFrancais[$date->month],
                'annee' => $date->format('Y'),
                'mois_court' => $date->format('M'),
                'affichage' => $moisFrancais[$date->month] . ' ' . $date->format('Y')
            ];
        } catch (\Exception $e) {
            return [
                'mois_nom' => 'Inconnu',
                'mois_nom_fr' => 'Inconnu',
                'annee' => 'Inconnu',
                'mois_court' => 'Inconnu',
                'affichage' => $moisAnnee
            ];
        }
    }

    /**
     * Calculer les tendances de progression
     */
    private function calculateProgression($monthlyStats)
    {
        if ($monthlyStats->count() < 2) {
            return [
                'tendance' => 'insuffisant_donnees',
                'evolution_sessions' => 0,
                'evolution_temps' => 0,
                'evolution_score' => 0
            ];
        }

        $dernierMois = $monthlyStats->first();
        $avantDernierMois = $monthlyStats->skip(1)->first();

        if (!$dernierMois || !$avantDernierMois) {
            return [
                'tendance' => 'insuffisant_donnees',
                'evolution_sessions' => 0,
                'evolution_temps' => 0,
                'evolution_score' => 0
            ];
        }

        // Calcul des évolutions en pourcentage
        $evolutionSessions = $avantDernierMois['nb_sessions'] > 0 ? 
            round((($dernierMois['nb_sessions'] - $avantDernierMois['nb_sessions']) / $avantDernierMois['nb_sessions']) * 100, 2) : 
            ($dernierMois['nb_sessions'] > 0 ? 100 : 0);

        $evolutionTemps = $avantDernierMois['temps_total_minutes'] > 0 ? 
            round((($dernierMois['temps_total_minutes'] - $avantDernierMois['temps_total_minutes']) / $avantDernierMois['temps_total_minutes']) * 100, 2) : 
            ($dernierMois['temps_total_minutes'] > 0 ? 100 : 0);

        $evolutionScore = $avantDernierMois['progression_score'] > 0 ? 
            round((($dernierMois['progression_score'] - $avantDernierMois['progression_score']) / $avantDernierMois['progression_score']) * 100, 2) : 
            ($dernierMois['progression_score'] > 0 ? 100 : 0);

        // Déterminer la tendance générale
        $tendance = 'stable';
        if ($evolutionSessions > 10 || $evolutionTemps > 10 || $evolutionScore > 10) {
            $tendance = 'croissance';
        } elseif ($evolutionSessions < -10 || $evolutionTemps < -10 || $evolutionScore < -10) {
            $tendance = 'declin';
        }

        return [
            'tendance' => $tendance,
            'evolution_sessions' => $evolutionSessions,
            'evolution_temps' => $evolutionTemps,
            'evolution_score' => $evolutionScore,
            'dernier_mois' => $dernierMois['mois_annee'],
            'avant_dernier_mois' => $avantDernierMois['mois_annee']
        ];
        
    }

    
    
}
