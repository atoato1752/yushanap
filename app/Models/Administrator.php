<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Administrator extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'username',
        'password',
        'name',
        'role',
        'status'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'status' => 'boolean',
        'last_login_at' => 'datetime'
    ];

    public function isAdmin()
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }
} 