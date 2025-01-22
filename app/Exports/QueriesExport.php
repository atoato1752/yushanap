<?php

namespace App\Exports;

use App\Models\Query;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class QueriesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        return Query::with(['user', 'payment', 'agent'])
            ->when($this->filters['search'] ?? null, function($query) {
                $query->whereHas('user', function($q) {
                    $q->where('name', 'like', "%{$this->filters['search']}%")
                      ->orWhere('phone', 'like', "%{$this->filters['search']}%");
                });
            })
            ->when($this->filters['date_range'] ?? null, function($query) {
                $dates = explode(' - ', $this->filters['date_range']);
                $query->whereBetween('created_at', $dates);
            });
    }

    public function headings(): array
    {
        return [
            'ID',
            '用户名',
            '手机号',
            '支付方式',
            '支付状态',
            '支付金额',
            '代理商',
            '查询时间'
        ];
    }

    public function map($query): array
    {
        return [
            $query->id,
            $query->user->name,
            $query->user->phone,
            $query->payment_type,
            $query->payment_status,
            $query->amount,
            $query->agent->username ?? '-',
            $query->created_at->format('Y-m-d H:i:s')
        ];
    }
} 