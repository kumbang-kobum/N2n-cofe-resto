<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('late_tolerance_minutes')->default(0);
            $table->unsignedInteger('overtime_after_minutes')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('default_shift_id')->nullable()->after('department')->constrained('attendance_shifts')->nullOnDelete();
            $table->string('face_reference_path')->nullable()->after('default_shift_id');
        });

        Schema::create('attendance_late_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('min_minutes');
            $table->unsignedInteger('max_minutes')->nullable();
            $table->decimal('deduction_amount', 18, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('attendance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('attendance_shifts')->nullOnDelete();
            $table->date('schedule_date');
            $table->boolean('is_day_off')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'schedule_date']);
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('type', 20);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('PENDING');
            $table->text('reason')->nullable();
            $table->text('approval_note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->foreignId('scheduled_shift_id')->nullable()->constrained('attendance_shifts')->nullOnDelete();
            $table->timestamp('clock_in_at')->nullable();
            $table->timestamp('clock_out_at')->nullable();
            $table->string('status', 20)->default('PRESENT');
            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('early_leave_minutes')->default(0);
            $table->unsignedInteger('overtime_minutes')->default(0);
            $table->decimal('late_deduction_amount', 18, 2)->default(0);
            $table->string('verification_status', 20)->default('MANUAL');
            $table->string('clock_in_photo_path')->nullable();
            $table->string('clock_out_photo_path')->nullable();
            $table->foreignId('payroll_id')->nullable()->constrained('payrolls')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('attendance_schedules');
        Schema::dropIfExists('attendance_late_rules');
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('default_shift_id');
            $table->dropColumn('face_reference_path');
        });
        Schema::dropIfExists('attendance_shifts');
    }
};
