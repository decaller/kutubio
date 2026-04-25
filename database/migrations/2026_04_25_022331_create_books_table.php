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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('authors_display')->nullable();
            $table->string('isbn13', 13)->nullable()->index();
            $table->string('publisher')->nullable();
            $table->unsignedInteger('page_count')->nullable();
            $table->text('synopsis')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('approved_metadata_revision_id')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
