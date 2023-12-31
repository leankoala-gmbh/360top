<?php

namespace Startwind\Top\Page;

use Startwind\Top\Application\Graph;
use Startwind\Top\Application\MainFrame;
use Startwind\Top\Client\Server;
use Symfony\Component\Console\Output\OutputInterface;

class CpuPage extends Page
{
    public function render(OutputInterface $output, MainFrame $mainFrame, Server $server, int $intervalInMinutes): void
    {
        $data = $this->getData($server, Server::METRIC_CPU, $intervalInMinutes);

        $timeSeries = $data['data']['cpu']['average']['usage'];

        $graphs[] = new Graph($timeSeries, "CPU (average)", $intervalInMinutes, Graph::UNIT_PERCENT);

        $this->renderGraphs($output, $mainFrame, $graphs);
    }
}
