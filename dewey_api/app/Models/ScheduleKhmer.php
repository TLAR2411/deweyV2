<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleKhmer extends Model
{
    use HasFactory;
    protected $fillable = ['classId', 'timeId', 'dayId', 'subjectId'];
}
