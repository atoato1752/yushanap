@extends('admin.layouts.app')

@section('title', '添加代理商')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">添加代理商</h5>
            <a href="{{ route('admin.agents.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>返回列表
            </a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.agents.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <!-- 基本信息 -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">基本信息</h6>
                        <div class="mb-3">
                            <label class="form-label">用户名</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                   name="username" value="{{ old('username') }}" required>
                            <div class="form-text">用于登录系统，创建后不可修改</div>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">姓名</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">密码</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   name="password" placeholder="留空则使用默认密码：123456">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">上级代理</label>
                            <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                                <option value="">无</option>
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}" {{ old('parent_id') == $agent->id ? 'selected' : '' }}>
                                        {{ $agent->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <!-- 价格设置 -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">价格设置</h6>
                        <div class="mb-3">
                            <label class="form-label">成本价</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control @error('cost_price') is-invalid @enderror" 
                                       name="cost_price" value="{{ old('cost_price') }}" step="0.01" required>
                            </div>
                            @error('cost_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">销售价</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control @error('selling_price') is-invalid @enderror" 
                                       name="selling_price" value="{{ old('selling_price') }}" step="0.01" required>
                            </div>
                            @error('selling_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">状态</label>
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="status" value="1" 
                                       {{ old('status', true) ? 'checked' : '' }}>
                                <label class="form-check-label">启用</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>保存
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 