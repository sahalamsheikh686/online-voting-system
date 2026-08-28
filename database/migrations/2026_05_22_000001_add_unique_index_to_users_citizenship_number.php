<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'citizenship_number')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('citizenship_number');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'citizenship_number')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['citizenship_number']);
        });
    }
};
