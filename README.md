# bKash Laravel Package

Welcome to the **bKash Laravel Package**!  
This package allows seamless integration with the bKash payment gateway in Laravel, making transactions quick and hassle-free.

It supports Checkout (URL), Auth & Capture, Refund, and Search Transactions with easy configuration.
Built-in automatic grantToken caching (1 hour) ensures full compliance with the latest bKash Tokenized API rules and prevents account lock issues.

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

### Enable or Disable bKash Log
You can turn bKash logging on or off. (Only sandbox/testing mode.)

Logs will be saved in the /storage/logs/laravel.log file.

```bash
"bkash_log_enabled" => env("BKASH_SANDBOX", false),
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
    public function createPayment(Request $request)
    {
        $request->validate([
            'payment_uid' => 'required',
            'amount' => 'required',
            'invoice_no' => 'required',
        ]);
            
        $data = [
            'payerReference' => $request->get('payment_uid'), // your payments table uid
            'amount' => $request->get('amount'),
            'merchantInvoiceNumber' => $request->get('invoice_no'),
            'callbackURL' => $request->get('bkash_callback_url'), // optional
        ];

        $response = (array) BkashPayment::create($data);

        if (isset($response['bkashURL'])) return redirect()->away($response['bkashURL']);
        else return redirect()->back()->with('error-alert2', $response['statusMessage']);
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

$response = BkashPayment::create($request);
return redirect($response->bkashURL);
```

### [Capture](https://developer.bka.sh/docs/auth-capture-process-overview)

```
BkashPayment::capture($paymentID);
```

### [Void](https://developer.bka.sh/docs/void)

```
BkashPayment::void($paymentID);
```
