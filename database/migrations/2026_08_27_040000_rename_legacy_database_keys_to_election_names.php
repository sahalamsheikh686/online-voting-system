<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $base = 'dis'.'trict';

        $this->renameForeignKey('audit_logs', "audit_logs_{$base}_id_foreign", 'audit_logs_election_id_foreign', 'election_id', 'elections', 'id', 'SET NULL');
        $this->renameForeignKey('candidates', "candidates_{$base}_id_foreign", 'candidates_election_id_foreign', 'election_id', 'elections', 'id', 'CASCADE');
        $this->renameForeignKey('election_settings', "election_settings_{$base}_id_foreign", 'election_settings_election_id_foreign', 'election_id', 'elections', 'id', 'CASCADE');
        $this->renameForeignKey('votes', "votes_{$base}_id_foreign", 'votes_election_id_foreign', 'election_id', 'elections', 'id', 'CASCADE');

        $this->dropForeignKeyIfExists('election_places', "{$base}_places_{$base}_id_foreign");
        $this->dropIndexIfExists('election_places', "{$base}_places_{$base}_id_unique");

        if (! $this->indexExists('election_places', 'election_places_election_id_unique')) {
            DB::statement('ALTER TABLE election_places ADD UNIQUE KEY election_places_election_id_unique (election_id)');
        }

        if (! $this->foreignKeyExists('election_places', 'election_places_election_id_foreign')) {
            DB::statement('ALTER TABLE election_places ADD CONSTRAINT election_places_election_id_foreign FOREIGN KEY (election_id) REFERENCES elections (id) ON DELETE CASCADE');
        }

        if (Schema::hasTable('candidates')) {
            DB::statement("ALTER TABLE candidates MODIFY position varchar(255) NOT NULL DEFAULT 'Election Representative'");
        }

        if (Schema::hasTable('audit_logs') && Schema::hasColumn('audit_logs', 'metadata')) {
            DB::table('audit_logs')
                ->where('metadata', 'like', '%'.$base.'%')
                ->orderBy('id')
                ->get(['id', 'metadata'])
                ->each(function ($log) use ($base) {
                    DB::table('audit_logs')
                        ->where('id', $log->id)
                        ->update(['metadata' => str_replace($base, 'election', (string) $log->metadata)]);
                });
        }
    }

    public function down(): void
    {
        //
    }

    private function renameForeignKey(string $table, string $oldName, string $newName, string $column, string $referencesTable, string $referencesColumn, string $onDelete): void
    {
        if (! $this->foreignKeyExists($table, $oldName) || $this->foreignKeyExists($table, $newName)) {
            return;
        }

        $this->dropForeignKeyIfExists($table, $oldName);
        $this->dropIndexIfExists($table, $oldName);

        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$newName} FOREIGN KEY ({$column}) REFERENCES {$referencesTable} ({$referencesColumn}) ON DELETE {$onDelete}");
    }

    private function dropForeignKeyIfExists(string $table, string $name): void
    {
        if ($this->foreignKeyExists($table, $name)) {
            DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$name}");
        }
    }

    private function dropIndexIfExists(string $table, string $name): void
    {
        if ($this->indexExists($table, $name)) {
            DB::statement("ALTER TABLE {$table} DROP INDEX {$name}");
        }
    }

    private function foreignKeyExists(string $table, string $name): bool
    {
        return DB::table('information_schema.table_constraints')
            ->where('constraint_schema', DB::connection()->getDatabaseName())
            ->where('table_name', $table)
            ->where('constraint_name', $name)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }

    private function indexExists(string $table, string $name): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::connection()->getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $name)
            ->exists();
    }
};
