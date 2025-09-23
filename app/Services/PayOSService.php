<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayOSService
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $apiKey;
    protected string $checksumKey;
    protected int    $timeout;
    protected int    $connectTimeout;
    protected bool   $verifySsl;

    public function __construct()
    {
        // Luôn dùng miền .vn (prod)
        $this->baseUrl        = (string) config('payos.base_url', 'https://api-merchant.payos.vn');
        $this->clientId       = trim((string) config('payos.client_id', ''));
        $this->apiKey         = trim((string) config('payos.api_key', ''));
        $this->checksumKey    = trim((string) config('payos.checksum_key', ''));
        $this->timeout        = (int)   config('payos.timeout', 60);
        $this->connectTimeout = (int)   config('payos.connect_timeout', 30);
        $this->verifySsl      = (bool)  config('payos.verify_ssl', true);

        $host = parse_url($this->baseUrl, PHP_URL_HOST);
        Log::info('[PayOS] boot', [
            'base'   => $this->baseUrl,
            'cid_3'  => substr($this->clientId, 0, 3),
            'apk_3'  => substr($this->apiKey, 0, 3),
            'ck_len' => strlen($this->checksumKey),
            'verify' => $this->verifySsl,
            'host'   => $host,
            'ip'     => $host ? gethostbyname($host) : 'n/a',
        ]);
    }

    /**
     * Tạo chuỗi ký: key=value nối bằng '&' theo thứ tự ABC:
     * amount & cancelUrl & description & orderCode & returnUrl
     * LƯU Ý: KHÔNG url-encode URL, dùng giá trị thô (theo log "00/success" của bạn).
     */
    protected function buildSignString(array $b): string
    {
        // Bắt buộc có đủ 5 khóa
        $keys = ['amount', 'cancelUrl', 'description', 'orderCode', 'returnUrl'];
        foreach ($keys as $k) {
            if (!array_key_exists($k, $b) || $b[$k] === null || $b[$k] === '') {
                throw new \InvalidArgumentException("Missing field for signature: {$k}");
            }
        }

        // KHÔNG dùng http_build_query (vì nó encode URL) — PayOS ở kênh của bạn cần raw
        return 'amount='.(int)$b['amount']
            .'&cancelUrl='.$b['cancelUrl']
            .'&description='.$b['description']
            .'&orderCode='.(int)$b['orderCode']
            .'&returnUrl='.$b['returnUrl'];
    }

    protected function makeSignature(array $body): string
    {
        $raw = $this->buildSignString($body);
        $sig = hash_hmac('sha256', $raw, $this->checksumKey);
        Log::debug('[PayOS] signature raw', ['raw' => $raw, 'sig' => $sig]);
        return $sig;
    }

    /**
     * Tạo link thanh toán PayOS
     * Body JSON: { amount, orderCode, description, returnUrl, cancelUrl, signature }
     * Header: x-client-id, x-api-key
     */
    public function createPaymentLink(array $payload): array
    {
        if ($this->clientId === '' || $this->apiKey === '' || $this->checksumKey === '') {
            Log::error('[PayOS] credentials missing. Check .env & config/payos.php');
            throw new \RuntimeException('PayOS credentials missing');
        }

        $url = rtrim($this->baseUrl, '/') . '/v2/payment-requests';

        $body = [
            'orderCode'   => (int) ($payload['orderCode'] ?? 0),
            'amount'      => (int) ($payload['amount'] ?? 0),
            'description' => (string) ($payload['description'] ?? ''),
            'returnUrl'   => (string) ($payload['returnUrl'] ?? ''),
            'cancelUrl'   => (string) ($payload['cancelUrl'] ?? ''),
        ];

        // Validate tối thiểu
        if ($body['orderCode'] <= 0 || $body['amount'] <= 0 || $body['description'] === '' || $body['returnUrl'] === '' || $body['cancelUrl'] === '') {
            Log::error('[PayOS] invalid payload', $body);
            throw new \InvalidArgumentException('Invalid PayOS payload');
        }
        foreach (['returnUrl','cancelUrl'] as $k) {
            if (!str_starts_with($body[$k], 'https://')) {
                throw new \InvalidArgumentException("$k must be HTTPS public");
            }
        }

        // Ký theo công thức ĐÚNG
        $body['signature'] = $this->makeSignature($body);

        $resp = Http::withHeaders([
                    'x-client-id'  => $this->clientId,
                    'x-api-key'    => $this->apiKey,
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->timeout($this->timeout)
                ->connectTimeout($this->connectTimeout)
                ->withOptions(['verify' => $this->verifySsl])
                ->post($url, $body);

        $json = ['http' => $resp->status(), 'json' => $resp->json()];
        Log::info('[PayOS] createPaymentLink response', $json);

        if (!$resp->successful()) {
            throw new \RuntimeException('PayOS API error');
        }

        $data = $resp->json();
        if (!is_array($data) || !isset($data['code']) || $data['code'] !== '00' || !isset($data['data'])) {
            Log::error('[PayOS] unexpected payload', ['body' => $data]);
            throw new \RuntimeException('PayOS API error: unexpected payload');
        }

        Log::info('[PayOS] createPaymentLink OK', ['has_checkout' => !empty($data['data']['checkoutUrl'])]);
        return $data; // trả nguyên {code, desc, data: {...}}
    }
    protected function canonicalize(array $arr): string
    {
        ksort($arr);
        $parts = [];
        foreach ($arr as $k => $v) {
            if (is_null($v) || $v === 'undefined') $v = '';
            if (is_array($v)) {
                // value là array/object -> json ko escape unicode/slashes
                $v = json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            $parts[] = $k . '=' . $v;
        }
        return implode('&', $parts);
    }
    public function getPaymentRequest(int $orderCode): array
    {
        $url = rtrim($this->baseUrl, '/') . '/v2/payment-requests/' . $orderCode;

        $resp = Http::withHeaders([
                    'x-client-id'  => $this->clientId,
                    'x-api-key'    => $this->apiKey,
                    'Accept'       => 'application/json',
                ])
                ->timeout($this->timeout)
                ->connectTimeout($this->connectTimeout)
                ->withOptions(['verify' => $this->verifySsl])
                ->get($url);

        $json = $resp->json();
        Log::info('[PayOS] getPaymentRequest', ['http' => $resp->status(), 'json' => $json]);

        if (!$resp->successful() || !is_array($json)) {
            throw new \RuntimeException('PayOS getPaymentRequest failed');
        }
        return $json; // {code, desc, data: { status: PENDING/PAID/CANCELLED, amount, ...}}
    }

    /**
     * Verify webhook:
     *  - Header x-signature ký HMAC_SHA256(rawBody, CHECKSUM_KEY)
     *  - Dashboard "test webhook" đôi khi không gửi header -> cho pass để lưu URL
     */
    public function verifyWebhook(string $rawBody, ?string $signatureHeader): bool
    {
        $json = json_decode($rawBody, true);
        if (!is_array($json)) {
            Log::warning('[PayOS] Webhook body not JSON');
            return false;
        }
        // Theo tài liệu: ưu tiên body.signature (chuẩn xác), header có thể không gửi.
        $sig = (string)($json['signature'] ?? '');
        $data = (array)($json['data'] ?? []);
        if ($sig === '' || empty($data)) {
            Log::warning('[PayOS] Webhook missing signature or data', compact('sig','data'));
            return false;
        }
        $plain = $this->canonicalize($data);
        $expected = hash_hmac('sha256', $plain, $this->checksumKey);

        $ok = hash_equals($expected, $sig);

        if (!$ok) {
            Log::warning('[PayOS] Webhook signature mismatch', [
                'expected' => $expected,
                'got'      => $sig,
                'plain'    => $plain,
            ]);
        }
        return $ok;
    }

}
