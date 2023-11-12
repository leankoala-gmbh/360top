<?php

namespace Startwind\Top\Page;

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

        $pageOption = $this->getPageOptions($mainFrame, $metrics);

        $count = 1;
        $position = 1;

        foreach ($metrics as $name => $values) {
            if ($count <= $pageOption['end'] && $count > $pageOption['start']) {
                $this->renderGraph($output, $name, 3, $position * (self::METRIC_HEIGHT + 5), $values, '', 30, $intervalInMinutes);
                $position++;
            }
            $count++;
        }

        $mainFrame->setInfo($metric . ' history');
    }
}
