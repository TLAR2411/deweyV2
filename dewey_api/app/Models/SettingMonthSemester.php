<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SettingMonthSemester extends Model
{
    use HasFactory;
    protected $table = "semester_setting";
    protected $fillable = [
        "id",
        "semester_month1",
        "semester_month2",
        "three_months_semester1",
        "three_months_semester2",
        "edu_id",
        "year_id",
        "campus_id"
    ];


    public function scopeWhereBranch($query, string $campusId)
    {
        return $query->when($campusId != "*", function ($query) use ($campusId) {
            return $query->where('semester_setting.campus_id', $campusId);
        })->when($campusId == "*", function ($query) {
            return $query->join('user_campus as uc', 'uc.campus_id', '=', 'semester_setting.campus_id')
                ->where('uc.user_id', Auth::id());
        });
    }
}
