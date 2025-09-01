<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Classroom extends Model
{
    use HasFactory;

    protected $table = "classrooms";
    protected $fillable = [
        "year_id",
        "room_id",
        "grade_id",
        "session_id",
        "classtype_id",
        "description",
        "deleted",
        "campus_id"
    ];

    public function scopeWhereBranch($query, string $campusId)
    {
        return $query->when($campusId != "*", function ($query) use ($campusId) {
            return $query->where('classrooms.campus_id', $campusId); // Corrected column name
        })->when($campusId == "*", function ($query) {
            return $query->join('user_campus as uc', 'uc.campus_id', '=', 'classrooms.campus_id')
                ->where('uc.user_id', Auth::id());
        });
    }
}
