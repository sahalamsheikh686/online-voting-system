<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', $this->legacyLastKnownName()) || ! Schema::hasColumn('users', 'election_id')) {
            return;
        }

        if (! Schema::hasColumn('users', 'last_known_election_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('last_known_election_name')->nullable()->after('election_id');
            });
        }

        if (! Schema::hasTable('elections') || ! Schema::hasColumn('users', 'election_id')) {
            return;
        }

        DB::table('users')
            ->join('elections', 'elections.id', '=', 'users.election_id')
            ->select('users.id as user_id', 'elections.name as election_name')
            ->get()
            ->each(function ($row) {
                DB::table('users')
                    ->where('id', $row->user_id)
                    ->update(['last_known_election_name' => $row->election_name]);
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'last_known_election_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('last_known_election_name');
            });
        }
    }

    private function legacyLastKnownName(): string
    {
        return 'last_known_dis'.'trict_name';
    }
};
