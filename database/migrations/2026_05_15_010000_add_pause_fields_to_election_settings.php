<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('election_settings', function (Blueprint $table) {
            $table->timestamp('paused_at')->nullable()->after('started_at');
            $table->unsignedInteger('remaining_seconds')->nullable()->after('paused_at');
        });
    }

    public function down(): void
    {
        Schema::table('election_settings', function (Blueprint $table) {
            $table->dropColumn(['paused_at', 'remaining_seconds']);
        });
    }
};
