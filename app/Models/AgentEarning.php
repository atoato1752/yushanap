<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentEarning extends Model
{
    protected $fillable = [
        'agent_id',
        'query_id',
        'amount',
        'status'
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function query()
    {
        return $this->belongsTo(Query::class);
    }
} 