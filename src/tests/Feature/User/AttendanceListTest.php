<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceListTest extends TestCase
{
    /**
     * 自分が行った勤怠情報がすべて表示されている
     */
    public function test_all_own_attendance_records_are_displayed()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // 自分の勤怠情報を3日分作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-01-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-01-02',
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
        ]);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-01-03',
            'clock_in' => '08:45:00',
            'clock_out' => '17:45:00',
        ]);

        // 他ユーザーの勤怠データ（混在確認用）
        Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'date' => '2025-01-01',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSeeText('01/01');
        $response->assertSeeText('01/02');
        $response->assertSeeText('01/03');
        $response->assertSeeText('9:00');
        $response->assertSeeText('18:00');
        $response->assertDontSeeText('10:00');
    }


    /**
     * 勤怠一覧画面に遷移した際に現在の月が表示される
     */
    public function test_current_month_is_displayed_on_attendance_list()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSeeText('2025/01');
    }


    /**
     * 「前月」ボタンを押下した時に表示月の前月の情報が表示される
     */
    public function test_previous_month_button_displays_previous_month_records()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2024-12-20',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list', ['month' => '2024-12']));

        $response->assertStatus(200);
        $response->assertSeeText('2024/12');
        $response->assertSeeText('12/20');
    }


    /**
     * 「翌月」ボタンを押下した時に表示月の翌月の情報が表示される
     */
    public function test_next_month_button_displays_next_month_records()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-02-05',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list', ['month' => '2025-02']));
        $response->assertStatus(200);

        $response->assertSeeText('2025/02');
        $response->assertSeeText('02/05');
    }


    /**
     * 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_clicking_detail_button_navigates_to_attendance_detail()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('attendance.show', $attendance->id));
        $response->assertStatus(200);

        $response->assertSeeText('01月10日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
