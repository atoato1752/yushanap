<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Query extends Model
{
    protected $fillable = [
        'user_id',
        'report_id',
        'payment_type',
        'payment_status',
        'amount',
        'agent_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function agentEarning()
    {
        return $this->hasOne(AgentEarning::class);
    }
} 