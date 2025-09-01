<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Room extends Model
{
    use HasFactory;
    protected $table = "rooms";
    protected $fillable = [
        "room_number",
        "building",
        "floor",
        "description",
        "campus_id"
    ];

    public function scopeWhereBranch($query, string $campusId)
    {
        return $query->when($campusId != "*", function ($query) use ($campusId) {
            return $query->where('rooms.campus_id', $campusId); // Corrected column name
        })->when($campusId == "*", function ($query) {
            return $query->join('user_campus as uc', 'uc.campus_id', '=', 'rooms.campus_id')
                ->where('uc.user_id', Auth::id());
        });
    }
}
