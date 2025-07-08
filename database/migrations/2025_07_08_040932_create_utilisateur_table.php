<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('utilisateur', function (Blueprint $table) {
            $table->id('id_utilisateur');
            $table->string('nom', 50);
            $table->string('prenom', 50);
            $table->string('email', 100)->unique();
            $table->string('mot_de_passe');
            $table->date('date_naissance')->nullable();
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_derniere_connexion')->nullable();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->unsignedBigInteger('id_role')->default(1);
            
            $table->foreign('id_role')->references('id_role')->on('role');
        });

        // Insérer des utilisateurs de test
        DB::table('utilisateur')->insert([
            [
                'nom' => 'Admin',
                'prenom' => 'Test',
                'email' => 'admin@cesizen.com',
                'mot_de_passe' => Hash::make('password'),
                'id_role' => 2,
                'statut' => 'actif',
                'date_creation' => now()
            ],
            [
                'nom' => 'User',
                'prenom' => 'Test',
                'email' => 'user@cesizen.com',
                'mot_de_passe' => Hash::make('password'),
                'id_role' => 1,
                'statut' => 'actif',
                'date_creation' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateur');
    }
};
