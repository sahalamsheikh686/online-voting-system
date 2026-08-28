<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('elections', 'invite_token')) {
            Schema::table('elections', function (Blueprint $table) {
                $table->string('invite_token', 64)->nullable()->after('name');
            });
        }

        DB::table('elections')
            ->whereNull('invite_token')
            ->orWhere('invite_token', '')
            ->orderBy('id')
            ->get()
            ->each(function ($election) {
                do {
                    $token = Str::random(40);
                } while (DB::table('elections')->where('invite_token', $token)->exists());

                DB::table('elections')
                    ->where('id', $election->id)
                    ->update([
                        'invite_token' => $token,
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('elections', 'invite_token')) {
            Schema::table('elections', function (Blueprint $table) {
                $table->dropColumn('invite_token');
            });
        }
    }
};
