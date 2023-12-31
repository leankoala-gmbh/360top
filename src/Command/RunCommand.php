<?php

namespace Startwind\Top\Command;

use GuzzleHttp\Client;
use Startwind\Top\Application\MainFrame;
use Startwind\Top\Client\Server;
use Startwind\Top\Page\CpuPage;
use Startwind\Top\Page\CustomMetricPage;
use Startwind\Top\Page\DiskSpacePage;
use Startwind\Top\Page\LoadAveragePage;
use Startwind\Top\Page\MemoryPage;
use Startwind\Top\Page\NotificationPage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends TopCommand
{
    private string $sttyMode = '';

    private int $currentIntervalInMinutes = 30;

    private int $refreshIntervalInSeconds = 60;

    private array $menu = [];

    private array $intervalMenu = [];

    private Server $server;

    private MainFrame $mainFrame;

    private int $lastRefresh;

    protected function configure()
    {
        $this->setName('top');
        $this->setDescription('Run the interactive monitoring dashboard.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        pcntl_signal(SIGINT, [$this, "exitTool"]);

        if (!file_exists($this->getConfigFile())) {
            $this->errorBox($output, 'No configuration file found. Please run "360top init" before.');
            return Command::FAILURE;
        }

        $this->initCommandLine();

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

        $this->mainFrame->render();

        $this->menu[0]['page']->render($output, $mainFrame, $this->server, $this->getBestInterval());

        $this->lastRefresh = time();

        $this->doRun($output);

        return Command::SUCCESS;
    }

    private function initCommandLine(): void
    {
        $this->sttyMode = shell_exec('stty -g');

        system('stty cbreak');
        system('stty -echo');
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
                'label' => "(L)oad Average",
                'shortcut' => 'l',
                'page' => new LoadAveragePage()
            ],
            [
                'label' => "(D)isk Space",
                'shortcut' => 'd',
                'page' => new DiskSpacePage()
            ],
            [
                'label' => "(N)otifications",
                'shortcut' => 'n',
                'page' => new NotificationPage()
            ]
        ];

        $metricTypes = $this->server->getMetricTypes();

        $knownMetrics = ['cpu', 'mem', 'df', 'pn', 'net', 'process', 'uptime', 'la', 'io', 'load_per_core', 'swp'];
        // $knownMetrics = ['cpu'];

        $count = 0;

        foreach ($metricTypes as $metricType) {
            if (!in_array($metricType, $knownMetrics)) {
                $this->menu[] = [
                    'label' => '(' . $count . ') ' . str_replace('_', ' ', ucfirst($metricType)),
                    'metric' => $metricType,
                    'shortcut' => $count,
                    'page' => new CustomMetricPage()
                ];
                $count++;
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
        $lastChar = $this->menu[0]['shortcut'];

        while (true) {
            stream_set_blocking(STDIN, false);

            $commandCharacter = fread(STDIN, 16);

            if ($commandCharacter == "") {
                usleep(100000);
                $doRefresh = $this->handleRefresh();
                if (!$doRefresh) {
                    continue;
                } else {
                    $commandCharacter = $lastChar;
                }
            }

            stream_set_blocking(STDIN, true);

            if (strtolower($commandCharacter) === 'q') {
                $this->exitTool();
            }

            $lastChar = $this->handleKeyPress($output, $commandCharacter, $lastChar);
        }
    }

    private function exitTool(): void
    {
        stream_set_blocking(STDIN, true);
        $this->mainFrame->clear();

        if ($this->sttyMode) {
            shell_exec('stty ' . $this->sttyMode);
        }

        die();
    }

    private function handleRefresh(): bool
    {
        $refresh = $this->refreshIntervalInSeconds - (time() - $this->lastRefresh) % $this->refreshIntervalInSeconds;
        $this->mainFrame->setRefresh('Next refresh in ' . ($refresh - 1) . 's');

        if ($refresh === 1) {
            $this->lastRefresh = time();
            return true;
        } else {
            return false;
        }
    }

    private function handleKeyPress(OutputInterface $output, $commandCharacter, $lastChar)
    {
        $mainFrame = $this->mainFrame;

        $arrowKey = preg_replace('/[^[:print:]\n]/u', '', mb_convert_encoding($commandCharacter, 'UTF-8', 'UTF-8'));

        $reloadByArrow = false;

        if ($arrowKey === "[C") {
            $mainFrame->incCurrentPage();
            $commandCharacter = $lastChar;
            $reloadByArrow = true;
        } else if ($arrowKey === "[D") {
            $mainFrame->decCurrentPage();
            $commandCharacter = $lastChar;
            $reloadByArrow = true;
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
        } else if (ord($arrowKey) === 0) {
            $mainFrame->closeDropDown();
            $mainFrame->setDropDownByInterval($this->currentIntervalInMinutes);
            $commandCharacter = $lastChar;
        }

        if (!$mainFrame->isDropDownOpen()) {
            foreach ($this->menu as $menu) {
                if (strtolower($menu['shortcut']) === strtolower($commandCharacter)) {
                    if ($lastChar != $commandCharacter) {
                        $mainFrame->setPage(0, 0);
                    }
                    try {
                        $this->mainFrame->render();
                        if (array_key_exists('metric', $menu)) {
                            $menu['page']->render($output, $mainFrame, $this->server, $menu['metric'], $this->getBestInterval());
                        } else {
                            $menu['page']->render($output, $mainFrame, $this->server, $this->getBestInterval());
                        }
                    } catch (\Exception $exception) {
                        $this->errorBox($output, $exception->getMessage());
                        die;
                    }

                    if (!$reloadByArrow) {
                        $this->lastRefresh = time();
                    }

                    return $commandCharacter;
                }
            }
        }

        return $lastChar;
    }
}
