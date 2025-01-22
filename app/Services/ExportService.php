<?php

namespace App\Services;

use App\Models\Query;
use App\Models\Agent;
use App\Models\Complaint;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Exception;

class ExportService
{
    /**
     * 导出查询记录
     */
    public function exportQueries(array $filters = []): string
    {
        $queries = Query::with(['user', 'agent', 'payment'])
            ->when(!empty($filters['status']), function ($query) use ($filters) {
                $query->where('payment_status', $filters['status']);
            })
            ->when(!empty($filters['date_range']), function ($query) use ($filters) {
                [$start, $end] = explode(' - ', $filters['date_range']);
                $query->whereBetween('created_at', [
                    $start . ' 00:00:00',
                    $end . ' 23:59:59'
                ]);
            })
            ->latest()
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 设置表头
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', '用户');
        $sheet->setCellValue('C1', '手机号');
        $sheet->setCellValue('D1', '姓名');
        $sheet->setCellValue('E1', '身份证号');
        $sheet->setCellValue('F1', '支付方式');
        $sheet->setCellValue('G1', '金额');
        $sheet->setCellValue('H1', '状态');
        $sheet->setCellValue('I1', '代理商');
        $sheet->setCellValue('J1', '查询时间');

        // 填充数据
        $row = 2;
        foreach ($queries as $query) {
            $sheet->setCellValue('A' . $row, $query->id);
            $sheet->setCellValue('B' . $row, $query->user->name);
            $sheet->setCellValue('C' . $row, $query->user->phone);
            $sheet->setCellValue('D' . $row, $query->name);
            $sheet->setCellValue('E' . $row, $query->id_card);
            $sheet->setCellValue('F' . $row, $this->formatPaymentType($query->payment_type));
            $sheet->setCellValue('G' . $row, $query->amount);
            $sheet->setCellValue('H' . $row, $this->formatPaymentStatus($query->payment_status));
            $sheet->setCellValue('I' . $row, $query->agent->name ?? '-');
            $sheet->setCellValue('J' . $row, $query->created_at);
            $row++;
        }

        return $this->saveSpreadsheet($spreadsheet, 'queries');
    }

    /**
     * 导出代理商收益
     */
    public function exportAgentEarnings(Agent $agent, array $filters = []): string
    {
        $earnings = $agent->earnings()
            ->with('query')
            ->when(!empty($filters['status']), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(!empty($filters['date_range']), function ($query) use ($filters) {
                [$start, $end] = explode(' - ', $filters['date_range']);
                $query->whereBetween('created_at', [
                    $start . ' 00:00:00',
                    $end . ' 23:59:59'
                ]);
            })
            ->latest()
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 设置表头
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', '查询ID');
        $sheet->setCellValue('C1', '查询用户');
        $sheet->setCellValue('D1', '收益金额');
        $sheet->setCellValue('E1', '结算状态');
        $sheet->setCellValue('F1', '结算时间');
        $sheet->setCellValue('G1', '创建时间');

        // 填充数据
        $row = 2;
        foreach ($earnings as $earning) {
            $sheet->setCellValue('A' . $row, $earning->id);
            $sheet->setCellValue('B' . $row, $earning->query_id);
            $sheet->setCellValue('C' . $row, $earning->query->user->name);
            $sheet->setCellValue('D' . $row, $earning->amount);
            $sheet->setCellValue('E' . $row, $this->formatEarningStatus($earning->status));
            $sheet->setCellValue('F' . $row, $earning->settled_at);
            $sheet->setCellValue('G' . $row, $earning->created_at);
            $row++;
        }

        return $this->saveSpreadsheet($spreadsheet, 'earnings');
    }

    /**
     * 导出投诉记录
     */
    public function exportComplaints(array $filters = []): string
    {
        $complaints = Complaint::with(['user', 'query'])
            ->when(!empty($filters['status']), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(!empty($filters['date_range']), function ($query) use ($filters) {
                [$start, $end] = explode(' - ', $filters['date_range']);
                $query->whereBetween('created_at', [
                    $start . ' 00:00:00',
                    $end . ' 23:59:59'
                ]);
            })
            ->latest()
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 设置表头
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', '用户');
        $sheet->setCellValue('C1', '手机号');
        $sheet->setCellValue('D1', '投诉内容');
        $sheet->setCellValue('E1', '状态');
        $sheet->setCellValue('F1', '处理备注');
        $sheet->setCellValue('G1', '投诉时间');

        // 填充数据
        $row = 2;
        foreach ($complaints as $complaint) {
            $sheet->setCellValue('A' . $row, $complaint->id);
            $sheet->setCellValue('B' . $row, $complaint->user->name);
            $sheet->setCellValue('C' . $row, $complaint->user->phone);
            $sheet->setCellValue('D' . $row, $complaint->content);
            $sheet->setCellValue('E' . $row, $this->formatComplaintStatus($complaint->status));
            $sheet->setCellValue('F' . $row, $complaint->admin_remark ?? '-');
            $sheet->setCellValue('G' . $row, $complaint->created_at);
            $row++;
        }

        return $this->saveSpreadsheet($spreadsheet, 'complaints');
    }

    /**
     * 保存电子表格
     */
    protected function saveSpreadsheet(Spreadsheet $spreadsheet, string $prefix): string
    {
        $filename = $prefix . '_' . date('YmdHis') . '.xlsx';
        $path = 'exports/' . $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save(storage_path('app/public/' . $path));

        return Storage::disk('public')->url($path);
    }

    /**
     * 格式化支付方式
     */
    protected function formatPaymentType(string $type): string
    {
        return [
            'wechat' => '微信支付',
            'alipay' => '支付宝',
            'auth_code' => '授权码'
        ][$type] ?? $type;
    }

    /**
     * 格式化支付状态
     */
    protected function formatPaymentStatus(string $status): string
    {
        return [
            'pending' => '待支付',
            'paid' => '已支付',
            'failed' => '已失败',
            'refunded' => '已退款'
        ][$status] ?? $status;
    }

    /**
     * 格式化收益状态
     */
    protected function formatEarningStatus(string $status): string
    {
        return [
            'pending' => '待结算',
            'settled' => '已结算',
            'refunded' => '已退款'
        ][$status] ?? $status;
    }

    /**
     * 格式化投诉状态
     */
    protected function formatComplaintStatus(string $status): string
    {
        return [
            'pending' => '待处理',
            'processing' => '处理中',
            'resolved' => '已解决'
        ][$status] ?? $status;
    }
} 