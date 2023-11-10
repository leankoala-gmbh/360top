<?php

namespace Startwind\Top\Command;

use GuzzleHttp\Client;
use JetBrains\PhpStorm\NoReturn;
use Startwind\Top\Application\MainFrame;
use Startwind\Top\Client\Server;
use Startwind\Top\Page\CpuPage;
use Startwind\Top\Page\CustomMetricPage;
use Startwind\Top\Page\DiskSpacePage;
use Startwind\Top\Page\MemoryPage;
use Startwind\Top\Page\NotificationPage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'top')]
class RunCommand extends TopCommand
{
    private int $currentPage = 0;

    private int $currentIntervalInMinutes = 30;

    private array $menu = [];

    private Server $server;

    private MainFrame $mainFrame;

    private function initMenu(): void
    {
        $this->menu = [
            [
                'label' => "(M)emory",
                'shortcut' => 'm',
                'page' => new MemoryPage()
            ],
            [
                'label' => "(C)PU",
                'shortcut' => 'c',
                'page' => new CpuPage()
            ],
            [
                'label' => "(D)isk Space",
                'shortcut' => 'd',
                'page' => new DiskSpacePage()
            ], [
                'label' => "(N)otifications",
                'shortcut' => 'n',
                'page' => new NotificationPage()
            ]
        ];

        $metricTypes = $this->server->getMetricTypes();

        $knownMetrics = ['cpu', 'mem', 'df', 'pn', 'net', 'uptime', 'process', 'la', 'io', 'load_per_core'];

        $count = 0;

        foreach ($metricTypes as $metricType) {
            if (!in_array($metricType, $knownMetrics)) {
                $this->menu[] = [
                    'label' => '(' . $count . ') ' . str_replace('_', ' ', ucfirst($metricType)),
                    'metric' => $metricType,
                    'shortcut' => $count,
                    'page' => new CustomMetricPage()
                ];
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!file_exists($this->getConfigFile())) {
            $output->writeln(['', '<error>No configuration file found. Please run "360top init" before.</error>', '']);
            return Command::FAILURE;
        }

        $config = json_decode(file_get_contents($this->getConfigFile()), true);

        $client = new \Startwind\Top\Client\Client($config['apiToken'], new Client());
        $this->server = $client->getServer($config['serverId']);

        $this->initMenu();

        $mainFrame = new MainFrame($output);

        $this->mainFrame = $mainFrame;

        $mainFrame->setMenu($this->menu);

        $mainFrame->setHeadline('360 Monitoring (by WebPros)');
        $mainFrame->setFooter($this->server->get360Link());

        (new MemoryPage())->render($output, $mainFrame, $this->server, $this->currentPage, $this->getBestInterval());

        $this->doRun($output, $mainFrame);

        return Command::SUCCESS;
    }

    private function getBestInterval()
    {
        $width = $this->mainFrame->getWidth();
        return max(($width / 3) - 10, $this->currentIntervalInMinutes);
    }

    #[NoReturn] private function doRun(OutputInterface $output, MainFrame $mainFrame): void
    {
        system('stty cbreak');

        // stream_set_blocking(STDIN, false);

        $lastChar = $this->menu[0]['shortcut'];

        while (true) {
            $commandCharacter = fread(STDIN, 16);

            if (strtolower($commandCharacter) === 'q') {
                die();
            }

            $arrowKey = preg_replace('/[^[:print:]\n]/u', '', mb_convert_encoding($commandCharacter, 'UTF-8', 'UTF-8'));

            $mainFrame->setPage(0, 0);

            if ($arrowKey === "[C") {
                $this->currentPage++;
                $commandCharacter = $lastChar;
            } else if ($arrowKey === "[D") {
                $this->currentPage--;
                if ($this->currentPage < 0) $this->currentPage = 0;
                $commandCharacter = $lastChar;
            }

            foreach ($this->menu as $menu) {
                if (strtolower($menu['shortcut']) === strtolower($commandCharacter)) {
                    if ($lastChar != $commandCharacter) {
                        $this->currentPage = 0;
                    }
                    $lastChar = $commandCharacter;
                    if (array_key_exists('metric', $menu)) {
                        $menu['page']->render($output, $mainFrame, $this->server, $menu['metric'], $this->currentPage, $this->getBestInterval());
                    } else {
                        $menu['page']->render($output, $mainFrame, $this->server, $this->currentPage, $this->getBestInterval());
                    }
                }
            }
        }
    }
}
