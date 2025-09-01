<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        "subject_id",
        "class_type_id",
        "grade_level_id",
        "full_score",
        "average_score"
    ];
}
