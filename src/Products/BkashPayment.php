<?php

namespace ProgrammerHasan\Bkash\Products;

use ProgrammerHasan\Bkash\app\Service\PaymentService;

class BkashPayment
{
    private PaymentService $checkoutUrl;

    public function __construct()
    {
        $this->checkoutUrl = new PaymentService();
    }

    public function create($request = [])
    {
        return $this->checkoutUrl->createPayment($request);
    }

    public function execute($paymentID)
    {
        return $this->checkoutUrl->executePayment($paymentID);
    }

    public function verify($paymentID)
    {
        return $this->checkoutUrl->verifyPayment($paymentID);
    }

    public function query($paymentID)
    {
        return $this->checkoutUrl->queryPayment($paymentID);
    }

    public function search($trxID)
    {
        return $this->checkoutUrl->searchTransaction($trxID);
    }

    public function refund($paymentID, $trxID, $amount)
    {
        return $this->checkoutUrl->refundTransaction($paymentID, $trxID, $amount);
    }

    public function capture($paymentID)
    {
        return $this->checkoutUrl->capturePayment($paymentID);
    }

    public function void($paymentID)
    {
        return $this->checkoutUrl->voidPayment($paymentID);
    }

    public function failed($message)
    {
        return $this->checkoutUrl->failed($message);
    }

    public function success($txrID)
    {
        return $this->checkoutUrl->success($txrID);
    }

}
