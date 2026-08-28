<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('election_settings', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('is_active');
        });

        Schema::create('election_archives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('election_name');
            $table->string('archive_reason')->default('deleted');
            $table->unsignedInteger('candidate_count')->default(0);
            $table->unsignedInteger('total_votes')->default(0);
            $table->timestamp('election_started_at')->nullable();
            $table->timestamp('election_ended_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->json('winners')->nullable();
            $table->json('position_results')->nullable();
            $table->timestamps();
        });

        Schema::create('deleted_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_archive_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('original_candidate_id')->nullable();
            $table->string('election_name');
            $table->string('candidate_name');
            $table->unsignedInteger('age')->nullable();
            $table->string('position')->nullable();
            $table->string('email')->nullable();
            $table->string('image_path')->nullable();
            $table->string('vision_path')->nullable();
            $table->unsignedInteger('vote_count')->default(0);
            $table->string('deleted_reason')->default('candidate_deleted');
            $table->timestamp('election_started_at')->nullable();
            $table->timestamp('election_ended_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deleted_candidates');
        Schema::dropIfExists('election_archives');

        Schema::table('election_settings', function (Blueprint $table) {
            $table->dropColumn('started_at');
        });
    }
};
