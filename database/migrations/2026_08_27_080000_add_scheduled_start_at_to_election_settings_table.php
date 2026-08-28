<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('election_settings', 'scheduled_start_at')) {
            Schema::table('election_settings', function (Blueprint $table) {
                $table->timestamp('scheduled_start_at')->nullable()->after('started_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('election_settings', 'scheduled_start_at')) {
            Schema::table('election_settings', function (Blueprint $table) {
                $table->dropColumn('scheduled_start_at');
            });
        }
    }
};
