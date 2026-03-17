<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $table = 'cours';

    protected $fillable = [
        'name',
        'description',
        'teacher_id',
        'interest_id',
        'price',
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'inscriptions', 'cours_id', 'user_id')
            ->withPivot('group_id');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class, 'cours_id');
    }

    public function interest(): BelongsTo
    {
        return $this->belongsTo(Interest::class);
    }

    public function favUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites', 'cours_id', 'user_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
