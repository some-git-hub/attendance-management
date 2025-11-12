<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_history_id',
        'rest_id',
        'updated_by',
        'before_rest_in',
        'before_rest_out',
        'after_rest_in',
        'after_rest_out',
    ];
}
