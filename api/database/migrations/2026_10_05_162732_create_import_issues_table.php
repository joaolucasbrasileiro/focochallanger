<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('import_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_run_id')->constrained()->cascadeOnDelete();
            $table->string('source', 32);
            $table->string('external_identifier')->nullable();
            $table->string('status', 32)->default('incomplete');
            $table->string('error_code', 100);
            $table->text('error_message');
            $table->longText('raw_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['source', 'status']);
            $table->index('external_identifier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_issues');
    }
};
