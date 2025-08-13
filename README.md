# bKash Laravel Package

Welcome to the **bKash Laravel Package**!  
This package allows seamless integration with the bKash payment gateway in Laravel, making transactions quick and hassle-free.

---

## 📦 Installation

```bash
composer require programmerhasan/bkash
```

### vendor publish (config)

```bash
php artisan vendor:publish --provider="ProgrammerHasan\Bkash\BkashServiceProvider"
```

### Set .env configuration

```bash
 BKASH_SANDBOX=true
 BKASH_USERNAME = ''
 BKASH_PASSWORD = ''
 BKASH_APP_KEY = ''
 BKASH_APP_SECRET = ''
 BKASH_CALLBACK_URL='Your defined Callback URl //default Callback Url => http://127.0.0.1:8000/bkash/callback'
```

### Generate the Controller

```bash
php artisan make:controller Payment/BkashPaymentController
```

## [Checkout (URL Based)](https://developer.bka.sh/docs/checkout-url-process-overview)

### 1. Create Payment

```
<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use ProgrammerHasan\Bkash\Facade\CheckoutUrl;

class BkashPaymentController extends Controller
{
    public function pay(Request $request)
    {
        $request['payerReference'] = $paymentUid;
        $request['amount'] = $amount;
        $request['merchantInvoiceNumber'] = $paymentUid;
        $request['callbackURL'] = $onBkashCallbackURL;

        $request_data_json = json_encode($request);
        
        $response = BkashPayment::create($request_data_json);
        return redirect($response->bkashURL);
    }
}
```

### 2. ADD callback function

```
public function callback(Request $request)
{
    $status = $request->input('status');
    $paymentId = $request->input('paymentID');

    if ($status === 'success')
    {
        $response = BkashPayment::verify($paymentId);

        if ($response->statusCode !== '0000')
        {
        return BkashPayment::failed($response->statusMessage);
        }

        if (isset($response->transactionStatus)&&($response->transactionStatus=='Completed'||$response->transactionStatus=='Authorized'))
        {
             //Database Insert Operation
            return BkashPayment::success($response->trxID."({$response->transactionStatus})");
        }
        else if($response->transactionStatus=='Initiated')
        {
            return BkashPayment::failed("Try Again");
        }
    }

    else
    {
      return BkashPayment::failed($status);
    }
}
```

### 3. ADD routes in Web.php

```
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
```

### 4. Use route('bkash.pay') in blade

```
<form action="{{ route('bkash.payment.create') }}" method="POST">
        @csrf
        <button type="submit">Pay with bkash</button>
    </form>
```

### For refund Transaction

```
 public function refund(Request $request)
    {
        return BkashPayment::refund(paymentID,$trxID,$amountToRefund);
    }
```

## [Auth & Capture (URL)](https://developer.bka.sh/docs/auth-capture-process-overview)

### Create Payment

```
$request['payerReference'] = $paymentUid;
$request['amount'] = $amount;
$request['merchantInvoiceNumber'] = $paymentUid;
$request['callbackURL'] = $onBkashCallbackURL;

$request_data_json = json_encode($request);

$response = BkashPayment::create($request_data_json);
return redirect($response['bkashURL']);
```

### [Capture](https://developer.bka.sh/docs/auth-capture-process-overview)

```
BkashPayment::capture($paymentID);
```

### [Void](https://developer.bka.sh/docs/void)

```
BkashPayment::void($paymentID);
```
