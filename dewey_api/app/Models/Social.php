<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Social extends Model
{
    use HasFactory;
    protected $table = "social";
    protected $fillable = [
        "id",
        "name",
        "link",
        "photo_path",
        "campus_id",
        "user_id",
        "created_at",
        "updated_at"
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
}
