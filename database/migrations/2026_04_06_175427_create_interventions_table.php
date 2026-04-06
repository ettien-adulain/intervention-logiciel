<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requete_id')->constrained('requetes')->cascadeOnDelete();
            $table->foreignId('technicien_id')->constrained('utilisateurs')->restrictOnDelete();

            $table->text('rapport')->nullable();
            $table->text('pieces_utilisees')->nullable();

            $table->dateTime('heure_debut')->nullable();
            $table->dateTime('heure_fin')->nullable();

            $table->enum('statut', ['en_cours', 'terminee'])->default('en_cours');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
