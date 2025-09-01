<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingSemesterList extends Model
{
    use HasFactory;
    protected $table = "setting_semester_list";
    protected $fillable = [
        "id_setting_list",
        "setting_semester_month_id",
        "semester_month1",
        "semester_month2",
        "three_months_semester1",
        "three_months_semester2",
        "grade_level_id",
        "year_id",
        "campus_id",
        "edu_id",
        "updated_at",
        "created_at",
    ];
}
