<?php

namespace Startwind\Top\Command;

use GuzzleHttp\Client;
use JetBrains\PhpStorm\NoReturn;
use RectorPrefix202308\Symfony\Component\Console\Question\Question;
use Startwind\Top\Application\MainFrame;
use Startwind\Top\Client\Server;
use Startwind\Top\Page\CpuPage;
use Startwind\Top\Page\CustomMetricPage;
use Startwind\Top\Page\DiskSpacePage;
use Startwind\Top\Page\MemoryPage;
use Startwind\Top\Page\NotificationPage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

abstract class TopCommand extends Command
{
    private const INIT_FILE = '.360top.config';

    protected function getConfigFile(): string
    {
        $home = getenv("HOME");
        return $home . DIRECTORY_SEPARATOR . self::INIT_FILE;
    }
}
