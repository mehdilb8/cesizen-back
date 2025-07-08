<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContenuInformation extends Model
{
    protected $table = 'contenu_information';
    protected $primaryKey = 'id_contenu';
    public $timestamps = false;

    protected $fillable = [
        'titre', 'contenu', 'type_contenu', 'meta_description', 'date_creation',
        'date_modification', 'statut', 'ordre_affichage'
    ];

    protected $casts = [
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];
}