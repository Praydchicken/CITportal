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
       Schema::create('announcement_sections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_announcements_id')
                ->constrained('teacher_announcements')
                ->onDelete('cascade');

            $table->foreignId('section_id')
                ->constrained('sections')
                ->onDelete('cascade');

            $table->timestamps();
        });

    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_sections');
    }
};
