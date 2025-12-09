<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeskUser extends Model
{
    protected $fillable = ['name', 'email', 'desk_id', 'status', 'height'];
}

