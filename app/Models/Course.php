<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{


    //relation
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class,'inscriptions');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class,'cours_id');
    }

    public function interest(): BelongsTo
    {
        return $this->belongsTo(Interest::class);
    }

    public function favUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class,'favorites');
    }


    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class,'teacher_id');
    }




}
