<?php

namespace Startwind\Top\Page;

use Startwind\Top\Application\MainFrame;
use Startwind\Top\Client\Server;
use Symfony\Component\Console\Output\OutputInterface;

class NotificationPage extends Page
{
    public function render(OutputInterface $output, MainFrame $mainFrame, Server $server): void
    {
        $data = $server->getNotifications();

        $mainFrame->render();

        if (array_key_exists('data', $data)) {
            $notifications = $data['data'];
        } else {
            $notifications = [];
        }

        $tableData = [];

        foreach ($notifications as $notification) {
            if (!$notification['end']) {
                $end = "";
                $duration = time() - $notification['start'];
            } else {
                $end = date('m-d H:i', $notification['end']);
                $duration = $notification['end'] - $notification['start'];
            }

            $tableData[] = [
                str_pad(date('m-d H:i', $notification['start']), 12, ' '),
                str_pad($end, 11, ' '),
                str_pad((int)($duration / 60), 10, ' '),
                str_pad($notification['metric'], 8, ' '),
                $notification['summary']
            ];
        }

        $this->renderTable($output, ['Start time', 'End time', ' Duration', 'Metric', 'Message'], $tableData);

        $mainFrame->setInfo('Incident history');
    }
}
