<?php

namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

trait ConsumesExternalService
{
    /**
     * Send a request to any service
     * @param  string $method
     * @param  string $requestUrl
     * @param  array $formParams
     * @param  array $headers
     * @return string
     */
    public function performRequest($method, $requestUrl, $formParams = [], $headers = [])
    {
        // Get base URI from configuration
        $baseUri = $this->baseUri;

        // Create client with base URI
        $client = new Client([
            'base_uri' => $baseUri,
        ]);

        // Set default headers
        $defaultHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        // Merge with provided headers
        $headers = array_merge($defaultHeaders, $headers);

        try {
            // Perform request
            $response = $client->request($method, $requestUrl, [
                'headers' => $headers,
                'json' => $formParams
            ]);

            return $response->getBody()->getContents();

        } catch (ClientException $e) {
            // Handle client errors (4xx, 5xx)
            throw $e;
        }
    }
}