<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['project_id', 'task_id', 'user_id', 'body'];

    public function user()
    {   
        return $this->belongsTo(\App\Models\User::class);
    }

    public function project()
    {
        return $this->belongsTo(\App\Models\Project::class);
    }

    public function task()
    {
        return $this->belongsTo(\App\Models\Task::class);
    }

}
