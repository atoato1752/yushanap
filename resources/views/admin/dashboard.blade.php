@extends('admin.layouts.app')

@section('title', '控制台')

@section('content')
<div class="container-fluid">
    <!-- 统计卡片 -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">今日查询</h6>
                            <h4 class="mb-0">{{ $stats['today_queries'] }}</h4>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-search fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-{{ $stats['query_trend'] >= 0 ? 'success' : 'danger' }}">
                            <i class="fas fa-{{ $stats['query_trend'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                            {{ abs($stats['query_trend']) }}%
                        </span>
                        <span class="text-muted ml-2">较昨日</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">今日收入</h6>
                            <h4 class="mb-0">￥{{ number_format($stats['today_income'], 2) }}</h4>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-{{ $stats['income_trend'] >= 0 ? 'success' : 'danger' }}">
                            <i class="fas fa-{{ $stats['income_trend'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                            {{ abs($stats['income_trend']) }}%
                        </span>
                        <span class="text-muted ml-2">较昨日</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">待处理投诉</h6>
                            <h4 class="mb-0">{{ $stats['pending_complaints'] }}</h4>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-exclamation-circle fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.complaints.index', ['status' => 'pending']) }}" 
                            class="text-muted">查看详情 <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">待审核代理商</h6>
                            <h4 class="mb-0">{{ $stats['pending_agents'] }}</h4>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-user-clock fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.agents.index', ['status' => 'pending']) }}" 
                            class="text-muted">查看详情 <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 图表 -->
    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">查询趋势</h5>
                </div>
                <div class="card-body">
                    <canvas id="queryChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">代理商排行</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>代理商</th>
                                    <th>查询量</th>
                                    <th>收益</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['agent_rankings'] as $agent)
                                <tr>
                                    <td>{{ $agent->name }}</td>
                                    <td>{{ $agent->query_count }}</td>
                                    <td>￥{{ number_format($agent->earnings_sum, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 最新记录 -->
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">最新查询</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>查询对象</th>
                                    <th>状态</th>
                                    <th>时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['latest_queries'] as $query)
                                <tr>
                                    <td>{{ $query->name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $query->status_color }}">
                                            {{ $query->status_text }}
                                        </span>
                                    </td>
                                    <td>{{ $query->created_at->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">最新投诉</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>投诉对象</th>
                                    <th>类型</th>
                                    <th>状态</th>
                                    <th>时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['latest_complaints'] as $complaint)
                                <tr>
                                    <td>{{ $complaint->query->name }}</td>
                                    <td>{{ $complaint->type_text }}</td>
                                    <td>
                                        <span class="badge badge-{{ $complaint->status_color }}">
                                            {{ $complaint->status_text }}
                                        </span>
                                    </td>
                                    <td>{{ $complaint->created_at->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // 查询趋势图表
    const ctx = document.getElementById('queryChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($stats['chart_labels']) !!},
            datasets: [{
                label: '查询量',
                data: {!! json_encode($stats['chart_data']['queries']) !!},
                borderColor: '#4e73df',
                tension: 0.1
            }, {
                label: '收入',
                data: {!! json_encode($stats['chart_data']['income']) !!},
                borderColor: '#1cc88a',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush 