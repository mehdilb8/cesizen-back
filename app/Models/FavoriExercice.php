<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoriExercice extends Model
{
    protected $table = 'favori_exercice';
    protected $primaryKey = 'id_favori';
    public $timestamps = false;

    protected $fillable = [
        'id_utilisateur', 'id_exercice', 'date_ajout'
    ];

    protected $casts = [
        'date_ajout' => 'datetime',
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