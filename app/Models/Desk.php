<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Desk extends Model
{
    use HasFactory;

    // Use desk_id as primary key instead of auto-increment id
    protected $primaryKey = 'desk_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'desk_id',
        'name',
        'room_id',
        'floor_id',
        'is_removed_from_api',
    ];

    protected $casts = [
        'is_removed_from_api' => 'boolean',
    ];

    /**
     * Get the room that the desk belongs to.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the floor that the desk is on.
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    /**
     * Get the user assigned to this desk.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'desk_id', 'desk_id');
    }

    /**
     * Get the metrics for this desk.
     */
    public function metrics(): HasMany
    {
        return $this->hasMany(DeskMetric::class, 'desk_id', 'desk_id');
    }

    /**
     * Get the schedules for this desk.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'desk_id', 'desk_id');
    }
}
