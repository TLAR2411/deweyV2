<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'login_attem',
        'photo_path',
        'locked_until',
        'student_id',
        "campus_ids",
        "role_id",
        "is_AllCampus",
        "teacher_id"
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_AllCampus' => 'boolean',
    ];




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

    public function scopeWhereBranch($query, string $campusId)
    {
        return $query->when($campusId != "*", function ($query) use ($campusId) {
            return $query->where('users.camp_id', $campusId); // Corrected column name
        })->when($campusId == "*", function ($query) {
            return $query->join('user_campus as uc', 'uc.campus_id', '=', 'users.camp_id')
                ->where('uc.user_id', Auth::id());
        });
    }
}
