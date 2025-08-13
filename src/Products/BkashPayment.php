<?php

namespace ProgrammerHasan\Bkash\Products;

use ProgrammerHasan\Bkash\app\Service\PaymentService;

class BkashPayment
{
    private PaymentService $paymentService;

    public function __construct()
    {
        $this->paymentService = new PaymentService();
    }

    public function create($request = [])
    {
        return $this->paymentService->createPayment($request);
    }

    public function execute($paymentID)
    {
        return $this->paymentService->executePayment($paymentID);
    }

    public function verify($paymentID)
    {
        return $this->paymentService->verifyPayment($paymentID);
    }

    public function query($paymentID)
    {
        return $this->paymentService->queryPayment($paymentID);
    }

    public function search($trxID)
    {
        return $this->paymentService->searchTransaction($trxID);
    }

    public function refund($paymentID, $trxID, $amount)
    {
        return $this->paymentService->refundTransaction($paymentID, $trxID, $amount);
    }

    public function capture($paymentID)
    {
        return $this->paymentService->capturePayment($paymentID);
    }

    public function void($paymentID)
    {
        return $this->paymentService->voidPayment($paymentID);
    }

    public function failed($message)
    {
        return $this->paymentService->failed($message);
    }

    public function success($txrID)
    {
        return $this->paymentService->success($txrID);
    }

}
