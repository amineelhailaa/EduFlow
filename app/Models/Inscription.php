<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inscription extends Model
{
    //
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

}
