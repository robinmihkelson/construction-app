<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMessageAttachment extends Model
{
    protected $fillable = ['project_message_id','disk','path','original_name','mime','size'];

    public function message()
    {
        return $this->belongsTo(ProjectMessage::class, 'project_message_id');
    }
}