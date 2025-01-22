@extends('admin.layouts.app')

@section('title', '代理商收益')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">代理商收益</h5>
            <a href="{{ route('admin.agents.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>返回列表
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- 代理商信息 -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="card-title">代理商</h6>
                        <p class="h4">{{ $agent->name }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-title">总收益</h6>
                        <p class="h4">¥{{ number_format($agent->earnings()->sum('amount'), 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="card-title">当前余额</h6>
                        <p class="h4">¥{{ number_format($agent->balance, 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6 class="card-title">查询次数</h6>
                        <p class="h4">{{ $agent->queries()->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 搜索表单 -->
        <form method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">结算状态</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>待结算</option>
                        <option value="settled" {{ request('status') == 'settled' ? 'selected' : '' }}>已结算</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="date_range" class="form-control" 
                           placeholder="日期范围" value="{{ request('date_range') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">搜索</button>
                </div>
            </div>
        </form>

        <!-- 收益列表 -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>查询用户</th>
                        <th>查询时间</th>
                        <th>收益金额</th>
                        <th>结算状态</th>
                        <th>结算时间</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($earnings as $earning)
                    <tr>
                        <td>{{ $earning->id }}</td>
                        <td>{{ $earning->query->user->name }}</td>
                        <td>{{ $earning->query->created_at }}</td>
                        <td>¥{{ number_format($earning->amount, 2) }}</td>
                        <td>
                            @if($earning->status == 'pending')
                                <span class="badge bg-warning">待结算</span>
                            @else
                                <span class="badge bg-success">已结算</span>
                            @endif
                        </td>
                        <td>{{ $earning->settled_at ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- 分页 -->
        <div class="mt-4">
            {{ $earnings->withQueryString()->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // 日期范围选择器
    $('input[name="date_range"]').daterangepicker({
        locale: {
            format: 'YYYY-MM-DD',
            applyLabel: '确定',
            cancelLabel: '取消',
            fromLabel: '从',
            toLabel: '至'
        },
        autoUpdateInput: false
    });

    $('input[name="date_range"]').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
    });

    $('input[name="date_range"]').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
});
</script>
@endpush 