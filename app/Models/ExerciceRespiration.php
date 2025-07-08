<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciceRespiration extends Model
{
    protected $table = 'exercice_respiration';
    protected $primaryKey = 'id_exercice';
    public $timestamps = false;

    protected $fillable = [
        'nom_exercice', 'description', 'duree_inspiration', 'duree_apnee', 'duree_expiration',
        'nb_cycles_recommandes', 'niveau_difficulte', 'couleur_theme', 'statut', 'date_creation'
    ];

    protected $casts = [
        'date_creation' => 'datetime',
    ];

    public function sessions()
    {
        return $this->hasMany(SessionRespiration::class, 'id_exercice', 'id_exercice');
    }

    public function favoris()
    {
        return $this->hasMany(FavoriExercice::class, 'id_exercice', 'id_exercice');
    }
}