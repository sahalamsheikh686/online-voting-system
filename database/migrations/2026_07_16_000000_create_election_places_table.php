<?php

use App\Models\Election;
use Illuminate\Database\QueryException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('election_places') || Schema::hasTable($this->legacyPlacesTable())) {
            return;
        }

        Schema::create('election_places', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        if (Schema::getConnection()->getDriverName() !== 'sqlite' && $this->hasIndex('elections', 'elections_name_unique')) {
            Schema::table('elections', function (Blueprint $table) {
                $table->dropUnique('elections_name_unique');
            });
        }

        if (Schema::hasTable('elections')) {
            Election::query()
                ->whereDoesntHave('place')
                ->each(function (Election $election) {
                    DB::table('election_places')->insert([
                        'election_id' => $election->id,
                        'name' => 'Default Place',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('election_places');

        if (Schema::getConnection()->getDriverName() !== 'sqlite' && ! $this->hasIndex('elections', 'elections_name_unique')) {
            Schema::table('elections', function (Blueprint $table) {
                $table->unique('name');
            });
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        try {
            return DB::table('information_schema.statistics')
                ->where('table_schema', $database)
                ->where('table_name', $table)
                ->where('index_name', $index)
                ->exists();
        } catch (QueryException) {
            return false;
        }
    }

    private function legacyPlacesTable(): string
    {
        return 'dis'.'trict_places';
    }
};
