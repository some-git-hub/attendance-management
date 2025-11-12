<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceCorrection;
use App\Models\User;
use App\Models\Attendance;

class AttendanceCorrectionFactory extends Factory
{
    protected $model = AttendanceCorrection::class;

    public function definition()
    {
        // 日付を固定せずランダムに生成
        $date = $this->faker->date();

        return [
            'attendance_id' => Attendance::factory(), // もし既存のAttendanceを指定したい場合は外部で渡す
            'user_id' => User::factory(),
            'date' => $date,
            'clock_in' => $this->faker->time('H:i:s', '09:00:00'),
            'clock_out' => $this->faker->time('H:i:s', '18:00:00'),
            'remark' => $this->faker->sentence(),
            'status' => 0, // デフォルトは承認待ち
        ];
    }

    /**
     * 承認済み
     */
    public function approved()
    {
        return $this->state(fn(array $attributes) => [
            'status' => 1,
        ]);
    }

    /**
     * 管理者修正
     */
    public function adminEdited()
    {
        return $this->state(fn(array $attributes) => [
            'status' => 2,
        ]);
    }
}
