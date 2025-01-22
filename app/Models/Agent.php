<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $fillable = [
        'username',
        'password',
        'parent_id',
        'cost_price',
        'selling_price',
        'balance',
        'status'
    ];

    protected $hidden = [
        'password'
    ];

    public function parent()
    {
        return $this->belongsTo(Agent::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Agent::class, 'parent_id');
    }

    public function queries()
    {
        return $this->hasMany(Query::class);
    }

    public function earnings()
    {
        return $this->hasMany(AgentEarning::class);
    }
} 