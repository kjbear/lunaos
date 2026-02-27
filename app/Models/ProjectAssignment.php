<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectAssignment extends Model
{
    protected $fillable = [
        'project_id',
        'persona_id',
        'role',
    ];

    /**
     * Get the project this assignment belongs to.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the persona assigned.
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }
}