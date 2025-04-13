<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_financials', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->string('school_year');
            $table->string('semester');
            $table->decimal('tuition_fee', 10, 2);
            $table->decimal('discount', 10, 2);
            $table->decimal('adjustment', 10, 2);
            $table->decimal('amount_paid', 10, 2);
            $table->decimal('balance', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_financials');
    }
};
