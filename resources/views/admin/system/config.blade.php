@extends('admin.layouts.app')

@section('title', '系统配置')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <!-- 基础配置 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">基础配置</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.system.config.update') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>系统名称</label>
                                    <input type="text" name="app_name" class="form-control" 
                                        value="{{ config('app.name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>客服电话</label>
                                    <input type="tel" name="service_phone" class="form-control" 
                                        value="{{ config('system.service_phone') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>查询费用</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">￥</span>
                                        </div>
                                        <input type="number" name="query_fee" class="form-control" 
                                            value="{{ config('system.query_fee') }}" 
                                            min="0" step="0.01" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>支付超时时间</label>
                                    <div class="input-group">
                                        <input type="number" name="payment_timeout" class="form-control" 
                                            value="{{ config('system.payment_timeout') }}" 
                                            min="1" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">分钟</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>最大查询次数</label>
                                    <input type="number" name="max_query_times" class="form-control" 
                                        value="{{ config('system.max_query_times') }}" 
                                        min="0" required>
                                    <small class="form-text text-muted">0表示不限制</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>查询结果有效期</label>
                                    <div class="input-group">
                                        <input type="number" name="result_expire_days" class="form-control" 
                                            value="{{ config('system.result_expire_days') }}" 
                                            min="1" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">天</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-4">保存配置</button>
                    </form>
                </div>
            </div>

            <!-- 短信配置 -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">短信配置</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.system.config.sms') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>短信服务商</label>
                                    <select name="sms_provider" class="form-control" required>
                                        <option value="aliyun" 
                                            {{ config('sms.default') == 'aliyun' ? 'selected' : '' }}>
                                            阿里云
                                        </option>
                                        <option value="tencent" 
                                            {{ config('sms.default') == 'tencent' ? 'selected' : '' }}>
                                            腾讯云
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>短信签名</label>
                                    <input type="text" name="sms_sign" class="form-control" 
                                        value="{{ config('sms.sign') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>AccessKey ID</label>
                                    <input type="text" name="sms_key" class="form-control" 
                                        value="{{ config('sms.key') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>AccessKey Secret</label>
                                    <input type="password" name="sms_secret" class="form-control" 
                                        value="{{ config('sms.secret') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>验证码模板ID</label>
                                    <input type="text" name="sms_template_code" class="form-control" 
                                        value="{{ config('sms.templates.code') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>通知模板ID</label>
                                    <input type="text" name="sms_template_notify" class="form-control" 
                                        value="{{ config('sms.templates.notify') }}" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-4">保存配置</button>
                        <button type="button" class="btn btn-info mt-4 ml-2" onclick="testSms()">
                            发送测试短信
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- 支付配置 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">支付配置</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.system.config.payment') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>微信支付商户号</label>
                            <input type="text" name="wechat_mch_id" class="form-control" 
                                value="{{ config('payment.wechat.mch_id') }}" required>
                        </div>

                        <div class="form-group mt-3">
                            <label>微信支付密钥</label>
                            <input type="password" name="wechat_key" class="form-control" 
                                value="{{ config('payment.wechat.key') }}" required>
                        </div>

                        <div class="form-group mt-3">
                            <label>支付宝应用ID</label>
                            <input type="text" name="alipay_app_id" class="form-control" 
                                value="{{ config('payment.alipay.app_id') }}" required>
                        </div>

                        <div class="form-group mt-3">
                            <label>支付宝私钥</label>
                            <textarea name="alipay_private_key" class="form-control" 
                                rows="3" required>{{ config('payment.alipay.private_key') }}</textarea>
                        </div>

                        <div class="form-group mt-3">
                            <label>支付宝公钥</label>
                            <textarea name="alipay_public_key" class="form-control" 
                                rows="3" required>{{ config('payment.alipay.public_key') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary mt-4">保存配置</button>
                    </form>
                </div>
            </div>

            <!-- 其他配置 -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">其他配置</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.system.config.other') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>用户协议</label>
                            <textarea name="user_agreement" class="form-control" 
                                rows="5">{{ config('system.user_agreement') }}</textarea>
                        </div>

                        <div class="form-group mt-3">
                            <label>隐私政策</label>
                            <textarea name="privacy_policy" class="form-control" 
                                rows="5">{{ config('system.privacy_policy') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary mt-4">保存配置</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 测试短信弹窗 -->
<div class="modal fade" id="testSmsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">发送测试短信</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="testSmsForm">
                    <div class="form-group">
                        <label>手机号</label>
                        <input type="tel" name="phone" class="form-control" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="sendTestSms()">发送</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// 发送测试短信
function testSms() {
    $('#testSmsForm')[0].reset();
    $('#testSmsModal').modal('show');
}

function sendTestSms() {
    let formData = new FormData($('#testSmsForm')[0]);
    formData.append('_token', '{{ csrf_token() }}');

    $.post('{{ route('admin.system.config.sms.test') }}', 
        Object.fromEntries(formData), 
        function(response) {
            if (response.success) {
                $('#testSmsModal').modal('hide');
                toastr.success('测试短信已发送');
            } else {
                toastr.error(response.message || '发送失败');
            }
        }
    );
}
</script>
@endpush 