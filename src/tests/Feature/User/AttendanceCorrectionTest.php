<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;

class AttendanceCorrectionTest extends TestCase
{
    /**
     * 出勤時間が退勤時間より後の場合はバリデーションエラーが表示される
     */
    public function test_clock_in_after_clock_out_shows_error()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->put(route('attendance.update', $attendance->id), [
            'clock_in' => '18:00',
            'clock_out' => '12:00',
            'remark' => '通常勤務',
        ]);

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です'
        ]);
    }


    /**
     * 休憩開始時間が退勤時間より後の場合はバリデーションエラーが表示される
     */
    public function test_break_start_after_clock_out_shows_error()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->put(route('attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rest_id' => [null],
            'rest_in' => ['19:00'],
            'rest_out' => ['20:00'],
            'remark' => '通常勤務',
        ]);

        $response->assertSessionHasErrors([
            'rest_in.0' => '休憩時間が不適切な値です',
        ]);
    }


    /**
     * 休憩終了時間が退勤時間より後の場合はバリデーションエラーが表示される
     */
    public function test_break_end_after_clock_out_shows_error()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->put(route('attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rest_id' => [null],
            'rest_in' => ['12:00'],
            'rest_out' => ['19:00'],
            'remark' => '通常勤務',
        ]);

        $response->assertSessionHasErrors([
            'rest_in.0' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }


    /**
     * 備考欄が未入力の場合はバリデーションエラーが表示される
     */
    public function test_empty_remarks_shows_error()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->put(route('attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'remark' => '',
        ]);

        $response->assertSessionHasErrors([
            'remark' => '備考を記入してください'
        ]);
    }


    /**
     * 修正申請処理が実行される
     */
    public function test_attendance_correction_request_is_created()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'remark' => '通常勤務',
        ]);

        $response = $this->actingAs($user)->put(route('attendance.update', $attendance->id), [
            'clock_in' => '09:15',
            'clock_out' => '18:15',
            'remark' => '修正申請',
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'date' => '2025-01-10',
            'clock_in' => '09:15:00',
            'clock_out' => '18:15:00',
            'remark' => '修正申請',
            'status' => 0,
        ]);
    }


    /**
     * 「承認待ち」にユーザーが行った申請が全て表示される
     */
    public function test_pending_correction_requests_are_displayed()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-01-10',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.correction-list',['status' => 0]));
        $response->assertStatus(200);
        $response->assertDontSeeText('2025/01/10');

        AttendanceCorrection::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'date' => '2025-01-10',
            'status' => 0, // 承認待ち
        ]);

        $response = $this->actingAs($user)->get(route('attendance.correction-list',['status' => 0]));
        $response->assertSeeText('2025/01/10');
        $response->assertSeeText('承認待ち');
    }


    /**
     * 「承認済み」に管理者が承認した修正申請が全て表示される
     */
    public function test_approved_correction_requests_are_displayed_for_user()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-01-10'
        ]);

        $response = $this->actingAs($user)->get(route('attendance.correction-list', ['status' => 1]));
        $response->assertStatus(200);
        $response->assertDontSeeText('2025/01/10');

        AttendanceCorrection::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'date' => '2025-01-10',
            'status' => 1, // 承認済み
        ]);

        $response = $this->actingAs($user)->get(route('attendance.correction-list', ['status' => 1]));
        $response->assertSeeText('2025/01/10');
        $response->assertSeeText('承認済み');
    }


    /**
     * 各申請の「詳細」を押下すると勤怠詳細画面に遷移する
     */
    public function test_correction_request_detail_redirects_to_attendance_detail()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-01-10'
        ]);

        $request = AttendanceCorrection::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'date' => '2025-01-10',
            'status' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', $attendance->id));
        $response->assertStatus(200);
        $response->assertSeeText('2025年');
        $response->assertSeeText('01月10日');
    }
}
