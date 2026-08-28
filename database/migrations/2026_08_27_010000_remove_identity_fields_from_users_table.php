<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'citizenship_number')) {
                $table->dropUnique(['citizenship_number']);
            }

            if (Schema::hasColumn('users', 'voter_id_number')) {
                $table->dropUnique(['voter_id_number']);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('users', 'citizenship_number') ? 'citizenship_number' : null,
                Schema::hasColumn('users', 'voter_id_number') ? 'voter_id_number' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'citizenship_number')) {
                $table->string('citizenship_number')->nullable()->after('last_known_election_name');
            }

            if (! Schema::hasColumn('users', 'voter_id_number')) {
                $table->string('voter_id_number')->nullable()->after('citizenship_number');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'citizenship_number')) {
                $table->unique('citizenship_number');
            }

            if (Schema::hasColumn('users', 'voter_id_number')) {
                $table->unique('voter_id_number');
            }
        });
    }
};
