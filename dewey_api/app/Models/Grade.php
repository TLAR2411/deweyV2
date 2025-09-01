<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;
    protected $table= "grades";
    protected $fillable = [
        "cur_id",
        "edu_id",
        "name",
        "grade_level_id",
        "symbol",
    ];
}
