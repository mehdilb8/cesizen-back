<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionRespiration extends Model
{
    protected $table = 'session_respiration';
    protected $primaryKey = 'id_session';
    public $timestamps = false;

    protected $fillable = [
        'id_utilisateur', 'id_exercice', 'date_debut', 'date_fin', 'nb_cycles_realises',
        'duree_totale_secondes', 'session_completee', 'ressenti_avant', 'ressenti_apres', 'commentaire'
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'session_completee' => 'boolean',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function exercice()
    {
        return $this->belongsTo(ExerciceRespiration::class, 'id_exercice', 'id_exercice');
    }
}