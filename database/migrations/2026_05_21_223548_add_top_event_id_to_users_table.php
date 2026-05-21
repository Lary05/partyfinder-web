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
            if (!Schema::hasColumn('users', 'top_event_id')) {
                $table->unsignedBigInteger('top_event_id')->nullable();
                $table->foreign('top_event_id')->references('id')->on('events')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'vibes')) {
                $table->json('vibes')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'top_event_id')) {
                $table->dropForeign(['top_event_id']);
                $table->dropColumn('top_event_id');
            }
            if (Schema::hasColumn('users', 'vibes')) {
                $table->dropColumn('vibes');
            }
        });
    }
};
