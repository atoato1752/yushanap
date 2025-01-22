<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'query_id',
        'amount',
        'payment_type',
        'transaction_id',
        'status'
    ];

    public function query()
    {
        return $this->belongsTo(Query::class);
    }
} 