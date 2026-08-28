<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('election_settings') || Schema::hasColumn('election_settings', 'election_id') || Schema::hasColumn('election_settings', $this->legacyKey())) {
            return;
        }

        Schema::table('election_settings', function (Blueprint $table) {
            $table->foreignId('election_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('election_settings') || ! Schema::hasColumn('election_settings', 'election_id')) {
            return;
        }

        Schema::table('election_settings', function (Blueprint $table) {
            $table->dropForeign(['election_id']);
            $table->dropColumn('election_id');
        });
    }

    private function legacyKey(): string
    {
        return 'dis'.'trict_id';
    }
};
