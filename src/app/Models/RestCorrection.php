<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_correction_id',
        'rest_id',
        'rest_in',
        'rest_out',
    ];
}
