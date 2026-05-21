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
        Schema::table('users', function (Blueprint $table) {
            // A gender és discovery_gender oszlopok hozzáadása, ha még nincsenek ott
            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'discovery_gender')) {
                $table->string('discovery_gender')->nullable()->after('discovery_max_age');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'gender')) {
                $table->dropColumn('gender');
            }
            if (Schema::hasColumn('users', 'discovery_gender')) {
                $table->dropColumn('discovery_gender');
            }
        });
    }
};