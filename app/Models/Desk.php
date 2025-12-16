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
    use HasFactory, Notifiable;

    protected $fillable = [
        'desk_id',
        'room_id',
        'floor_id',
        'is_removed_from_api',
        'name',
        'manufacturer',
        'position_mm',
        'speed_mms',
        'status',
        'activations_counter',
        'sit_stand_counter',
        'last_synced_at',
    ];

    protected $casts = [
        'is_removed_from_api' => 'boolean',
        'last_synced_at' => 'datetime',
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
}
