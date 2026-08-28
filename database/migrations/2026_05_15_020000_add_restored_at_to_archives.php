<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('election_archives', function (Blueprint $table) {
            $table->timestamp('restored_at')->nullable()->after('deleted_at');
        });

        Schema::table('deleted_candidates', function (Blueprint $table) {
            $table->timestamp('restored_at')->nullable()->after('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('deleted_candidates', function (Blueprint $table) {
            $table->dropColumn('restored_at');
        });

        Schema::table('election_archives', function (Blueprint $table) {
            $table->dropColumn('restored_at');
        });
    }
};
