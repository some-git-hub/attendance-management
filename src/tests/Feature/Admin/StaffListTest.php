<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class StaffListTest extends TestCase
{
    /**
     * 管理者はスタッフ一覧ページで全ユーザーの氏名とメールアドレスを確認できる
     */
    public function test_admin_can_see_all_staff_names_and_emails()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.staff.list'));

        $response->assertStatus(200);
        $response->assertSeeText($user1->name);
        $response->assertSeeText($user1->email);
        $response->assertSeeText($user2->name);
        $response->assertSeeText($user2->email);
    }


    /**
     * 管理者はユーザーの勤怠一覧を確認できる
     */
    public function test_admin_can_view_user_attendance_list()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin)->get(
            route('admin.staff-attendance.list', ['id' => $user->id])
        );

        $response->assertStatus(200);
        $response->assertSeeText(Carbon::now()->format('Y/m'));
        $response->assertSeeText('09:00');
        $response->assertSeeText('18:00');
    }


    /**
     * 「前月」ボタンを押すと前月の勤怠情報が表示される
     */
    public function test_admin_can_view_previous_month_attendance()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create();

        $prevMonth = Carbon::now()->copy()->subMonth();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $prevMonth->copy()->day(10)->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
        ]);

        $response = $this->actingAs($admin)->get(
            route('admin.staff-attendance.list', [
                'id' => $user->id,
                'month' => $prevMonth->format('Y-m'),
            ])
        );

        $response->assertStatus(200);
        $response->assertSeeText($prevMonth->format('Y/m'));
        $response->assertSeeText('09:00');
    }


    /**
     * 「翌月」ボタンを押すと翌月の勤怠情報が表示される
     */
    public function test_admin_can_view_next_month_attendance()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create();

        $nextMonth = Carbon::now()->copy()->addMonth();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $nextMonth->copy()->day(10)->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
        ]);

        $response = $this->actingAs($admin)->get(
            route('admin.staff-attendance.list', [
                'id' => $user->id,
                'month' => $nextMonth->format('Y-m'),
            ])
        );

        $response->assertStatus(200);
        $response->assertSeeText($nextMonth->format('Y/m'));
        $response->assertSeeText('09:00');
    }


    /**
     * 「詳細」ボタンを押すとその日の勤怠詳細画面に遷移できる
     */
    public function test_admin_can_view_attendance_detail_page()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin)->get(
            route('admin.attendance.show', [
                'id' => $attendance->id,
            ])
        );

        $response->assertStatus(200);
        $response->assertSeeText(Carbon::now()->format('Y年'));
        $response->assertSeeText(Carbon::now()->format('m月d日'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
