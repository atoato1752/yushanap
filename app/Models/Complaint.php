<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'content',
        'status',
        'result',
        'handler_id',
        'handled_at',
    ];

    protected $casts = [
        'handled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function handler()
    {
        return $this->belongsTo(Admin::class, 'handler_id');
    }

    public function getTypeTextAttribute()
    {
        return [
            'query' => '查询问题',
            'payment' => '支付问题',
            'report' => '报告问题',
            'other' => '其他问题',
        ][$this->type] ?? '未知';
    }

    public function getStatusTextAttribute()
    {
        return [
            'pending' => '待处理',
            'processing' => '处理中',
            'completed' => '已完成',
        ][$this->status] ?? '未知';
    }

    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'danger',
            'processing' => 'warning',
            'completed' => 'success',
        ][$this->status] ?? 'secondary';
    }
} 