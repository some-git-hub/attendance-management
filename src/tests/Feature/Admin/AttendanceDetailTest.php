<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    /**
     * 勤怠詳細画面に選択したデータが表示される
     */
    public function test_attendance_detail_page_displays_selected_attendance_data()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'remark' => '通常勤務',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.show', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $response->assertSeeText($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSeeText('通常勤務');
    }


    /**
     * 出勤時間が退勤時間より後の場合はバリデーションエラーが表示される
     */
    public function test_clock_in_after_clock_out_shows_error_message()
    {
        $admin = User::factory()->create(['role' => 1]);
        $attendance = Attendance::factory()->create([
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.attendance.update', $attendance->id), [
            'clock_in' => '16:00',
            'clock_out' => '12:00',
            'remark' => '通常勤務',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['clock_in']);
        $this->assertStringContainsString(
            '出勤時間もしくは退勤時間が不適切な値です',
            session('errors')->first('clock_in')
        );
    }


    /**
     * 休憩開始時間が退勤時間より後の場合はバリデーションエラーが表示される
     */
    public function test_rest_in_after_clock_out_shows_error_message()
    {
        $admin = User::factory()->create(['role' => 1]);
        $attendance = Attendance::factory()->create([
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rest_id' => [null],
            'rest_in' => ['19:00'],
            'rest_out' => ['20:00'],
            'remark' => '通常勤務',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['rest_in.0']);
        $this->assertStringContainsString(
            '休憩時間が不適切な値です',
            session('errors')->first('rest_in.0')
        );
    }


    /**
     * 休憩終了時間が退勤時間より後の場合はバリデーションエラーが表示される
     */
    public function test_rest_out_after_clock_out_shows_error_message()
    {
        $admin = User::factory()->create(['role' => 1]);
        $attendance = Attendance::factory()->create([
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.attendance.update', $attendance->id), [
            'clock_in' => '09:15',
            'clock_out' => '18:15',
            'rest_id' => [null],
            'rest_in' => ['12:00'],
            'rest_out' => ['19:00'],
            'remark' => '通常勤務',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['rest_in.0']);
        $this->assertStringContainsString(
            '休憩時間もしくは退勤時間が不適切な値です',
            session('errors')->first('rest_in.0')
        );
    }


    /**
     * 備考欄が未入力の場合はバリデーションエラーが表示される
     */
    public function test_remark_is_required_and_shows_error_message()
    {
        $admin = User::factory()->create(['role' => 1]);
        $attendance = Attendance::factory()->create([
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $showResponse = $this->actingAs($admin)->get(route('admin.attendance.show', $attendance->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee($attendance->id);

        $updateResponse = $this->put(route('admin.attendance.update', $attendance->id), [
            'clock_in' => '09:15',
            'clock_out' => '18:15',
            'remark' => '', // 未入力
        ]);

        $updateResponse->assertStatus(302);
        $updateResponse->assertSessionHasErrors(['remark']);
        $this->assertStringContainsString(
            '備考を記入してください',
            session('errors')->first('remark')
        );
    }
}
