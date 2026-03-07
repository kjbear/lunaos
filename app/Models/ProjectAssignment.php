<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectAssignment extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'project_id',
        'agent_id',
        'role',
        'deleted_at',
    ];

    /**
     * Get the project this assignment belongs to.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the agent assigned.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }
}