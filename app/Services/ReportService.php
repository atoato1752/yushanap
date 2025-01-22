<?php

namespace App\Services;

use App\Models\Query;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Exception;

class ReportService
{
    protected $logService;
    protected $fileService;
    protected $disk;
    protected $path;

    public function __construct(LogService $logService, FileService $fileService)
    {
        $this->logService = $logService;
        $this->fileService = $fileService;
        $this->disk = config('yushan.report.storage_disk');
        $this->path = config('yushan.report.storage_path');
    }

    /**
     * 生成信用报告
     */
    public function generate(Query $query)
    {
        // 生成报告内容
        $content = $this->buildContent($query);

        // 保存报告文件
        $filename = $this->generateFilename($query);
        Storage::disk($this->disk)->put(
            $this->path . '/' . $filename,
            $content
        );

        // 更新查询记录
        $query->update([
            'report_path' => $this->path . '/' . $filename,
            'report_generated_at' => now(),
        ]);

        return $query;
    }

    protected function buildContent(Query $query)
    {
        // 构建报告内容
        $data = [
            'query_id' => $query->id,
            'name' => $query->name,
            'id_card' => $query->id_card,
            'query_time' => $query->created_at->format('Y-m-d H:i:s'),
            'result' => $query->result,
        ];

        // 这里可以使用视图或PDF库来生成报告
        return view('reports.template', $data)->render();
    }

    protected function generateFilename(Query $query)
    {
        return sprintf(
            '%s_%s_%s.pdf',
            $query->id,
            Str::slug($query->name),
            date('Ymd')
        );
    }

    public function download(Query $query)
    {
        if (!$query->report_path || !Storage::disk($this->disk)->exists($query->report_path)) {
            throw new \Exception('报告文件不存在');
        }

        return Storage::disk($this->disk)->download(
            $query->report_path,
            $this->generateFilename($query)
        );
    }

    public function delete(Query $query)
    {
        if ($query->report_path && Storage::disk($this->disk)->exists($query->report_path)) {
            Storage::disk($this->disk)->delete($query->report_path);
        }
    }

    /**
     * 获取报告访问URL
     */
    public function getReportUrl(Query $query): string
    {
        $filename = 'reports/' . date('Ymd', strtotime($query->created_at)) . '/' . $query->id . '.docx';
        
        if (!Storage::disk('public')->exists($filename)) {
            throw new Exception('报告文件不存在');
        }

        return Storage::disk('public')->url($filename);
    }

    /**
     * 删除报告文件
     */
    public function deleteReport(Query $query): void
    {
        $filename = 'reports/' . date('Ymd', strtotime($query->created_at)) . '/' . $query->id . '.docx';
        
        if (Storage::disk('public')->exists($filename)) {
            Storage::disk('public')->delete($filename);
            
            $this->logService->operation('report', 'delete', [
                'query_id' => $query->id,
                'file' => $filename
            ]);
        }
    }
}