<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inscription extends Model
{
    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PAID = 'paid';

    protected $fillable = [
        'user_id',
        'cours_id',
        'group_id',
        'payment_status',
        'stripe_session_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'cours_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
