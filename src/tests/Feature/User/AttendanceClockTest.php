<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceClockTest extends TestCase
{
    /**
     * 出勤ボタンが正しく表示され押下すると勤務中になる
     */
    public function test_clock_in_button_displays_and_works_correctly()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSee('<button type="submit" class="button button-start">出勤</button>', false);

        $response = $this->actingAs($user)->post(route('attendance.start'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => '09:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertSeeText('出勤中');
    }


    /**
     * 出勤は一日一回のみ押下できる
     */
    public function test_user_can_clock_in_only_once_per_day()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => '09:15:00',
            'clock_out' => '18:15:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertDontSee('<button type="submit" class="button button-start">出勤</button>', false);
    }


    /**
     * 出勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_in_time_is_displayed_in_attendance_list()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('attendance.start'));

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSeeText('01/01');
        $response->assertSeeText('09:00');
    }


    /**
     * 退勤ボタンが正しく表示され押下すると退勤済になる
     */
    public function test_clock_out_button_displays_and_works_correctly()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSee('<button type="submit" class="button button-end">退勤</button>', false);

        $this->actingAs($user)->post(route('attendance.end'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
        ]);

        $updatedAttendance = Attendance::where('user_id', $user->id)->first();
        $this->assertNotNull($updatedAttendance->clock_out);

        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertSeeText('退勤済');
    }


    /**
     * 退勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_out_time_is_displayed_in_attendance_list()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '18:15:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list'));
        $response->assertStatus(200);
        $response->assertSeeText('18:15');
    }
}
