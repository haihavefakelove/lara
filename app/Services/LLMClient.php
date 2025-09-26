<?php

namespace App\Services;

use GuzzleHttp\Client;

class LLMClient
{
    private Client $http;
    private string $model;

    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => rtrim(config('services.llm.base'), '/') . '/',
            'timeout'  => 30,
        ]);
        $this->model = config('services.llm.model', 'qwen-3-235b-a22b-instruct-2507');
    }

    public function chat(array $messages, array $options = []): array
{
    $payload = array_merge([
        'model' => $this->model,
        'messages' => $messages,
        'response_format' => ['type' => 'json_object'],
        'max_tokens' => 800,
    ], $options);

    try {
        $res = $this->http->post('chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . config('services.llm.key'),
                'Content-Type'  => 'application/json',
            ],
            'json' => $payload,
        ]);
        return json_decode($res->getBody()->getContents(), true);
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        $code = $e->getResponse()->getStatusCode();
        $body = (string) $e->getResponse()->getBody();

        if ($code === 422 && str_contains($body, 'response_format')) {
            unset($payload['response_format']);
            $res = $this->http->post('chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.llm.key'),
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
            ]);
            return json_decode($res->getBody()->getContents(), true);
        }
        throw $e;
    }
}

}
