<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['name', 'description', 'status', 'inquiry_id'];

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps()->withPivot('role');
    }

    public function tasks()
    {
    return $this->hasMany(\App\Models\Task::class);
    }

    public function comments()
    {
    return $this->hasMany(\App\Models\Comment::class);
    }

    public function messages()
    {
    return $this->hasMany(\App\Models\ProjectMessage::class);
    }

    public function inquiry()
    {
    return $this->belongsTo(\App\Models\Inquiry::class);
    }


}
