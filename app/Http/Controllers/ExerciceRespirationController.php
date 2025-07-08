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
    

    public function userSessions()
{
    $sessions = SessionRespiration::with('exercice')
        ->where('id_utilisateur', auth()->id())
        ->orderBy('date_debut', 'desc')
        ->get();
    return response()->json($sessions);
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
}
