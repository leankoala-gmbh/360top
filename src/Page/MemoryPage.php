<?php

namespace Startwind\Top\Page;

use Startwind\Top\Application\MainFrame;
use Startwind\Top\Client\Server;
use Symfony\Component\Console\Output\OutputInterface;

class MemoryPage extends Page
{
    public function render(OutputInterface $output, MainFrame $mainFrame, Server $server, int $currentPage, int $intervalInMinutes): void
    {
        var_dump($intervalInMinutes);

        if (!$intervalInMinutes) {
            die;
        }

        $mainFrame->render();
        $data = $this->getData($server, Server::METRIC_MEMORY, $intervalInMinutes);

        $memData = $data['data']['average'];

        $this->renderGraph($output, "Memory (active percent)", 3, 15, $memData['active_percent'], self::UNIT_PERCENT);
        $this->renderGraph($output, "Memory (free)", 3, 30, $memData['p'], self::UNIT_PERCENT);

        $mainFrame->setInfo('Memory history');
    }
}
