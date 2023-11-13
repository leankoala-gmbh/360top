<?php

namespace Startwind\Top\Page;

use Startwind\Top\Application\Graph;
use Startwind\Top\Application\MainFrame;
use Startwind\Top\Client\Server;
use Startwind\Top\Page\Exception\NoDataReturnedException;
use Symfony\Component\Console\Output\OutputInterface;

class LoadAveragePage extends Page
{
    const INTERVAL_ONE_MINUTE = '1';
    const INTERVAL_FIVE_MINUTES = '5';
    const INTERVAL_FIFTEEN_MINUTE = '15';

    public function render(OutputInterface $output, MainFrame $mainFrame, Server $server, int $intervalInMinutes): void
    {
        $data = $this->getData($server, Server::METRIC_LOAD_AVERAGE, $intervalInMinutes);

        if (!$data) {
            throw new NoDataReturnedException('Unable to fetch data for load average metric.');
        }

        $loadDate = $data['data']['average'];

        $graphs[] = new Graph($loadDate[self::INTERVAL_ONE_MINUTE], "Load average (1 minute)", $intervalInMinutes);
        $graphs[] = new Graph($loadDate[self::INTERVAL_FIVE_MINUTES], "Load average (5 minute)", $intervalInMinutes);
        $graphs[] = new Graph($loadDate[self::INTERVAL_FIFTEEN_MINUTE], "Load average (15 minute)", $intervalInMinutes);

        $this->renderGraphs($output, $mainFrame, $graphs);
    }
}
