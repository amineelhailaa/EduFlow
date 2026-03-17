<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    //JWT


    public function getJWTIdentifier()
    {
        // TODO: Implement getJWTIdentifier() method.
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        // TODO: Implement getJWTCustomClaims() method.
        return [];
    }

    public function coursesAsTeacher(): HasMany
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    public function coursesAsStudent(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'inscriptions', 'user_id', 'cours_id')
            ->withPivot('group_id');
    }

    public function favCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'favorites', 'user_id', 'cours_id');
    }

    public function studentInterests(): BelongsToMany
    {
        return $this->belongsToMany(Interest::class, 'student_interests');
    }
}
