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
                $status = "open";
            } else {
                $end = date('m-d H:i', $notification['end']);
                $duration = $notification['end'] - $notification['start'];
                $status = 'closed';
            }

            $tableData[] = [
                "data" => [
                    str_pad(date('m-d H:i', $notification['start']), 12, ' '),
                    str_pad($end, 11, ' '),
                    str_pad((string)((int)($duration / 60)), 10, ' '),
                    str_pad($notification['metric'], 8, ' '),
                    $notification['summary']
                ],
                "status" => $status
            ];
        }

        $this->renderTable($output, ['Start time', 'End time', ' Duration', 'Metric', 'Message'], $tableData);

        $mainFrame->setInfo('Incident history');
    }
}
