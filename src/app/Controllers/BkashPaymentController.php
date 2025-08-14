<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ProgrammerHasan\Bkash\Facade\BkashPayment;
use Illuminate\Routing\Controller;

class BkashPaymentController extends Controller
{
    public function index()
    {
        return view('bkash::payment');
    }

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

        // or you can use like : $response = BkashPayment::create($data);
        // return $response->bkashURL;
    }

    public function callback(Request $request)
    {
        $status = $request->input('status');
        $paymentId = $request->input('paymentID');

        if ($status === 'success') {
            $response = BkashPayment::verify($paymentId);

            if ($response->statusCode !== '0000') {
                return BkashPayment::failed($response->statusMessage);
            }

            if (isset($response->transactionStatus) && ($response->transactionStatus == 'Completed' || $response->transactionStatus == 'Authorized')) {
                //Database Insert Operation
                return BkashPayment::success($response->trxID . "({$response->transactionStatus})");
            } else if ($response->transactionStatus == 'Initiated') {

                return BkashPayment::failed("Try Again");
            }
        } else {
            return BkashPayment::failed($status);
        }
    }

    public function searchTnx($trxID)
    {
        return BkashPayment::searchTransaction($trxID);
    }

    public function refund(Request $request)
    {
        $paymentID = 'Your payment id';
        $trxID = 'your transaction no';
        $amount = 5;
        $reason = 'this is test reason';
        $sku = 'abc';
        return BkashPayment::refund($paymentID, $trxID, $amount, $reason, $sku);
    }

    public function refundStatus(Request $request)
    {
        $paymentID = 'Your payment id';
        $trxID = 'your transaction no';
        return BkashPayment::refundStatus($paymentID, $trxID);
    }

    public function failed()
    {
        return view('bkash::fail');
    }

    public function success()
    {
        return view('bkash::success');
    }
}
