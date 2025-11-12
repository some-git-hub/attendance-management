<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceRestTest extends TestCase
{
    /**
     * 休憩ボタンが正しく機能する
     */
    public function test_rest_button_displays_and_works_correctly()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => null, // 出勤中
        ]);

        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertSee('<button type="submit" class="button button-rest">休憩入</button>', false);

        $this->actingAs($user)->post(route('attendance.rest'));
        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertSeeText('休憩中');
    }


    /**
     * 休憩は一日に何回でもできる
     */
    public function test_rest_can_be_started_multiple_times_in_a_day()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => '09:00:00',
        ]);

        $this->actingAs($user)->post(route('attendance.rest'));
        $this->actingAs($user)->post(route('attendance.resume'));
        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertSee('<button type="submit" class="button button-rest">休憩入</button>', false);
    }


    /**
     * 休憩戻ボタンが正しく機能する
     */
    public function test_rest_end_button_displays_and_works_correctly()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => '09:00:00',
        ]);

        $this->actingAs($user)->post(route('attendance.rest'));
        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertSee('<button type="submit" class="button button-resume">休憩戻</button>', false);

        $this->actingAs($user)->post(route('attendance.resume'));
        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertSeeText('出勤中');
    }


    /**
     * 休憩戻は一日に何回でもできる
     */
    public function test_rest_end_can_be_done_multiple_times_in_a_day()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => '09:00:00',
        ]);

        $this->actingAs($user)->post(route('attendance.rest'));
        $this->actingAs($user)->post(route('attendance.resume'));
        $this->actingAs($user)->post(route('attendance.rest'));
        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertSee('<button type="submit" class="button button-resume">休憩戻</button>', false);
    }


    /**
     * 休憩時刻が勤怠一覧で確認できる
     */
    public function test_rest_times_are_displayed_in_attendance_list()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => '09:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertDontSeeText('0:00');

        $this->actingAs($user)->post(route('attendance.rest'));
        $this->actingAs($user)->post(route('attendance.resume'));
        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertSeeText('09:00');
        $response->assertSeeText('0:00');
    }
}
