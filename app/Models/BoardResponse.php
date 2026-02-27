<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardResponse extends Model
{
    protected $fillable = [
        'session_id',
        'member_id',
        'member_name',
        'member_role',
        'response',
        'model_used',
        'tokens_used',
        'response_order',
    ];

    /**
     * Get the session this response belongs to.
     */
    public function session()
    {
        return $this->belongsTo(BoardSession::class, 'session_id');
    }
}