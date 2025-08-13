<?php

namespace ProgrammerHasan\Bkash\App\Service;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ProgrammerHasan\Bkash\App\Exceptions\BkashException;
use ProgrammerHasan\Bkash\App\Util\BkashCredential;

class PaymentService extends BkashService
{
    private BkashCredential $credential;
    private BkashAuthService $bkashAuthService;

    public function __construct()
    {
        parent::__construct('tokenized');
        $this->credential = new BkashCredential(config('bkash'));
        $this->bkashAuthService = new BkashAuthService();
    }

    private function storeLog($apiName, $url, $headers, $body, $response): void
    {
       if($this->credential->logEnabled) {
           $log = [
               'url' => $url,
               'headers' => $headers,
               'body' => $body,
               'response' => $response,
           ];
           $key = 'bkash:log:' . $apiName;

           Log::info($key . "=>" . json_encode($log));
       }
    }

    public function grantToken()
    {
        try {
            $url = $this->credential->getURL('/checkout/token/grant');
            $headers = $this->credential->getAuthHeaders();

            $body = [
                'app_key' => $this->credential->appKey,
                'app_secret' => $this->credential->appSecret,
            ];

            $res = $this->httpClient()->post($url, [
                'json' => $body,
                'headers' => $headers,
            ]);

            $response = json_decode($res->getBody()->getContents());

            if ($response->statusCode != '0000') {
                throw new BkashException(json_encode($response));
            }

            $this->storeLog('grant_token', $url, $headers, $body, $response);

            return $response;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function refreshToken($refreshToken)
    {
        try {
            $res = $this->httpClient()->post($this->credential->getURL('/checkout/token/refresh'), [
                'json' => [
                    'App_key' => $this->credential->appKey,
                    'App_secret' => $this->credential->appSecret,
                    'refresh_token' => $refreshToken,
                ],
                'headers' => $this->credential->getAuthHeaders(),
            ]);

            return json_decode($res->getBody()->getContents());
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function createPayment($request)
    {
        $defaults = [
            'payerReference' => '11',
            'intent' => 'sale',
            'merchantInvoiceNumber' => str::random(20)
        ];

        $request = array_merge($defaults, $request);

        if ($request['intent'] !== 'sale' && $request['intent'] !== 'authorization') {
            throw new BkashException('Invalid value for "intent". Allowed values are "sale" or "authorization".');
        }

        $amount = $request['amount'];
        if (!$amount) {
            throw new BkashException('Invalid amount.');
        }

        try {
            $url = $this->credential->getURL('/checkout/create');
            $headers = $this->credential->getAccessHeaders($this->bkashAuthService->getToken());

            $body = [
                "mode" => "0011",
                'payerReference' => $request['payerReference'],
                'currency' => $request['currency'] ?? 'BDT',
                'callbackURL' => $request['callbackURL'] ?? $this->credential->getCallBackURL(),
                'amount' => strval($amount * 1.0),
                'intent' => $request['intent'],
                'merchantInvoiceNumber' => $request['merchantInvoiceNumber']
            ];

            $res = $this->httpClient()->post($url, [
                'json' => $body,
                'headers' => $headers,
            ]);

            $response = json_decode($res->getBody()->getContents());

            if ($response->statusCode != '0000') {
                throw new BkashException(json_encode($response));
            }

            $this->storeLog('create_payment', $url, $headers, $body, $response);

            return $response;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function executePayment($paymentID)
    {
        try {
            $url = $this->credential->getURL('/checkout/execute/');
            $headers = $this->credential->getAccessHeaders($this->bkashAuthService->getToken());
            $body = [
                'paymentID' => $paymentID
            ];

            $res = $this->httpClient()->post($url, [
                'json' => $body,
                'headers' => $headers,
            ]);

            $response = json_decode($res->getBody()->getContents());

            $this->storeLog('Execute Payment', $url, $headers, $body, $response);

            return $response;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function queryPayment($paymentID)
    {
        try {
            $url = $this->credential->getURL('/checkout/payment/status');
            $headers = $this->credential->getAccessHeaders($this->bkashAuthService->getToken());
            $body = ['paymentID' => $paymentID];
            $res = $this->httpClient()->post($url, [
                'json' => $body,
                'headers' => $headers,
            ]);

            $response = json_decode($res->getBody()->getContents());

            $this->storeLog('query_payment', $url, $headers, $body, $response);

            return $response;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function searchTransaction($trxID)
    {
        try {
            $url = $this->credential->getURL('/checkout/general/searchTransaction');
            $headers = $this->credential->getAccessHeaders($this->bkashAuthService->getToken());
            $body = ['trxID' => $trxID];
            $res = $this->httpClient()->post($url, [
                'json' => $body,
                'headers' => $headers,
            ]);

            $response = json_decode($res->getBody()->getContents());
            $this->storeLog('search_transaction', $url, $headers, $body, $response);

            return $response;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function refundTransaction($paymentID, $trxID, $amount)
    {
        try {
            $res = $this->httpClient()->post($this->credential->getURL('/checkout/payment/refund'), [
                'json' => [
                    'paymentID' => $paymentID,
                    'trxID' => $trxID,
                    'amount' => strval($amount),
                    'sku' => 'no SKU',
                    'reason' => 'Product quality issue'
                ],
                'headers' => $this->credential->getAccessHeaders($this->bkashAuthService->getToken()),
            ]);

            return json_decode($res->getBody()->getContents());
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function capturePayment($paymentID)
    {
        try {
            $url = $this->credential->getURL('/checkout/payment/confirm/capture');
            $headers = $this->credential->getAccessHeaders($this->bkashAuthService->getToken());
            $body = [
                'paymentID' => $paymentID
            ];
            $res = $this->httpClient()->post($url, [
                'json' => $body,
                'headers' => $headers,
            ]);

            $response = json_decode($res->getBody()->getContents());

            return $response;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function voidPayment($paymentID)
    {
        try {
            $url = $this->credential->getURL('/checkout/payment/confirm/void');
            $headers = $this->credential->getAccessHeaders($this->bkashAuthService->getToken());
            $body = [
                'paymentID' => $paymentID
            ];

            $res = $this->httpClient()->post($url, [
                'json' => $body,
                'headers' => $headers,
            ]);

            $response = json_decode($res->getBody()->getContents());

            return $response;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function verifyPayment($paymentID)
    {
        $response = $this->executePayment($paymentID);

        if (is_object($response) && empty((array)$response)) {
            $newResponse = json_decode('{"statusCode":"1111", "statusMessage":"Unknown error"}');
            return $newResponse;
        }

        if (isset($response->statusCode)) {
            return $response;
        }

        if (isset($response->message)) {
            // If the executePayment took too long, call the query API
            sleep(1);
            return $this->queryPayment($paymentID);
        }
    }

    public function failed($message)
    {
        session()->put('payment_Fail_message', $message);
        return redirect()->route('bkash.payment.fail');
    }

    public function success($txrID)
    {
        session()->put('payment_confirm_message', $txrID);
        return redirect()->route('bkash.payment.success');
    }
}
