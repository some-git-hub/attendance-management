<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    /**
     * 管理者がその日の全ユーザー勤怠を確認できる
     */
    public function test_admin_can_see_all_users_attendance_for_today()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        Rest::factory()->create([
            'attendance_id' => $attendance1->id,
            'rest_in' => '12:00:00',
            'rest_out' => '13:00:00',
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => '09:15:00',
            'clock_out' => '13:00:00',
        ]);

        Rest::factory()->create([
            'attendance_id' => $attendance2->id,
            'rest_in' => '10:15:00',
            'rest_out' => '11:00:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        $response->assertSeeText(Carbon::now()->format('Y/m/d'));
        $response->assertSeeText($user1->name);
        $response->assertSeeText($user2->name);

        // user1 の勤怠
        $response->assertSeeText('09:00');
        $response->assertSeeText('18:00');
        $response->assertSeeText('1:00'); // 休憩
        $response->assertSeeText('8:00'); // 合計

        // user2 の勤怠
        $response->assertSeeText('09:15');
        $response->assertSeeText('13:00');
        $response->assertSeeText('0:45'); // 休憩
        $response->assertSeeText('3:00'); // 合計
    }


    /**
     * 遷移時に現在の日付が表示される
     */
    public function test_current_date_is_displayed_on_page_load()
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        $response->assertSeeText(Carbon::now()->format('Y/m/d'));
    }


    /**
     * 「前日」ボタン押下で前日の勤怠が表示される
     */
    public function test_previous_day_button_displays_previous_attendance()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create();

        $yesterday = Carbon::yesterday();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $yesterday->format('Y-m-d'),
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.list', ['date' => $yesterday->format('Y-m-d')]));

        $response->assertStatus(200);
        $response->assertSeeText($yesterday->format('Y/m/d'));
        $response->assertSeeText($user->name);
    }


    /**
     * 「翌日」ボタン押下で翌日の勤怠が表示される
     */
    public function test_next_day_button_displays_next_attendance()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create();

        $tomorrow = Carbon::tomorrow();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $tomorrow->format('Y-m-d'),
            'clock_in' => '09:15:00',
            'clock_out' => '18:15:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.list', ['date' => $tomorrow->format('Y-m-d')]));
        $response->assertStatus(200);
        $response->assertSeeText($tomorrow->format('Y/m/d'));
        $response->assertSeeText($user->name);
    }
}
