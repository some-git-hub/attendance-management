<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

class AttendanceDetailTest extends TestCase
{
    /**
     * 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
     */
    public function test_attendance_detail_displays_logged_in_user_name()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSeeText($user->name);
    }


    /**
     * 勤怠詳細画面の「日付」が選択した日付になっている
     */
    public function test_attendance_detail_displays_selected_date()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSeeText('01月10日');
    }


    /**
     * 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_attendance_detail_displays_correct_clock_in_and_out_times()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-01-10',
            'clock_in' => '09:15:00',
            'clock_out' => '18:45:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('09:15');
        $response->assertSee('18:45');
    }


    /**
     * 「休憩」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_attendance_detail_displays_correct_break_times()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'rest_in' => '12:15:00',
            'rest_out' => '13:15:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('12:15');
        $response->assertSee('13:15');
    }
}
