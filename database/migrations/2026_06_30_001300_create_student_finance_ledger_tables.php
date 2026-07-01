<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_concepts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type', 40)->default('fee');
            $table->decimal('default_amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->boolean('is_required_for_enrollment')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index(['is_required_for_enrollment', 'is_active']);
        });

        Schema::create('student_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('financial_concept_id')->constrained()->restrictOnDelete();
            $table->decimal('original_amount', 12, 2);
            $table->decimal('adjustment_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['student_id', 'course_id', 'status']);
            $table->index(['financial_concept_id', 'status']);
            $table->index(['due_date', 'status']);
        });

        Schema::create('financial_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_charge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);
            $table->decimal('amount', 12, 2);
            $table->string('status', 30)->default('approved');
            $table->string('reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'type', 'status']);
            $table->index(['student_charge_id', 'status']);
        });

        Schema::create('student_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('unallocated_amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('payment_method', 50);
            $table->string('payment_reference')->unique();
            $table->date('paid_at');
            $table->string('status', 30)->default('confirmed');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['paid_at', 'status']);
            $table->index(['enrollment_id', 'status']);
        });

        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_charge_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->unique(['student_payment_id', 'student_charge_id']);
            $table->index(['student_charge_id']);
        });

        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_payment_id')->constrained()->cascadeOnDelete();
            $table->string('receipt_number')->unique();
            $table->string('file_path')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->string('status', 30)->default('issued');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('financial_holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('reason');
            $table->string('status', 30)->default('active');
            $table->timestamp('placed_at')->useCurrent();
            $table->timestamp('released_at')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('release_reason')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['course_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_holds');
        Schema::dropIfExists('payment_receipts');
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('student_payments');
        Schema::dropIfExists('financial_adjustments');
        Schema::dropIfExists('student_charges');
        Schema::dropIfExists('financial_concepts');
    }
};
