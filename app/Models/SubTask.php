<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubTask extends Model
{
    protected $fillanble = [
        'title',
        'description',
        'status',
        'task_id',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
