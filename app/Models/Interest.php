<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Interest extends Model
{
    protected $fillable = [
        'name',
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'student_interests');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
