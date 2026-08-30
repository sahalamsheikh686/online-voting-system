<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('candidates', 'party')) {
            Schema::table('candidates', function (Blueprint $table) {
                $table->dropColumn('party');
            });
        }

        if (Schema::hasColumn('deleted_candidates', 'party')) {
            Schema::table('deleted_candidates', function (Blueprint $table) {
                $table->dropColumn('party');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('candidates', 'party')) {
            Schema::table('candidates', function (Blueprint $table) {
                $table->string('party')->nullable()->after('name');
            });
        }

        if (! Schema::hasColumn('deleted_candidates', 'party')) {
            Schema::table('deleted_candidates', function (Blueprint $table) {
                $table->string('party')->nullable()->after('candidate_name');
            });
        }
    }
};
