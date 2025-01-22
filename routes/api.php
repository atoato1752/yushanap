<?php

use App\Http\Controllers\Api\QueryController;
use App\Http\Controllers\Api\ComplaintController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // 查询相关接口
    Route::post('queries', [QueryController::class, 'store']);
    Route::get('queries/{id}', [QueryController::class, 'show']);
    
    // 支付相关接口
    Route::post('payments/wechat/{query}', [PaymentController::class, 'wechat']);
    Route::post('payments/alipay/{query}', [PaymentController::class, 'alipay']);
    Route::post('payments/auth-code/{query}', [PaymentController::class, 'authCode']);
    
    // 投诉相关接口
    Route::post('complaints', [ComplaintController::class, 'store']);
});

// 支付回调接口
Route::post('payments/notify/{type}', [PaymentController::class, 'notify']); 