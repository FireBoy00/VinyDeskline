<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeskMetric extends Model
{
    protected $fillable = [
        'desk_id',
        'height_mm',
        'is_sitting',
        'recorded_at',
    ];

    protected $casts = [
        'is_sitting' => 'boolean',
        'recorded_at' => 'datetime',
    ];

    /**
     * Get the desk that this metric belongs to.
     */
    public function desk(): BelongsTo
    {
        return $this->belongsTo(Desk::class, 'desk_id', 'desk_id');
    }
}
