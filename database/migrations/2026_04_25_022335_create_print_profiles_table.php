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
        Schema::create('print_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('page_width_mm', 8, 2);
            $table->decimal('page_height_mm', 8, 2);
            $table->unsignedSmallInteger('grid_columns');
            $table->unsignedSmallInteger('grid_rows');
            $table->decimal('offset_x_mm', 8, 2)->default(0);
            $table->decimal('offset_y_mm', 8, 2)->default(0);
            $table->decimal('slot_width_mm', 8, 2);
            $table->decimal('slot_height_mm', 8, 2);
            $table->boolean('is_default')->default(false)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_profiles');
    }
};
