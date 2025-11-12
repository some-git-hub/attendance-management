<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class AttendanceCreateTest extends TestCase
{
    /**
     * 現在の日時情報が正しい形式で表示される
     */
    public function test_attendance_page_displays_current_datetime()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSee('2025年01月01日');
        $response->assertSee('09:00');
    }


    /**
     * 勤務外の場合はステータスが「勤務外」と表示される
     */
    public function test_status_is_displayed_as_off_duty()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }


    /**
     * 出勤中の場合はステータスが「出勤中」と表示される
     */
    public function test_status_is_displayed_as_working()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id'   => $user->id,
            'date'      => Carbon::today(),
            'clock_in'  => '09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }


    /**
     * 休憩中の場合はステータスが「休憩中」と表示される
     */
    public function test_status_is_displayed_as_on_break()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'date'      => Carbon::today(),
            'clock_in'  => '09:00:00',
            'clock_out' => null,
        ]);

        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'rest_in'       => '12:00:00',
            'rest_out'      => null,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }


    /**
     * 退勤済の場合はステータスが「退勤済」と表示される
     */
    public function test_status_is_displayed_as_clocked_out()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id'   => $user->id,
            'date'      => Carbon::today(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
