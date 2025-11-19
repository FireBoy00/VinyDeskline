<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Desk extends Model
{
    protected $fillable = [
        'desk_id',
        'state',
    ];

    protected $casts = [
        'state' => 'array',
    ];
}
