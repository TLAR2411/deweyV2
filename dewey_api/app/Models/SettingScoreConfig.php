<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SettingScoreConfig extends Model
{
    use HasFactory;
    protected $table = "tbl_config_avg";
    protected $fillable = [
        "id_config",
        "id_academic",
        "id_academic",
        "edu_id",
        "campus_id",
        "created_at",
        "updated_at"
    ];

    public function scopeWhereBranch($query, string $campusId)
    {
        return $query->when($campusId != "*", function ($query) use ($campusId) {
            return $query->where('tbl_config_avg.campus_id', $campusId);
        })->when($campusId == "*", function ($query) {
            return $query->join('user_campus as uc', 'uc.campus_id', '=', 'tbl_config_avg.campus_id')
                ->where('uc.user_id', Auth::id());
        });
    }
}
