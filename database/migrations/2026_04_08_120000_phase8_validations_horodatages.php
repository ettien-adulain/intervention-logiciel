<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('validations', function (Blueprint $table) {
            $table->timestamp('client_arrivee_at')->nullable()->after('technicien_fin');
            $table->timestamp('client_fin_at')->nullable();
            $table->timestamp('technicien_fin_at')->nullable();
            $table->timestamp('client_intervention_en_cours_at')->nullable();
        });

        foreach (DB::table('validations')->orderBy('id')->cursor() as $row) {
            $base = $row->date_validation ?? $row->created_at;
            $updates = [];
            if ((bool) $row->client_arrivee) {
                $updates['client_arrivee_at'] = $base;
            }
            if ((bool) $row->client_fin) {
                $updates['client_fin_at'] = $base;
            }
            if ((bool) $row->technicien_fin) {
                $updates['technicien_fin_at'] = $base;
            }
            if ($updates !== []) {
                DB::table('validations')->where('id', $row->id)->update($updates);
            }
        }

        $groups = DB::table('validations')->orderBy('id')->get()->groupBy('requete_id');
        foreach ($groups as $items) {
            if ($items->count() < 2) {
                continue;
            }
            $keeperId = (int) $items->sortBy('id')->first()->id;
            $merged = [
                'client_arrivee_at' => null,
                'client_fin_at' => null,
                'technicien_fin_at' => null,
                'client_intervention_en_cours_at' => null,
            ];
            foreach ($items as $r) {
                foreach (array_keys($merged) as $col) {
                    $v = $r->{$col};
                    if ($v === null) {
                        continue;
                    }
                    if ($merged[$col] === null || $v < $merged[$col]) {
                        $merged[$col] = $v;
                    }
                }
            }
            DB::table('validations')->where('id', $keeperId)->update($merged);
            $otherIds = $items->pluck('id')->map(fn ($id) => (int) $id)->filter(fn (int $id) => $id !== $keeperId);
            DB::table('validations')->whereIn('id', $otherIds)->delete();
        }

        Schema::table('validations', function (Blueprint $table) {
            $table->unique('requete_id');
            $table->dropColumn(['client_arrivee', 'client_fin', 'technicien_fin', 'date_validation']);
        });
    }

    public function down(): void
    {
        Schema::table('validations', function (Blueprint $table) {
            $table->dropUnique(['requete_id']);
        });

        Schema::table('validations', function (Blueprint $table) {
            $table->boolean('client_arrivee')->default(false);
            $table->boolean('client_fin')->default(false);
            $table->boolean('technicien_fin')->default(false);
            $table->timestamp('date_validation')->useCurrent();
        });

        foreach (DB::table('validations')->orderBy('id')->cursor() as $row) {
            $updates = [
                'client_arrivee' => $row->client_arrivee_at !== null,
                'client_fin' => $row->client_fin_at !== null,
                'technicien_fin' => $row->technicien_fin_at !== null,
                'date_validation' => $row->updated_at ?? $row->created_at,
            ];
            DB::table('validations')->where('id', $row->id)->update($updates);
        }

        Schema::table('validations', function (Blueprint $table) {
            $table->dropColumn([
                'client_arrivee_at',
                'client_fin_at',
                'technicien_fin_at',
                'client_intervention_en_cours_at',
            ]);
        });
    }
};
