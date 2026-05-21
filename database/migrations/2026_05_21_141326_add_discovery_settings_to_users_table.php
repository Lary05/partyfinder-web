<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Csak akkor adjuk hozzá, ha MÉG NINCS ott
            if (!Schema::hasColumn('users', 'birth_date')) {
                $table->date('birth_date')->nullable();
            }
            if (!Schema::hasColumn('users', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable();
            }
            if (!Schema::hasColumn('users', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable();
            }
            if (!Schema::hasColumn('users', 'discovery_distance')) {
                $table->integer('discovery_distance')->default(50);
            }
            if (!Schema::hasColumn('users', 'discovery_min_age')) {
                $table->integer('discovery_min_age')->default(18);
            }
            if (!Schema::hasColumn('users', 'discovery_max_age')) {
                $table->integer('discovery_max_age')->default(60);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'birth_date', 'latitude', 'longitude', 
                'discovery_distance', 'discovery_min_age', 'discovery_max_age'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};