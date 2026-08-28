<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('elections') || Schema::hasTable($this->legacyTable())) {
            return;
        }

        Schema::create('elections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elections');
    }

    private function legacyTable(): string
    {
        return 'dis'.'tricts';
    }
};
