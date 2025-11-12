<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;

class AttendanceCorrectionTest extends TestCase
{
    /**
     * 承認待ちの修正申請が全て表示されている
     */
    public function test_pending_corrections_are_displayed_for_admin()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        AttendanceCorrection::factory()->create([
            'user_id' => $user1->id,
            'date' => '2025-11-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 0, // 承認待ち
        ]);
        AttendanceCorrection::factory()->create([
            'user_id' => $user2->id,
            'date' => '2025-11-02',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'status' => 0, // 承認待ち
        ]);

        $response = $this->actingAs($admin)->get(route('attendance.correction-list', ['status' => 0]));

        $response->assertStatus(200);
        $response->assertSeeText('承認待ち');
        $response->assertSeeText($user1->name);
        $response->assertSeeText($user2->name);
        $response->assertSeeText('2025/11/01');
        $response->assertSeeText('2025/11/02');
    }


    /**
     * 承認済みの修正申請が全て表示されている
     */
    public function test_approved_corrections_are_displayed_for_admin()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        AttendanceCorrection::factory()->create([
            'user_id' => $user1->id,
            'date' => '2025-11-03',
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
            'status' => 1, // 承認済み
        ]);
        AttendanceCorrection::factory()->create([
            'user_id' => $user2->id,
            'date' => '2025-11-04',
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
            'status' => 1, // 承認済み
        ]);

        $response = $this->actingAs($admin)->get(route('attendance.correction-list', ['status' => 1]));

        $response->assertStatus(200);
        $response->assertSeeText('承認済み');
        $response->assertSeeText($user1->name);
        $response->assertSeeText($user2->name);
        $response->assertSeeText('2025/11/03');
        $response->assertSeeText('2025/11/04');
    }


    /**
     * 修正申請の詳細内容が正しく表示されている
     */
    public function test_correction_detail_is_displayed_correctly()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create();

        $correction = AttendanceCorrection::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-11-04',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'remark' => '退勤時刻修正',
            'status' => 0, // 承認待ち
        ]);

        $response = $this->actingAs($admin)->get(route('admin.correction-approval.show', $correction->id));

        $response->assertStatus(200);
        $response->assertSeeText($user->name);
        $response->assertSeeText('2025年');
        $response->assertSeeText('11月04日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSeeText('退勤時刻修正');
    }


    /**
     * 修正申請の承認処理が正しく行われる
     */
    public function test_admin_can_approve_correction_and_update_attendance()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-11-05',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $correction = AttendanceCorrection::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'date' => '2025-11-05',
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
            'remark' => '出勤時刻の修正',
            'status' => 0, // 承認待ち
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.correction-approval.approve', $correction->id),
            ['action' => 'approve']
        );

        $response->assertRedirect();

        $attendance->refresh();
        $correction->refresh();

        $this->assertEquals('08:30:00', $attendance->clock_in);
        $this->assertEquals('17:30:00', $attendance->clock_out);
        $this->assertEquals(1, $correction->status);
    }
}
