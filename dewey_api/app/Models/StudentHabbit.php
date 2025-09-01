<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentHabbit extends Model
{
    use HasFactory;

    protected $table = "tbl_study_habits";
    public $timestamps = true;
    protected $fillable = [
        "student_id",
        "class_id",
        "month_id",
        "respects_school",
        "pay_attention",
        "neat_and_tidy",
        "work",
        "get_along_with_other",
        "take_care",

    ];
}
