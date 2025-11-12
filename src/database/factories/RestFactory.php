<?php

namespace Database\Factories;

use App\Models\Rest;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class RestFactory extends Factory
{
    protected $model = Rest::class;

    public function definition(): array
    {
        return [
            'attendance_id' => Attendance::factory(),
            'rest_in'       => '12:00:00',
            'rest_out'      => '13:00:00',
        ];
    }

    /**
     * 休憩中（まだ休憩戻していない）状態を作るステート
     */
    public function onBreak(): static
    {
        return $this->state(fn () => [
            'rest_out' => null,
        ]);
    }
}
