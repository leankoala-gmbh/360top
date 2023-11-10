<?php

namespace Startwind\Top\Client;

class Client
{
    private \GuzzleHttp\Client $client;
    private string $apiToken;

    public function __construct(string $apiToken, \GuzzleHttp\Client $client)
    {
        $this->client = $client;
        $this->apiToken = $apiToken;
    }

    public function getServer(string $serverId): Server
    {
        return new Server($this, $serverId);
    }

    public function fetch(string $url, array $payload = [], string $method = "GET"): array
    {
        foreach ($payload as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }

        if (strtoupper($method) === "GET") {
            $first = true;
            foreach ($payload as $key => $value) {
                if ($first) {
                    $url .= '?' . $key . '=' . $value;
                    $first = false;
                } else {
                    $url .= '&' . $key . '=' . $value;
                }
            }
            //  var_dump($url);

            $response = $this->client->get($url, ['headers' => ['Authorization' => "Bearer " . $this->apiToken]]);
        } else {
            throw  new \RuntimeException('Method not implemented yet.');
        }

        return json_decode((string)$response->getBody(), true);
    }
}
