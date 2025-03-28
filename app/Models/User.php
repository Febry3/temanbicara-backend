<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'phone_number',
        'password',
        'image',
        'name',
        'nickname',
        'gender',
        'birthdate',
        'role'
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthdate' => 'datetime',
        ];
    }
    public function artikels()
    {
        return $this->hasMany(Article::class, 'user_id');
    }
    public function expertises()
    {
        return $this->hasMany(Expertise::class, 'user_id');
    }
    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'user_id');
    }
    public function user_answers()
    {
        return $this->hasMany(user_answer::class, 'user_id');
    }
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'counselor_id');
    }
}
