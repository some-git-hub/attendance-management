<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rest extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'attendance_id',
        'rest_in',
        'rest_out',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function restCorrections()
    {
        return $this->hasMany(RestCorrection::class);
    }

    public function restHistories()
    {
        return $this->hasMany(RestHistory::class);
    }
}
