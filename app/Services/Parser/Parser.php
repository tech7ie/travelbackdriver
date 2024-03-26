<?php

namespace App\Services\Parser;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
abstract class Parser
{
    protected function doRequest(string $url, array $headers = [], $options = []): array
    {
        $result = [
            'status' => false
        ];

        $client = new Client(['verify' => false]);
        $headers = array_merge($headers, ['Connection' => 'close']);

        try {
            $response = $client->request($options['method'], $url, [
                'headers' => $headers,
                'form_params' => $options['body'],
            ]);

            $result['status'] = true;
            $result['response'] = $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            $result['response'] = $e->getMessage();
        }

        return $result;
    }

    abstract protected function getData();
}
