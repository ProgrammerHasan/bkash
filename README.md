# BKash Payment Gateway for Laravel

Welcome to the **bKash Laravel Package**!  
This package allows seamless integration with the bKash payment gateway in Laravel, making transactions quick and hassle-free.

It supports Checkout (URL), Auth & Capture, Refund, and Search Transactions with easy configuration.
Built-in automatic grantToken caching (1 hour) ensures full compliance with the latest bKash Tokenized API rules and prevents account lock issues.

---

## ✨ Features
- ⚡ **Clean and easy to integrate** with any Laravel project.
- 🧪 Supports **sandbox and production mode** for Bkash payments.
- 🔑 Supports **Auth & Capture** for tokenized payments.
- ↩️ Supports **Refunds** and **Search Transactions**.
- ⚙️ Built-in **automatic grantToken caching (1 hour)** to comply with bKash Tokenized API rules.
- 💰 Enables creating, executing, and querying **payments** via Bkash API.
- 📄 Provides **payment status verification** and transaction history.
- 📝 Enables logging of Bkash payment activities in **sandbox mode only**.
- 🛠️ Follows **Laravel conventions** for service providers and facades.
- 🧩 Provides clean **helper methods** for easy integration into controllers and services.

## 🛠️ Requirements
- 🐘 **PHP:** ^7.4 | ^8.0 | ^8.1 | ^8.2
- ⚡ **Laravel (illuminate/support):** ~6 | ~7 | ~8 | ~9 | ^10 | ^11 | ^12
- 🌐 **cURL enabled** in PHP
- 🔑 **bKash Merchant Account** (sandbox or production)

## 📦 Installation

```bash
composer require programmerhasan/bkash
```

### ⚙️ Vendor publish (config)

```bash
php artisan vendor:publish --provider="ProgrammerHasan\Bkash\BkashServiceProvider" --tag="config"
```
After publish config file setup your credential. you can see this in your config directory bkash.php file
```
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enable or Disable bKash Log
    |--------------------------------------------------------------------------
    | Logging is only recommended in sandbox/testing mode.
    | In production, keep logging disabled to avoid exposing sensitive payment data.
    | Logs will be saved in the /storage/logs/laravel.log file.
    |
    | Usage:
    |   - Sandbox: BKASH_SANDBOX=true → logs enabled
    |   - Production: BKASH_SANDBOX=false → logs disabled
    */
    "bkash_log_enabled" => env("BKASH_SANDBOX", false),

    "bkash_sandbox" => env("BKASH_SANDBOX", false),
    "bkash_username" => env("BKASH_USERNAME"),
    "bkash_password" => env("BKASH_PASSWORD"),
    "bkash_app_key" => env("BKASH_APP_KEY"),
    "bkash_app_secret" => env("BKASH_APP_SECRET"),
    "bkash_base_url_sandbox" => "https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized",
    "bkash_base_url_production" => "https://tokenized.Pay.bka.sh/v1.2.0-beta/tokenized",
    "bkash_callback_url" => env("BKASH_CALLBACK_URL", "http://127.0.0.1:8000/bkash/callback"),
];
```

### 📝 Set .env configuration
Add your Bkash credentials and environment settings in the `.env` file:
```bash
 BKASH_SANDBOX=true
 BKASH_USERNAME = ''
 BKASH_PASSWORD = ''
 BKASH_APP_KEY = ''
 BKASH_APP_SECRET = ''
 BKASH_CALLBACK_URL='Your defined Callback URl //default Callback Url => http://127.0.0.1:8000/bkash/callback'
```
### 📝 Enable or Disable bKash Log
You can turn bKash logging on or off. (Only sandbox/testing mode.)

Logs will be saved in the /storage/logs/laravel.log file.

```bash
"bkash_log_enabled" => env("BKASH_SANDBOX", false),
```

## 🚀 Usage
### Publish a route

```bash
php artisan vendor:publish --provider="ProgrammerHasan\Bkash\BkashServiceProvider" --tag="routes"
```
"bkash.php" Route include in web.php

### Publish a controller

```bash
php artisan vendor:publish --provider="ProgrammerHasan\Bkash\BkashServiceProvider" --tag="controllers"
```
### You can override the routes
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

### Use route('bkash.payment.create') in blade

```
<form action="{{ route('bkash.payment.create') }}" method="POST">
        @csrf
        <button type="submit">Pay with bkash</button>
    </form>
```

## [Checkout (URL Based)](https://developer.bka.sh/docs/checkout-url-process-overview)

### 1. Create Payment

```
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
```
### Create payment response
```
array[
  "statusCode" => "0000"
  "statusMessage" => "Successful"
  "paymentID" => "Your payment id"
  "bkashURL" => "https://sandbox.payment.bkash.com/redirect/tokenized/?paymentID=your_payment_id&hash=your_hash"
  "callbackURL" => "base_url/bkash/callback"
  "successCallbackURL" => "base_url/bkash/callback?paymentID=your_payment_id&status=success"
  "failureCallbackURL" => "base_url/bkash/callback?paymentID=your_payment_id&status=failure"
  "cancelledCallbackURL" => "base_url/bkash/callback?paymentID=your_payment_id&status=cancel"
  "amount" => "100"
  "intent" => "sale"
  "currency" => "BDT"
  "paymentCreateTime" => "2025-07-22T02:16:57:784 GMT+0600"
  "transactionStatus" => "Initiated"
  "merchantInvoiceNumber" => "merchant_invoice_no"
]
```

### 2. Add callback function

```
public function callback(Request $request)
{
    // Callback request params
    // paymentID=your_payment_id&status=success&apiVersion=1.2.0-beta
    // using paymentID find the account number for sending params
        
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
### Execute payment response

```
{
   "statusCode":"0000",
   "statusMessage":"Successful",
   "paymentID":"your_payment_id",
   "payerReference":"your_ref_id",
   "customerMsisdn":"customer_msi",
   "trxID":"your_tnx_id",
   "amount":"100",
   "transactionStatus":"Completed",
   "paymentExecuteTime":"2023-01-23T02:04:05:736 GMT+0600",
   "currency":"BDT",
   "intent":"sale"
}
```

### Query payment response

```
{
   "paymentID":"your_payment_id",
   "mode":"0011",
   "paymentCreateTime":"2023-01-23T02:01:06:713 GMT+0600",
   "paymentExecuteTime":"2023-01-23T02:04:05:736 GMT+0600",
   "amount":"100",
   "currency":"BDT",
   "intent":"sale",
   "merchantInvoice":"merchant_inv_no",
   "trxID":"tnx_no",
   "transactionStatus":"Completed",
   "verificationStatus":"Complete",
   "statusCode":"0000",
   "statusMessage":"Successful",
   "payerReference":"pay_ref"
}
```
### 3. Search Transaction
```
public function searchTnx($trxID)
{
   return BkashPayment::searchTransaction($trxID);
}
```
### Response
```
 {
   "trxID":"tnx_no",
   "initiationTime":"2023-01-23T12:06:05:000 GMT+0600",
   "completedTime":"2023-01-23T12:06:05:000 GMT+0600",
   "transactionType":"bKash Tokenized Checkout via API",
   "customerMsisdn":"customer_msi",
   "transactionStatus":"Completed",
   "amount":"20",
   "currency":"BDT",
   "organizationShortCode":"og_short_code",
   "statusCode":"0000",
   "statusMessage":"Successful"
 }
```

### 4. Refund Transaction

```
public function refund(Request $request)
{
    $paymentID = 'Your payment id';
    $trxID = 'your transaction no';
    $amount = 5;
    $reason = 'this is test reason';
    $sku = 'abc';
    return BkashPayment::refund($paymentID, $trxID, $amount, $reason, $sku);
}
```
### Response
```
 {
    "statusCode":"0000",
    "statusMessage":"Successful",
    "originalTrxID":"or_tnx_no",
    "refundTrxID":"refund_tnx",
    "transactionStatus":"Completed",
    "amount":"5",
    "currency":"BDT",
    "charge":"0.00",
    "completedTime":"2023-01-23T15:53:29:120 GMT+0600"
 }
```
### 5. Refund status check
```
public function refundStatus(Request $request)
{
    $paymentID = 'Your payment id';
    $trxID = 'your transaction no';
    return BkashPayment::refundStatus($paymentID, $trxID);
}
```
### Response
```
{
    "statusCode":"0000",
    "statusMessage":"Successful",
    "originalTrxID":"ori_tx",
    "refundTrxID":"ref_tx",
    "transactionStatus":"Completed",
    "amount":"5",
    "currency":"BDT",
    "charge":"0.00",
    "completedTime":"2023-01-23T15:53:29:120 GMT+0600"
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

---

### Required APIs
0. **Developer Portal** (detail Product, workflow, API information): https://developer.bka.sh/docs/checkout-process-overview
1. **Grant Token :** https://developer.bka.sh/v1.2.0-beta/reference#gettokenusingpost
2. **Create Payment :** https://developer.bka.sh/v1.2.0-beta/reference#createpaymentusingpost
3. **Execute Payment :** https://developer.bka.sh/v1.2.0-beta/reference#executepaymentusingpost
4. **Query Payment :** https://developer.bka.sh/v1.2.0-beta/reference#querypaymentusingget
5. **Search Transaction Details :** https://developer.bka.sh/v1.2.0-beta/reference#searchtransactionusingget

### Tokenized Checkout (v2) Demo
0. Bkash: https://merchantdemo.sandbox.bka.sh/
1. Go to https://merchantdemo.sandbox.bka.sh/tokenized-checkout/version/v2
2. **Wallet Number:** 01770618575
3. **OTP:** 123456
4. **Pin:** 12121

## License

This repository is licensed under the [MIT License](http://opensource.org/licenses/MIT).

Copyright 2025 [ProgrammerHasan](https://github.com/ProgrammerHasan). 