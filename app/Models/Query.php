<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Query extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'id_card',
        'status',
        'payment_status',
        'amount',
        'result',
        'report_path',
        'report_generated_at',
    ];

    protected $casts = [
        'result' => 'array',
        'report_generated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusTextAttribute()
    {
        return [
            'pending' => '待处理',
            'processing' => '处理中',
            'completed' => '已完成',
            'failed' => '失败',
            'cancelled' => '已取消',
        ][$this->status] ?? '未知';
    }

    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'secondary',
        ][$this->status] ?? 'secondary';
    }

    public function getPaymentStatusTextAttribute()
    {
        return [
            'unpaid' => '未支付',
            'paid' => '已支付',
            'refunded' => '已退款',
        ][$this->payment_status] ?? '未知';
    }

    public function getPaymentStatusColorAttribute()
    {
        return [
            'unpaid' => 'danger',
            'paid' => 'success',
            'refunded' => 'info',
        ][$this->payment_status] ?? 'secondary';
    }
} 