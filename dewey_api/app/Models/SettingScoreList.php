<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingScoreList extends Model
{
    use HasFactory;
    protected $table = "tbl_list_avg";
    protected $fillable = [
        "id_list_avg",
        "id_config",
        "grade_level_id",
        "avg_month",
        "avg_semester_one",
        "avg_semester_two"
    ];
}
