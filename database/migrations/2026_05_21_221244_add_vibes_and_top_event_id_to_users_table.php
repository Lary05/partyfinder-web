<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Stores the user's selected vibes/tags as a JSON array, e.g. ["Techno","Night Owl"]
            $table->json('vibes')->nullable()->after('bio');

            // The user's pinned "top event" they plan to attend — nullable FK
            $table->unsignedBigInteger('top_event_id')->nullable()->after('vibes');

            // Soft FK — no hard constraint so events can be deleted without cascading
            $table->index('top_event_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['top_event_id']);
            $table->dropColumn(['vibes', 'top_event_id']);
        });
    }
};
