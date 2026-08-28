<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('election_settings', function (Blueprint $table) {
            $table->string('election_title')->nullable()->after('election_id');
        });

        Schema::table('election_archives', function (Blueprint $table) {
            $table->string('election_title')->nullable()->after('election_name');
        });
    }

    public function down(): void
    {
        Schema::table('election_archives', function (Blueprint $table) {
            $table->dropColumn('election_title');
        });

        Schema::table('election_settings', function (Blueprint $table) {
            $table->dropColumn('election_title');
        });
    }
};
