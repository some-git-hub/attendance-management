<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendances = DB::table('attendances')->get();

        foreach ($attendances as $attendance) {
            if ($attendance->clock_out !== null) {
                $rest_in  = Carbon::parse($attendance->date)->hour(12)->minute(0);
                $rest_out = Carbon::parse($attendance->date)->hour(13)->minute(0);

                DB::table('rests')->insert([
                    'attendance_id' => $attendance->id,
                    'rest_in'       => $rest_in,
                    'rest_out'      => $rest_out,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }
    }
}
