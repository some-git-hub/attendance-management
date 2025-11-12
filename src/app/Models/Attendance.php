<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'remark',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    public function corrections()
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    /**
     * 出勤時間の取得
     */
    public function getClockInFormattedAttribute()
    {
        return $this->clock_in ? Carbon::parse($this->clock_in)->format('H:i') : '';
    }

    /**
     * 退勤時間の取得
     */
    public function getClockOutFormattedAttribute()
    {
        return $this->clock_out ? Carbon::parse($this->clock_out)->format('H:i') : '';
    }


    /**
     * 休憩時間の取得（勤務中でもリアルタイムで計算可能）
     */
    public function getCurrentRestDurationAttribute()
    {
        $totalSeconds = $this->rests->sum(function ($rest) {
            $start = Carbon::parse($rest->rest_in);
            $end = $rest->rest_out ? Carbon::parse($rest->rest_out) : now();
            return $end->diffInSeconds($start);
        });

        $hours = intdiv($totalSeconds, 3600);
        $minutes = intdiv($totalSeconds % 3600, 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }


    /**
     * 合計勤務時間の取得
     */
    public function getWorkDurationAttribute()
    {
        $clockIn = Carbon::parse($this->clock_in);
        $clockOut = Carbon::parse($this->clock_out);

        $restMinutes = $this->current_rest_minutes ?? 0;

        $workMinutes = max($clockOut->diffInMinutes($clockIn) - $restMinutes, 0);

        $hours = intdiv($workMinutes, 60);
        $minutes = $workMinutes % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }
}
