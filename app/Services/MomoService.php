<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MomoService
{
    protected string $endpoint;
    protected string $partnerCode;
    protected string $accessKey;
    protected string $secretKey;
    protected string $redirectUrl;
    protected string $ipnUrl;

    public function __construct()
    {
        $this->endpoint    = config('services.momo.endpoint', 'https://test-payment.momo.vn/v2/gateway/api/create');
        $this->partnerCode = config('services.momo.partner_code');
        $this->accessKey   = config('services.momo.access_key');
        $this->secretKey   = config('services.momo.secret_key');
        $this->redirectUrl = config('services.momo.redirect_url');
        $this->ipnUrl      = config('services.momo.ipn_url');
    }

    public function createPayment(array $options)
    {
        // $options cần: amount, orderId, requestId, orderInfo
        $amount    = $options['amount'];
        $orderId   = $options['orderId'];
        $requestId = $options['requestId'];
        $orderInfo = $options['orderInfo'] ?? 'Thanh toan don hang';

        $rawHash = "accessKey=".$this->accessKey
            ."&amount=".$amount
            ."&extraData="
            ."&ipnUrl=".$this->ipnUrl
            ."&orderId=".$orderId
            ."&orderInfo=".$orderInfo
            ."&partnerCode=".$this->partnerCode
            ."&redirectUrl=".$this->redirectUrl
            ."&requestId=".$requestId
            ."&requestType=captureWallet";

        $signature = hash_hmac('sha256', $rawHash, $this->secretKey);

        $payload = [
            'partnerCode' => $this->partnerCode,
            'partnerName' => "MyShop",
            'storeId'     => "MyShop",
            'requestId'   => $requestId,
            'amount'      => (int)$amount,
            'orderId'     => $orderId,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $this->redirectUrl,
            'ipnUrl'      => $this->ipnUrl,
            'lang'        => 'vi',
            'extraData'   => "",
            'requestType' => 'captureWallet',
            'signature'   => $signature,
        ];

        $response = Http::post($this->endpoint, $payload);
        return $response->json();
    }

    public function verifySignature(array $params): bool
    {
        // MoMo trả về các key để verify, bạn làm theo docs
        $rawHash = "accessKey=".$this->accessKey
            ."&amount=".$params['amount']
            ."&extraData=".$params['extraData']
            ."&message=".$params['message']
            ."&orderId=".$params['orderId']
            ."&orderInfo=".$params['orderInfo']
            ."&orderType=".$params['orderType']
            ."&partnerCode=".$params['partnerCode']
            ."&payType=".$params['payType']
            ."&requestId=".$params['requestId']
            ."&responseTime=".$params['responseTime']
            ."&resultCode=".$params['resultCode']
            ."&transId=".$params['transId'];

        $signature = hash_hmac('sha256', $rawHash, $this->secretKey);

        return isset($params['signature']) && $signature === $params['signature'];
    }
}
