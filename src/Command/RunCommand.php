<?php

namespace Startwind\Top\Command;

use GuzzleHttp\Client;
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

class RunCommand extends TopCommand
{
    private int $currentPage = 0;

    private int $currentIntervalInMinutes = 30;

    private array $menu = [];

    private array $intervalMenu = [];

    private Server $server;

    private MainFrame $mainFrame;

    protected function configure()
    {
        $this->setName('top');
        $this->setDescription('Run the interactive monitoring dashboard.');
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

        $mainFrame = new MainFrame($output);


        $this->initMenu();
        $this->initIntervalMenu();

        $mainFrame->setDropDownMenu($this->intervalMenu);
        $mainFrame->setMenu($this->menu);

        $mainFrame->setHeadline(TOP_NAME_LONG);
        $mainFrame->setFooter($this->server->get360Link());

        $this->mainFrame = $mainFrame;

        (new MemoryPage())->render($output, $mainFrame, $this->server, $this->getBestInterval());

        $this->doRun($output);

        return Command::SUCCESS;
    }

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

        $knownMetrics = ['cpu', 'mem', 'df', 'pn', 'net', 'uptime', 'process', 'la', 'io', 'load_per_core', 'swp'];

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

    private function initIntervalMenu(): void
    {
        $this->intervalMenu = [
            ['caption' => 'last 30 minutes', 'value' => 30],
            ['caption' => 'last hour', 'value' => 60],
            ['caption' => 'last day', 'value' => 60 * 24],
            ['caption' => 'last month', 'value' => 60 * 24 * 30],
        ];
    }

    private function getBestInterval(): int
    {
        return $this->currentIntervalInMinutes;
    }

    private function doRun(OutputInterface $output): void
    {
        $mainFrame = $this->mainFrame;

        system('stty cbreak');
        system('stty -echo');

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
                $mainFrame->incCurrentPage();
                $commandCharacter = $lastChar;
            } else if ($arrowKey === "[D") {
                $mainFrame->decCurrentPage();
                $commandCharacter = $lastChar;
            } else if ($arrowKey === "[B") {
                if ($mainFrame->isDropDownOpen()) {
                    $mainFrame->incDropDownIndex();
                } else {
                    $mainFrame->openDropDown();
                }
            } else if ($arrowKey === "[A") {
                if ($mainFrame->isDropDownOpen()) {
                    $mainFrame->decDropDownIndex();
                } else {
                    $mainFrame->openDropDown();
                }
            } else if (ord($arrowKey) === 10) {
                $mainFrame->closeDropDown();
                $index = $mainFrame->getDropDownIndex();
                $this->currentIntervalInMinutes = $this->intervalMenu[$index]['value'];
                $commandCharacter = $lastChar;
            }

            if (!$mainFrame->isDropDownOpen()) {
                foreach ($this->menu as $menu) {
                    if (strtolower($menu['shortcut']) === strtolower($commandCharacter)) {
                        if ($lastChar != $commandCharacter) {
                            $this->currentPage = 0;
                        }
                        $lastChar = $commandCharacter;
                        if (array_key_exists('metric', $menu)) {
                            $menu['page']->render($output, $mainFrame, $this->server, $menu['metric'], $this->getBestInterval());
                        } else {
                            $menu['page']->render($output, $mainFrame, $this->server, $this->getBestInterval());
                        }
                    }
                }
            }
        }
    }
}
