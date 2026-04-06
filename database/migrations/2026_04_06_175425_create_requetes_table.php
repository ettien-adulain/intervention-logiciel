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
        Schema::create('requetes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('utilisateurs')->restrictOnDelete();
            $table->foreignId('technicien_id')->nullable()->constrained('utilisateurs')->nullOnDelete();

            $table->string('titre')->nullable();
            $table->text('description')->nullable();

            $table->enum('urgence', ['faible', 'moyenne', 'elevee'])->default('moyenne');

            $table->enum('statut', [
                'ouverte', 'en_attente', 'planifiee', 'en_cours', 'terminee', 'cloturee',
            ])->default('ouverte');

            $table->timestamp('date_creation')->useCurrent();
            $table->dateTime('date_planification')->nullable();
            $table->dateTime('date_intervention')->nullable();
            $table->dateTime('date_fin')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->index(['client_id', 'date_creation']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requetes');
    }
};
