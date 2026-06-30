<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('concept')->default('enrollment');
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_reference')->nullable()->unique();
            $table->date('due_date')->nullable();
            $table->date('paid_at')->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamps();

            $table->index(['student_id', 'concept', 'status']);
            $table->index(['enrollment_id', 'status']);
            $table->index(['due_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finances');
    }
};
