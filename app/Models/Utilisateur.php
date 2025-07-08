<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Utilisateur extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'utilisateur';
    protected $primaryKey = 'id_utilisateur';
    public $timestamps = false;

    protected $fillable = [
        'nom', 'prenom', 'email', 'mot_de_passe', 'date_naissance', 
        'date_creation', 'date_derniere_connexion', 'statut', 'id_role'
    ];

    protected $hidden = [
        'mot_de_passe',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_creation' => 'datetime',
        'date_derniere_connexion' => 'datetime',
    ];

    // Spécifier le nom du champ password pour Laravel
    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    public function sessions()
    {
        return $this->hasMany(SessionRespiration::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function favoris()
    {
        return $this->hasMany(FavoriExercice::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function statistiques()
    {
        return $this->hasMany(StatistiqueMensuelle::class, 'id_utilisateur', 'id_utilisateur');
    }
}