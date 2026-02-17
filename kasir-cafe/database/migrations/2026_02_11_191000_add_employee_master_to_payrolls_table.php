<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->foreignId('employee_master_id')
                ->nullable()
                ->after('employee_id')
                ->constrained('employees');
        });

        // Support payroll untuk karyawan non-user aplikasi.
        DB::statement('ALTER TABLE payrolls DROP FOREIGN KEY payrolls_employee_id_foreign');
        DB::statement('ALTER TABLE payrolls MODIFY employee_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE payrolls ADD CONSTRAINT payrolls_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES users(id)');

        Schema::table('payrolls', function (Blueprint $table) {
            $table->unique(['period_month', 'employee_master_id'], 'payrolls_period_employee_master_unique');
        });

        // Seed master karyawan dari users yang sudah ada (untuk transisi data lama).
        $now = now();
        $users = DB::table('users')->select('id', 'name')->get();
        foreach ($users as $user) {
            $existingEmployeeId = DB::table('employees')->where('user_id', $user->id)->value('id');
            if ($existingEmployeeId) {
                $employeeId = $existingEmployeeId;
            } else {
                $employeeId = DB::table('employees')->insertGetId([
                    'employee_code' => 'EMP-' . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
                    'name' => $user->name,
                    'position' => null,
                    'department' => null,
                    'uses_app' => 1,
                    'user_id' => $user->id,
                    'is_active' => 1,
                    'note' => 'Auto-generated from users',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('payrolls')
                ->where('employee_id', $user->id)
                ->whereNull('employee_master_id')
                ->update(['employee_master_id' => $employeeId]);
        }
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropUnique('payrolls_period_employee_master_unique');
            $table->dropConstrainedForeignId('employee_master_id');
        });

        DB::statement('ALTER TABLE payrolls DROP FOREIGN KEY payrolls_employee_id_foreign');
        DB::statement('ALTER TABLE payrolls MODIFY employee_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE payrolls ADD CONSTRAINT payrolls_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES users(id)');
    }
};
