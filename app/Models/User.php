<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
            'birthdate' => 'date',
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

    protected function lastSevenDaysTracking(): Attribute
    {
        return Attribute::make(
            get: fn() => Tracking::where("user_id", $this->id)->where("created_at", ">=", Carbon::today()->subDays(6))->get()
        );
    }
        public function getBirthdateAttribute($value)
    {
        return Carbon::parse( $value)->format('Y-m-d h:i:s');
    }
}
