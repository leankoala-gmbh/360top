<?php

namespace Startwind\Top\Page;

use Startwind\Top\Application\MainFrame;
use Startwind\Top\Client\Server;
use Startwind\Top\Page\Exception\NoDataReturnedException;
use Symfony\Component\Console\Output\OutputInterface;

class MemoryPage extends Page
{
    public function render(OutputInterface $output, MainFrame $mainFrame, Server $server, int $intervalInMinutes): void
    {
        $mainFrame->render();

        $data = $this->getData($server, Server::METRIC_MEMORY, $intervalInMinutes);

        if (!$data) {
            throw new NoDataReturnedException('Unable to fetch data for Memory metric.');
        }

        $memData = $data['data']['average'];

        $this->renderGraph($output, "Memory (active percent)", 3, 15, $memData['active_percent'], self::UNIT_PERCENT, 30, $intervalInMinutes);
        $this->renderGraph($output, "Memory (free)", 3, 30, $memData['p'], self::UNIT_PERCENT, 30, $intervalInMinutes);

        $mainFrame->setInfo('Memory history');
    }
}
