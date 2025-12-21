<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'height',
        'age',
        'needs_personalization',
        'is_admin',
        'desk_id',
        'optimal_sitting_height',
        'optimal_standing_height',
        'custom_name_1',
        'custom_name_2',
        'custom_height_1',
        'custom_height_2',

    ];

    /**
     * Boot the model.
     * Register the observer for automatic height calculation.
     */
    protected static function boot()
    {
        parent::boot();
        // Observer is registered globally in AppServiceProvider
    }

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

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    /**
     * Get the desk assigned to this user.
     */
    public function desk(): BelongsTo
    {
        return $this->belongsTo(Desk::class, 'desk_id', 'desk_id');
    }
}
