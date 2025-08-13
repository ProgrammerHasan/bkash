<?php

namespace ProgrammerHasan\Bkash\App\Util;

class BkashCredential
{
    public $callbackUrl;
    public $baseUrl;
    public $sandbox;
    public $appKey;
    public $appSecret;
    public $username;
    public $password;

    public function __construct($arr)
    {
        $baseKey = $arr['bkash_sandbox'] ? 'bkash_base_url_sandbox' : 'bkash_base_url_production';
        $this->baseUrl = $arr[$baseKey];
        $this->appKey = $arr['bkash_app_key'];
        $this->appSecret = $arr['bkash_app_secret'];
        $this->username = $arr['bkash_username'];
        $this->password = $arr['bkash_password'];
        $this->callbackUrl = $arr['bkash_callback_url'];
        $this->sandbox = $arr['bkash_sandbox'];
    }

    public function getURL($path): string
    {
        return $this->baseUrl . $path;
    }

    public function getCallBackURL()
    {
        return $this->callbackUrl;
    }

    public function getAuthHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'username' => $this->username,
            'password' => $this->password,
        ];
    }

    public function getAccessHeaders($accessToken): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $accessToken,
            'X-App-Key' => $this->appKey,
        ];
    }
}
