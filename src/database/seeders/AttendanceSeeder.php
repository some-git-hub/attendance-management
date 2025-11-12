<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userIds = DB::table('users')->where('role', 0)->pluck('id');

        foreach ($userIds as $userId) {
            for ($i = 89; $i >= 0; $i--) {
                $date = Carbon::yesterday()->subDays($i);

                // 日曜日は休日のため、勤怠情報は入れない
                if ($date->isSunday()) {
                    continue;
                }

                DB::table('attendances')->insert([
                    'user_id'    => $userId,
                    'date'       => $date->toDateString(),
                    'clock_in'   => $date->isSunday() ? null : $date->copy()->hour(9)->minute(0),
                    'clock_out'  => $date->isSunday() ? null : $date->copy()->hour(18)->minute(0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
