<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceCorrectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::find(2);
        $attendances = Attendance::where('user_id', $user->id)
            ->orderByDesc('date')
            ->take(10)
            ->get();

        foreach ($attendances->values() as $attendance) {
            DB::table('attendance_corrections')->insert([
                'attendance_id' => $attendance->id,
                'user_id'       => $user->id,
                'date'          => $attendance->date,
                'clock_in'      => Carbon::parse($attendance->clock_in)->addMinutes(rand(-5,5)),
                'clock_out'     => Carbon::parse($attendance->clock_out)->addMinutes(rand(-5,5)),
                'remark'        => '電車遅延のため',
                'status'        => 0, // 承認待ち
                'created_at'    => Carbon::now()->subDays(rand(1, 10)),
                'updated_at'    => Carbon::now(),
            ]);
        }
    }
}
