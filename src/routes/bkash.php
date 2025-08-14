<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BkashPaymentController;

Route::group(['middleware' => ['web']], static function () {
    // Payment routes for bKash
    Route::get('/bkash/payment', [BkashPaymentController::class, 'index']);
    Route::get('/bkash/create-payment', [BkashPaymentController::class, 'createPayment'])->name('bkash.payment.create');
    Route::get('/bkash/callback', [BkashPaymentController::class, 'callBack'])->name('bkash.payment.callback');

    Route::get("bkash/failed", [BkashPaymentController::class, 'failed'])->name('bkash.payment.fail');
    Route::get("bkash/success", [BkashPaymentController::class, 'success'])->name('bkash.payment.success');

    // Search payment
    Route::get('/bkash/search/{trxID}', [BkashPaymentController::class, 'searchTnx'])->name('bkash.payment.search');

    // Refund payment routes
    Route::get('/bkash/refund', [BkashPaymentController::class, 'refund'])->name('bkash.payment.refund');
    Route::get('/bkash/refund/status', [BkashPaymentController::class, 'refundStatus'])->name('bkash.payment.refund.status');
});







