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
        Schema::create('planifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requete_id')->constrained('requetes')->cascadeOnDelete();
            $table->foreignId('technicien_id')->constrained('utilisateurs')->restrictOnDelete();

            $table->dateTime('date_intervention');
            $table->text('message')->nullable();

            $table->enum('statut', ['planifiee', 'confirmee', 'annulee'])->default('planifiee');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planifications');
    }
};
