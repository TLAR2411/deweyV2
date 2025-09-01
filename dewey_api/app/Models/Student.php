<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
    use HasFactory;
    protected $table = 'students';
    protected $fillable = [
        "student_card_id",
        "kh_name",
        "en_name",
        "gender",
        "dob",
        "phone",
        "email",
        "national",
        "village_birth",
        "commune_birth",
        "district_birth",
        "province_birth",
        "village_address",
        "commune_address",
        "district_address",
        "province_address",
        "old_school",
        "old_school_english",
        "old_grade",
        "description",
        "photo_path",
        "new_photo_path",
        "status",
        "m_name",
        "m_job",
        "m_phone",
        "m_address",
        "m_national",
        "f_address",
        "f_job",
        "f_name",
        "f_phone",
        "f_national",
        "deleted",
        "campus_id"

    ];

    // public function scopeWhereBranch($query, string $campusId)
    // {
    //     return $query->when($campusId != "*", function ($query) use ($campusId) {
    //         return $query->where('students.$campus_id', $campusId); // Correct column: staff.branch_id
    //     })->when($campusId == "*", function ($query) {
    //         return $query->join('user_campus as uc', 'uc.campus_id', '=', 'students.campus_id')
    //             ->where('uc.user_id', Auth::id());
    //     });
    // }

    public function scopeWhereBranch($query, string $campusId)
    {
        return $query->when($campusId != "*", function ($query) use ($campusId) {
            return $query->where('students.campus_id', $campusId);
        })->when($campusId == "*", function ($query) {
            return $query->whereIn('students.campus_id', function ($subQuery) {
                $subQuery->select('campus_id')
                    ->from('user_campus')
                    ->where('user_id', Auth::id());
            });
        });
    }

    public function setPhotoPathAttribute($value)
    {
        if (isset($value)) {
            // Remove the old photo only if a new one is provided
            if ($this->photo_path && gettype($value) !== "string") {
                Storage::disk('public')->delete($this->photo_path);
            }

            if (gettype($value) === "string") {
                $this->attributes['photo_path'] = $value; // Assign the string path
            } else {
                // Store the new file and update the attribute
                $path = $value->store('user/' . date('FY'), ['disk' => 'public']);
                $this->attributes['photo_path'] = $path;
            }
        }
    }

    public static function boot()
    {
        parent::boot();

        // Ensure the photo is deleted only when the model is deleted
        static::deleting(function ($item) {
            if ($item->photo_path) {
                Storage::disk('public')->delete($item->photo_path);
            }
        });
    }
}
