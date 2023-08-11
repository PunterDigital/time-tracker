<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'start_time',
        'end_time',
        'description',
        'user_id',
        'billing_rate',
        'is_billable',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

}
