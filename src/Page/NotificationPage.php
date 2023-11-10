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
            $duration = $notification['end'] - $notification['start'];

            $tableData[] = [
                str_pad(date('m-d H:i', $notification['start']), 12, ' '),
                str_pad(date('m-d H:i', $notification['end']), 9, ' '),
                str_pad((int)($duration / 60), 10, ' '),
                $notification['summary']
            ];
        }

        $this->renderTable($output, ['Start time', 'End time', ' Duration', 'Message'], $tableData);

        $mainFrame->setInfo('Incident history');
    }
}
