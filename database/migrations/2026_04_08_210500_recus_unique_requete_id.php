<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $requeteIds = DB::table('recus')
            ->select('requete_id')
            ->groupBy('requete_id')
            ->havingRaw('count(*) > 1')
            ->pluck('requete_id');

        foreach ($requeteIds as $requeteId) {
            $garder = (int) DB::table('recus')->where('requete_id', $requeteId)->max('id');
            DB::table('recus')->where('requete_id', $requeteId)->where('id', '!=', $garder)->delete();
        }

        Schema::table('recus', function (Blueprint $table) {
            $table->unique('requete_id');
        });
    }

    public function down(): void
    {
        Schema::table('recus', function (Blueprint $table) {
            $table->dropUnique(['requete_id']);
        });
    }
};
