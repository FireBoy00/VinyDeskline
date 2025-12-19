<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Floor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'floor_number',
        'description',
    ];

    /**
     * Get the rooms on this floor.
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Get the desks on this floor (only those not in a room).
     * Desks in rooms get their floor from the room.
     */
    public function desks(): HasMany
    {
        // This relationship is no longer used since desks don't have floor_id
        // Kept for backward compatibility but will return empty
        return $this->hasMany(Desk::class, 'room_id', 'id')->whereNull('room_id');
    }
    
    /**
     * Get all desks that are on this floor (either directly or through rooms)
     */
    public function allDesks()
    {
        // Get desks through rooms on this floor
        $roomIds = $this->rooms()->pluck('id');
        return Desk::whereIn('room_id', $roomIds)->get();
    }
}
