<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'updated_by',
        'before_clock_in',
        'before_clock_out',
        'before_remark',
        'after_clock_in',
        'after_clock_out',
        'after_remark',
    ];
}
