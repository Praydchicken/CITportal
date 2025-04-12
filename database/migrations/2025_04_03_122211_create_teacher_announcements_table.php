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
        Schema::create('teacher_announcements', function (Blueprint $table) {
            $table->id(); // This creates an unsigned big integer primary key named "id"
            $table->string('title_announcement');
            $table->string('description_announcement');
            $table->timestamp('deadline_announcement')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_announcements');
    }
};
