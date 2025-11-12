<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Rest;
use App\Models\AttendanceCorrection;
use Carbon\Carbon;

class RestCorrectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::find(2);

        $attendanceCorrections = AttendanceCorrection::where('user_id', $user->id)
            ->orderByDesc('date')
            ->take(10)
            ->get();

        foreach ($attendanceCorrections as $correction) {
            $rests = Rest::where('attendance_id', $correction->attendance_id)->get();

            foreach ($rests as $rest) {
                DB::table('rest_corrections')->insert([
                    'attendance_correction_id' => $correction->id,
                    'rest_id' => $rest->id,
                    'rest_in'  => Carbon::parse($rest->rest_in)->addMinutes(rand(-5,5)),
                    'rest_out' => Carbon::parse($rest->rest_out)->addMinutes(rand(-5,5)),
                    'created_at' => Carbon::now()->subDays(rand(1,10)),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}
