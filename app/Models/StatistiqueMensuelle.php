<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatistiqueMensuelle extends Model
{
    protected $table = 'statistique_mensuelle';
    protected $primaryKey = 'id_statistique';
    public $timestamps = false;

    protected $fillable = [
        'id_utilisateur', 'mois_annee', 'nb_sessions', 'temps_total_minutes', 'progression_score', 'date_calcul'
    ];

    protected $casts = [
        'date_calcul' => 'datetime',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }
}