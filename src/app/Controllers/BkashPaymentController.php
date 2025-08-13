<?php

namespace ProgrammerHasan\Bkash\App\Controllers;

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
        $inv = uniqid();
        $request['intent'] = 'sale';
        $request['mode'] = '0011'; //0011 for checkout
        $request['payerReference'] = $inv;
        $request['currency'] = 'BDT';
        $request['amount'] = 10;
        $request['merchantInvoiceNumber'] = $inv;
        $request['callbackURL'] = config("bkash.bkash_callback_url");

        $request_data_json = json_encode($request->all());

        $response = BkashPayment::create($request_data_json);

        if (isset($response['bkashURL'])) return redirect()->away($response['bkashURL']);
        else return redirect()->back()->with('error-alert2', $response['statusMessage']);
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
