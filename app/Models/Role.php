<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'role';
    protected $primaryKey = 'id_role';
    public $timestamps = false;

    protected $fillable = [
        'nom_role', 'description', 'permissions', 'date_creation'
    ];

    protected $casts = [
        'permissions' => 'array',
        'date_creation' => 'datetime',
    ];

    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class, 'id_role', 'id_role');
    }
}
