<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'message', 'status'
    ];

    public function project()
    {
    return $this->hasOne(\App\Models\Project::class);
    }
}
