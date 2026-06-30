<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->foreignId('career_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('shift', 30)->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(['course_id', 'career_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
