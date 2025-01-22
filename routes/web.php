<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\QueryController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\ComplaintController;
use App\Http\Controllers\Admin\SystemConfigController;

// 前台路由
Route::get('/', function () {
    return view('query.index');
});

Route::post('/sms/send', [SmsController::class, 'send'])->name('sms.send');
Route::post('/query', [QueryController::class, 'store'])->name('query.store');

// 支付回调
Route::post('/payment/notify/{type}', [PaymentController::class, 'notify'])
    ->name('payment.notify');

// 后台路由组
Route::prefix('admin')->name('admin.')->group(function () {
    // 登录相关
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // 需要认证的路由
    Route::middleware(['auth:admin', 'admin.role:admin,super_admin'])->group(function () {
        // 控制台
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // 查询记录管理
        Route::get('queries', [QueryController::class, 'index'])->name('queries.index');
        Route::get('queries/{query}', [QueryController::class, 'show'])->name('queries.show');
        Route::get('queries/export', [QueryController::class, 'export'])->name('queries.export');

        // 代理商管理
        Route::resource('agents', AgentController::class);

        // 投诉管理
        Route::get('complaints', [ComplaintController::class, 'index'])->name('complaints.index');
        Route::put('complaints/{complaint}', [ComplaintController::class, 'update'])->name('complaints.update');

        // 系统设置
        Route::get('system/config', [SystemConfigController::class, 'index'])->name('system.config');
        Route::put('system/config', [SystemConfigController::class, 'update'])->name('system.config.update');
    });
}); 