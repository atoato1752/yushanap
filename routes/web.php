<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\QueryController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\ComplaintController;
use App\Http\Controllers\Admin\SystemConfigController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;

// 前台路由
Route::get('/', [HomeController::class, 'index'])->name('home');

// 认证路由
Auth::routes(['verify' => true]);

// 需要登录的路由
Route::middleware(['auth'])->group(function () {
    // 查询
    Route::resource('queries', QueryController::class);
    Route::post('queries/{query}/pay', [QueryController::class, 'pay'])->name('queries.pay');
    Route::get('queries/{query}/download', [QueryController::class, 'download'])->name('queries.download');
    
    // 个人中心
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

// 静态页面
Route::view('about', 'about')->name('about');
Route::view('contact', 'contact')->name('contact');
Route::view('help', 'help')->name('help');
Route::view('privacy', 'privacy')->name('privacy');
Route::view('terms', 'terms')->name('terms');

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