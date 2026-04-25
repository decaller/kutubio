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
        Schema::create('metadata_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('capture_session_id')->nullable()->constrained()->nullOnDelete();
            $table->string('revision_type')->index();
            $table->string('source_stage')->index();
            $table->string('source_actor_type')->nullable();
            $table->unsignedBigInteger('source_actor_id')->nullable();
            $table->decimal('confidence_score', 5, 4)->nullable();
            $table->json('payload');
            $table->json('diff_from_previous')->nullable();
            $table->json('source_meta')->nullable();
            $table->timestamps();

            $table->index(['book_id', 'created_at']);
            $table->index(['capture_session_id', 'created_at']);
            $table->index(['source_actor_type', 'source_actor_id']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table
                ->foreign('approved_metadata_revision_id')
                ->references('id')
                ->on('metadata_revisions')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['approved_metadata_revision_id']);
        });

        Schema::dropIfExists('metadata_revisions');
    }
};
