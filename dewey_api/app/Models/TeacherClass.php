<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TeacherClass extends Model
{
    use HasFactory;
    protected $table = "teacher_class";
    protected $fillable = ["teacherId", "classId", "subjectId", "role", "campus_id"];


    public function scopeWhereBranch($query, string $campusId)
    {
        return $query->when($campusId != "*", function ($query) use ($campusId) {
            return $query->where('tc.campus_id', $campusId); // Use alias 'tc'
        })->when($campusId == "*", function ($query) {
            return $query->join('user_campus as uc', 'uc.campus_id', '=', 'tc.campus_id')
                ->where('uc.user_id', Auth::id());
        });
    }
}
