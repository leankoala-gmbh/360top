<?php

namespace Startwind\Top\Client;

class Server
{
    const METRIC_CPU = 'cpu';
    const METRIC_MEMORY = 'mem';
    const METRIC_DISK_SPACE = 'df';
    const METRIC_DISK = 'disk';

    const ENDPOINT_METRIC = 'https://api.monitoring360.io/v1/server/{serverId}/metrics';
    const ENDPOINT_NOTIFICATIONS = 'https://api.monitoring360.io/v1/server/{serverId}/notifications';
    const ENDPOINT_METRIC_TYPE = 'https://api.monitoring360.io/v1/server/{serverId}';

    const LINK_360 = 'https://monitoring.platform360.io/server/{serverId}/overview';

    private int $defaultNotificationInterval = 24 * 60 * 60;

    private string $serverId;

    private Client $client;

    public function __construct(Client $client, string $serverId)
    {
        $this->serverId = $serverId;
        $this->client = $client;
    }

    public function getMetricTypes(): array
    {
        $result = $this->client->fetch(self::ENDPOINT_METRIC_TYPE, ['serverId' => $this->serverId]);
        return $result['metrics'];
    }

    public function getMetric(string $metric, int $durationInMinutes): array
    {
        $payload = ['serverId' => $this->serverId, 'durationInMinutes' => $durationInMinutes, 'metric' => $metric];
        return $this->client->fetch(self::ENDPOINT_METRIC, $payload);
    }

    public function getNotifications(?int $start = null): array
    {
        if (!$start) {
            $start = time() - $this->defaultNotificationInterval;
        }

        return $this->client->fetch(self::ENDPOINT_NOTIFICATIONS, ['start' => $start, 'serverId' => $this->serverId]);
    }

    public function getMetaData(): array
    {
        $result = $this->client->fetch(self::ENDPOINT_METRIC_TYPE, ['serverId' => $this->serverId]);
        var_dump($result);
        die;
    }

    public function get360Link(): string
    {
        return str_replace('{serverId}', $this->serverId, self::LINK_360);
    }
}
