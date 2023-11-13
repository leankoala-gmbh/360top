<?php

namespace Startwind\Top\Page;

use Startwind\Top\Application\Graph;
use Startwind\Top\Application\MainFrame;
use Startwind\Top\Client\Server;
use Symfony\Component\Console\Output\OutputInterface;

class CustomMetricPage extends Page
{
    public function render(OutputInterface $output, MainFrame $mainFrame, Server $server, string $metric, int $intervalInMinutes): void
    {
        $data = $this->getData($server, $metric, $intervalInMinutes);

        $metrics = $data['data']['average'];

        if (is_null($metrics)) {
            $metrics = [];
        }

        $graphs = [];

        foreach ($metrics as $key => $timeSeries) {
            $graphs[] = new Graph($timeSeries, $key, $intervalInMinutes);
        }

        $this->renderGraphs($output, $mainFrame, $graphs);
    }
}
