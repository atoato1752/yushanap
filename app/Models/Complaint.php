<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'content',
        'status',
        'admin_remark'
    ];

    protected $casts = [
        'status' => 'string'
    ];
} 