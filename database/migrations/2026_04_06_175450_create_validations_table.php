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
        Schema::create('validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requete_id')->constrained('requetes')->cascadeOnDelete();

            $table->boolean('client_arrivee')->default(false);
            $table->boolean('client_fin')->default(false);
            $table->boolean('technicien_fin')->default(false);

            $table->timestamp('date_validation')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validations');
    }
};
