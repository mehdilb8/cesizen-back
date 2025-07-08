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
        Schema::create('role', function (Blueprint $table) {
            $table->id('id_role');
            $table->string('nom_role', 50);
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->timestamp('date_creation')->useCurrent();
        });

        // Insérer les rôles par défaut
        DB::table('role')->insert([
            [
                'id_role' => 1,
                'nom_role' => 'Utilisateur',
                'description' => 'Utilisateur standard',
                'permissions' => json_encode(['read_exercises', 'create_sessions']),
                'date_creation' => now()
            ],
            [
                'id_role' => 2,
                'nom_role' => 'Administrateur',
                'description' => 'Administrateur du système',
                'permissions' => json_encode(['*']),
                'date_creation' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role');
    }
};
