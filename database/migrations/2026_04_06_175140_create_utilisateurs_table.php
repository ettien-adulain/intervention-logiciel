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
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('nom')->nullable();
            $table->string('prenom')->nullable();
            $table->string('email', 191)->unique();
            $table->string('password');
            $table->rememberToken();
            $table->enum('role', ['super_admin', 'client_admin', 'client_user', 'technicien'])->default('client_user');
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};
