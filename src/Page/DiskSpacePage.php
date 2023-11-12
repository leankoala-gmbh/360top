<?php

namespace Startwind\Top\Page;

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

        $pageOption = $this->getPageOptions($mainFrame, $mounts);

        $count = 1;
        $position = 1;

        foreach ($mounts as $mountName => $timeSeries) {
            $free = $this->byteToHumanReadable((int)end($timeSeries[self::FIELD_BYTES_FREE]));
            $used = $this->byteToHumanReadable((int)end($timeSeries[self::FIELD_BYTES_USED]));

            if ($count <= $pageOption['end'] && $count > $pageOption['start']) {
                $this->renderGraph($output, "Mount point \"" . $mountName . '" (used: ' . $used . ', free: ' . $free . ')', 3, (self::METRIC_HEIGHT + 5) * $position, $timeSeries[self::FIELD_PERCENTAGE_USED], self::UNIT_PERCENT, 30, $intervalInMinutes);
                $position++;
            }

            $count++;
        }

        $mainFrame->setInfo('Disk space history');
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
