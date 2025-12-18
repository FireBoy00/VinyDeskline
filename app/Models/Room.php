<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'name',
        'floor_id',
        'description',
    ];

    /**
     * Get the floor that the room is on.
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    /**
     * Get the desks in this room.
     */
    public function desks(): HasMany
    {
        return $this->hasMany(Desk::class, 'room_id', 'id');
    }
}
