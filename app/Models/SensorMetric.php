<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorMetric extends Model
{
    protected $fillable = [
        'desk_id',
        'temperature',
        'humidity',
        'light',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    /**
     * Get the desk that this metric belongs to (optional).
     */
    public function desk()
    {
        return $this->belongsTo(Desk::class, 'desk_id', 'desk_id');
    }
}
