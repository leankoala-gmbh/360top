<?php

namespace Startwind\Top\Page;

use Startwind\Top\Application\Graph;
use Startwind\Top\Application\MainFrame;
use Startwind\Top\Client\Server;
use Startwind\Top\Page\Exception\NoDataReturnedException;
use Symfony\Component\Console\Output\OutputInterface;

class MemoryPage extends Page
{
    public function render(OutputInterface $output, MainFrame $mainFrame, Server $server, int $intervalInMinutes): void
    {
        $data = $this->getData($server, Server::METRIC_MEMORY, $intervalInMinutes);

        if (!$data) {
            throw new NoDataReturnedException('Unable to fetch data for memory metric.');
        }

        $memData = $data['data']['average'];

        $graphs[] = new Graph($memData['active_percent'], "Memory (active percent)", $intervalInMinutes, self::UNIT_PERCENT);
        $graphs[] = new Graph($memData['p'], "Memory (free)", $intervalInMinutes, self::UNIT_PERCENT);

        $this->renderGraphs($output, $mainFrame, $graphs);
    }
}
