<?php

namespace Startwind\Top\Page;

use Startwind\Top\Application\MainFrame;
use Startwind\Top\Client\Server;
use Symfony\Component\Console\Output\OutputInterface;

class DiskSpacePage extends Page
{
    public function render(OutputInterface $output, MainFrame $mainFrame, Server $server, int $currentPage, int $intervalInMinutes): void
    {
        $mainFrame->render();

        $data = $this->getData($server, Server::METRIC_DISK_SPACE, $intervalInMinutes);

        $mounts = $data['data']['average'];

        $pageOption = $this->getPageOptions($mainFrame, $currentPage, $mounts);

        $count = 1;
        $position = 1;

        foreach ($mounts as $mountName => $timeSeries) {
            if ($count <= $pageOption['end'] && $count > $pageOption['start']) {
                $humanReadableTimeSeries = [];
                foreach ($timeSeries as $timeStamp => $value) {
                    $humanReadableTimeSeries[$timeStamp] = (int)($value / (1000 * 1000 * 1000));
                }
                $this->renderGraph($output, "Mount point " . $mountName, 3, (self::METRIC_HEIGHT + 5) * $position, $humanReadableTimeSeries, '', 30,$intervalInMinutes);
                $position++;
            }
            $count++;
        }

        $mainFrame->setInfo('Disk space history');
    }
}
