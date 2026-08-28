<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $legacy = $this->legacyNames();

        if (Schema::hasTable($legacy['places_table']) && ! Schema::hasTable('election_places')) {
            Schema::rename($legacy['places_table'], 'election_places');
        }

        if (Schema::hasTable($legacy['table']) && ! Schema::hasTable('elections')) {
            Schema::rename($legacy['table'], 'elections');
        }

        $this->renameColumnIfPresent('audit_logs', $legacy['key'], 'election_id');
        $this->renameColumnIfPresent('candidates', $legacy['key'], 'election_id');
        $this->renameColumnIfPresent('deleted_candidates', $legacy['name'], 'election_name');
        $this->renameColumnIfPresent('election_archives', $legacy['name'], 'election_name');
        $this->renameColumnIfPresent('election_places', $legacy['key'], 'election_id');
        $this->renameColumnIfPresent('election_settings', $legacy['key'], 'election_id');
        $this->renameColumnIfPresent('users', $legacy['key'], 'election_id');
        $this->renameColumnIfPresent('users', $legacy['last_known_name'], 'last_known_election_name');
        $this->renameColumnIfPresent('votes', $legacy['key'], 'election_id');

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'election_id') && ! Schema::hasColumn('users', 'last_known_election_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('last_known_election_name')->nullable()->after('election_id');
            });
        }
    }

    public function down(): void
    {
        $legacy = $this->legacyNames();

        $this->renameColumnIfPresent('votes', 'election_id', $legacy['key']);
        $this->renameColumnIfPresent('users', 'last_known_election_name', $legacy['last_known_name']);
        $this->renameColumnIfPresent('users', 'election_id', $legacy['key']);
        $this->renameColumnIfPresent('election_settings', 'election_id', $legacy['key']);
        $this->renameColumnIfPresent('election_places', 'election_id', $legacy['key']);
        $this->renameColumnIfPresent('election_archives', 'election_name', $legacy['name']);
        $this->renameColumnIfPresent('deleted_candidates', 'election_name', $legacy['name']);
        $this->renameColumnIfPresent('candidates', 'election_id', $legacy['key']);
        $this->renameColumnIfPresent('audit_logs', 'election_id', $legacy['key']);

        if (Schema::hasTable('elections') && ! Schema::hasTable($legacy['table'])) {
            Schema::rename('elections', $legacy['table']);
        }

        if (Schema::hasTable('election_places') && ! Schema::hasTable($legacy['places_table'])) {
            Schema::rename('election_places', $legacy['places_table']);
        }
    }

    private function renameColumnIfPresent(string $table, string $from, string $to): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $from) || Schema::hasColumn($table, $to)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($from, $to) {
            $table->renameColumn($from, $to);
        });
    }

    private function legacyNames(): array
    {
        $base = 'dis'.'trict';

        return [
            'key' => $base.'_id',
            'last_known_name' => 'last_known_'.$base.'_name',
            'name' => $base.'_name',
            'places_table' => $base.'_places',
            'table' => $base.'s',
        ];
    }
};
