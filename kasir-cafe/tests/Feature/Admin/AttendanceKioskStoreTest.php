<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceKioskStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_kiosk_store_saves_selfie_to_public_disk(): void
    {
        Storage::fake('public');

        $employee = Employee::create([
            'employee_code' => 'EMP-001',
            'name' => 'Petugas Test',
            'is_active' => true,
        ]);

        $this->post(route('attendance.public_kiosk.store'), [
            'employee_id' => $employee->id,
            'action_type' => 'CLOCK_IN',
            'selfie_image' => $this->tinyPngDataUrl(),
            'verification_status' => 'PHOTO_ONLY',
        ])->assertRedirect();

        $attendance = Attendance::firstOrFail();

        $this->assertSame($employee->id, $attendance->employee_id);
        $this->assertNotNull($attendance->clock_in_at);
        $this->assertNotNull($attendance->clock_in_photo_path);
        Storage::disk('public')->assertExists($attendance->clock_in_photo_path);
    }

    public function test_kiosk_store_rejects_invalid_selfie_without_server_error(): void
    {
        Storage::fake('public');

        $employee = Employee::create([
            'employee_code' => 'EMP-002',
            'name' => 'Petugas Test Dua',
            'is_active' => true,
        ]);

        $this->from(route('attendance.public_kiosk'))->post(route('attendance.public_kiosk.store'), [
            'employee_id' => $employee->id,
            'action_type' => 'CLOCK_IN',
            'selfie_image' => 'bukan-data-url-foto',
            'verification_status' => 'PHOTO_ONLY',
        ])
            ->assertRedirect(route('attendance.public_kiosk'))
            ->assertSessionHasErrors('selfie_image');

        $this->assertSame(0, Attendance::count());
    }

    private function tinyPngDataUrl(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=';
    }
}
