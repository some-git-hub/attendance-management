<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'user_id'   => User::factory(), // 関連ユーザーを自動生成
            'date'      => Carbon::today(), // 今日の日付
            'clock_in'  => '09:00:00',
            'clock_out' => null,             // 出勤中がデフォルト
            'remark'    => null,
        ];
    }

    /**
     * 退勤済み状態（出勤・退勤あり）を作るステート
     */
    public function clockedOut(): static
    {
        return $this->state(fn () => [
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
    }

    /**
     * 出勤前（まだレコードがない）状態を表現するためのステート
     * → テストではあまり使わないが一応
     */
    public function notStarted(): static
    {
        return $this->state(fn () => [
            'clock_in'  => null,
            'clock_out' => null,
        ]);
    }
}
