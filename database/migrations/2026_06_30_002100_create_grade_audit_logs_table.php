<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete();
            $table->decimal('old_grade', 8, 2)->nullable();
            $table->decimal('new_grade', 8, 2)->nullable();
            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30)->nullable();
            $table->text('reason');
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->index(['grade_id', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_audit_logs');
    }
};
