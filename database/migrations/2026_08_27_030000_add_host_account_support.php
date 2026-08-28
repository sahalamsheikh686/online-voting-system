<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'email')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('email')->nullable()->unique()->after('contact_number');
            });
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','user','host') NOT NULL DEFAULT 'user'");
        }

        if (! Schema::hasTable('host_profiles')) {
            Schema::create('host_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('reason_type');
                $table->text('reason_message');
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('elections', 'host_id')) {
            Schema::table('elections', function (Blueprint $table) {
                $table->foreignId('host_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            });
        }

        if (Schema::hasTable('election_archives') && ! Schema::hasColumn('election_archives', 'host_id')) {
            Schema::table('election_archives', function (Blueprint $table) {
                $table->foreignId('host_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('election_archives') && Schema::hasColumn('election_archives', 'host_id')) {
            Schema::table('election_archives', function (Blueprint $table) {
                $table->dropForeign(['host_id']);
                $table->dropColumn('host_id');
            });
        }

        if (Schema::hasColumn('elections', 'host_id')) {
            Schema::table('elections', function (Blueprint $table) {
                $table->dropForeign(['host_id']);
                $table->dropColumn('host_id');
            });
        }

        Schema::dropIfExists('host_profiles');

        if (Schema::hasColumn('users', 'email')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['email']);
                $table->dropColumn('email');
            });
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','user') NOT NULL DEFAULT 'user'");
        }
    }
};
