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
        // Table session_respiration
        Schema::create('session_respiration', function (Blueprint $table) {
            $table->id('id_session');
            $table->unsignedBigInteger('id_utilisateur');
            $table->unsignedBigInteger('id_exercice');
            $table->timestamp('date_debut');
            $table->timestamp('date_fin')->nullable();
            $table->integer('nb_cycles_realises')->default(0);
            $table->integer('duree_totale_secondes')->default(0);
            $table->boolean('session_completee')->default(false);
            $table->enum('ressenti_avant', ['très_stressé', 'stressé', 'neutre', 'calme', 'très_calme'])->nullable();
            $table->enum('ressenti_apres', ['très_stressé', 'stressé', 'neutre', 'calme', 'très_calme'])->nullable();
            $table->text('commentaire')->nullable();
            
            $table->foreign('id_utilisateur')->references('id_utilisateur')->on('utilisateur')->onDelete('cascade');
            $table->foreign('id_exercice')->references('id_exercice')->on('exercice_respiration')->onDelete('cascade');
        });

        // Table favori_exercice
        Schema::create('favori_exercice', function (Blueprint $table) {
            $table->id('id_favori');
            $table->unsignedBigInteger('id_utilisateur');
            $table->unsignedBigInteger('id_exercice');
            $table->timestamp('date_ajout')->useCurrent();
            
            $table->foreign('id_utilisateur')->references('id_utilisateur')->on('utilisateur')->onDelete('cascade');
            $table->foreign('id_exercice')->references('id_exercice')->on('exercice_respiration')->onDelete('cascade');
            $table->unique(['id_utilisateur', 'id_exercice'], 'unique_favori');
        });

        // Table statistique_mensuelle
        Schema::create('statistique_mensuelle', function (Blueprint $table) {
            $table->id('id_statistique');
            $table->unsignedBigInteger('id_utilisateur');
            $table->string('mois_annee', 7)->comment('Format YYYY-MM');
            $table->integer('nb_sessions')->default(0);
            $table->integer('temps_total_minutes')->default(0);
            $table->decimal('progression_score', 5, 2)->default(0.00)->comment('Score sur 100');
            $table->timestamp('date_calcul')->useCurrent();
            
            $table->foreign('id_utilisateur')->references('id_utilisateur')->on('utilisateur')->onDelete('cascade');
            $table->unique(['id_utilisateur', 'mois_annee'], 'unique_stat_mensuelle');
        });

        // Table contenu_information
        Schema::create('contenu_information', function (Blueprint $table) {
            $table->id('id_contenu');
            $table->string('titre', 200);
            $table->longText('contenu');
            $table->enum('type_contenu', ['article', 'conseil', 'guide', 'actualité'])->default('article');
            $table->string('meta_description', 300)->nullable();
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->useCurrent()->useCurrentOnUpdate();
            $table->enum('statut', ['publié', 'brouillon', 'archivé'])->default('brouillon');
            $table->integer('ordre_affichage')->default(0);
        });

        // Insérer du contenu de test
        DB::table('contenu_information')->insert([
            [
                'titre' => 'Bienfaits de la respiration consciente',
                'contenu' => 'La respiration consciente est une pratique millénaire qui offre de nombreux bienfaits pour votre santé physique et mentale...',
                'type_contenu' => 'article',
                'meta_description' => 'Découvrez les bienfaits de la respiration consciente sur votre bien-être',
                'statut' => 'publié',
                'date_creation' => now(),
                'date_modification' => now()
            ],
            [
                'titre' => 'Guide du débutant',
                'contenu' => 'Comment commencer votre pratique de respiration consciente ? Ce guide vous accompagne pas à pas...',
                'type_contenu' => 'guide',
                'meta_description' => 'Guide complet pour débuter la respiration consciente',
                'statut' => 'publié',
                'date_creation' => now(),
                'date_modification' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contenu_information');
        Schema::dropIfExists('statistique_mensuelle');
        Schema::dropIfExists('favori_exercice');
        Schema::dropIfExists('session_respiration');
    }
};
