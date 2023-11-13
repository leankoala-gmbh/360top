<?php

namespace Startwind\Top\Page;

use Startwind\Top\Application\Graph;
use Startwind\Top\Application\MainFrame;
use Startwind\Top\Client\Server;
use Symfony\Component\Console\Output\OutputInterface;

class DiskSpacePage extends Page
{
    const FIELD_PERCENTAGE_USED = 'percentage_used';
    const FIELD_BYTES_FREE = 'bytes_free';
    const FIELD_BYTES_USED = 'bytes_used';

    public function render(OutputInterface $output, MainFrame $mainFrame, Server $server, int $intervalInMinutes): void
    {
        $data = $this->getData($server, Server::METRIC_DISK, $intervalInMinutes);

        $mounts = $data['data']['disk'];

        $graphs = [];

        foreach ($mounts as $mountName => $timeSeries) {
            $free = $this->byteToHumanReadable((int)end($timeSeries[self::FIELD_BYTES_FREE]));
            $used = $this->byteToHumanReadable((int)end($timeSeries[self::FIELD_BYTES_USED]));

            $graphs[] = new Graph($timeSeries[self::FIELD_PERCENTAGE_USED], "Mount point \"" . $mountName . '" (used: ' . $used . ', free: ' . $free . ')', $intervalInMinutes, self::UNIT_PERCENT);
        }

        $this->renderGraphs($output, $mainFrame, $graphs);
    }

    protected function byteToHumanReadable(int $bytes): string
    {
        $bytesInMb = $bytes / 1024 / 1024;

        if ($bytesInMb > 1000) {
            $result = round(($bytesInMb / 1000)) . ' GB';
        } else {
            $result = round($bytesInMb) . ' MB';
        }

        return $result;
    }
}
