<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exercice_respiration', function (Blueprint $table) {
            $table->id('id_exercice');
            $table->string('nom_exercice', 100);
            $table->text('description')->nullable();
            $table->integer('duree_inspiration')->comment('Durée en secondes');
            $table->integer('duree_apnee')->default(0)->comment('Durée en secondes');
            $table->integer('duree_expiration')->comment('Durée en secondes');
            $table->integer('nb_cycles_recommandes')->default(5);
            $table->enum('niveau_difficulte', ['débutant', 'intermédiaire', 'avancé'])->default('débutant');
            $table->string('couleur_theme', 7)->default('#4A90E2')->comment('Code couleur hexadécimal');
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->timestamp('date_creation')->useCurrent();
        });

        // Insérer quelques exercices par défaut
        DB::table('exercice_respiration')->insert([
            [
                'nom_exercice' => 'Respiration 4-4-4',
                'description' => 'Respiration équilibrée pour débuter',
                'duree_inspiration' => 4,
                'duree_apnee' => 4,
                'duree_expiration' => 4,
                'nb_cycles_recommandes' => 5,
                'niveau_difficulte' => 'débutant',
                'couleur_theme' => '#4A90E2',
                'date_creation' => now()
            ],
            [
                'nom_exercice' => 'Respiration 4-7-8',
                'description' => 'Technique relaxante pour le sommeil',
                'duree_inspiration' => 4,
                'duree_apnee' => 7,
                'duree_expiration' => 8,
                'nb_cycles_recommandes' => 4,
                'niveau_difficulte' => 'intermédiaire',
                'couleur_theme' => '#7B68EE',
                'date_creation' => now()
            ],
            [
                'nom_exercice' => 'Respiration Box',
                'description' => 'Respiration carrée pour la concentration',
                'duree_inspiration' => 4,
                'duree_apnee' => 4,
                'duree_expiration' => 4,
                'nb_cycles_recommandes' => 8,
                'niveau_difficulte' => 'intermédiaire',
                'couleur_theme' => '#32CD32',
                'date_creation' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercice_respiration');
    }
};
