<?php

namespace Startwind\Top\Client;

class Client
{
    const CACHE_TIME_IN_SECONDS = 60;

    private \GuzzleHttp\Client $client;
    private string $apiToken;


    private array $cache = [];

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
        if (array_key_exists('durationInMinutes', $payload)) {
            $payload['start'] = time() - $payload['durationInMinutes'] * 60;
        }

        $baseUrl = $url;
        $basePayload = $payload;

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

            unset($basePayload['start']);
            unset($basePayload['end']);

            $hash = md5($baseUrl . json_encode($basePayload));

            if (array_key_exists($hash, $this->cache)) {
                if ($this->cache[$hash]['time'] + self::CACHE_TIME_IN_SECONDS > time()) {
                    return $this->cache[$hash]['data'];
                }
            }

            $response = $this->client->get($url, ['headers' => ['Authorization' => "Bearer " . $this->apiToken]]);
        } else {
            throw  new \RuntimeException('Method not implemented yet.');
        }

        $data = json_decode((string)$response->getBody(), true);

        $this->cache[$hash]['time'] = time();
        $this->cache[$hash]['data'] = $data;

        return $data;
    }
}
