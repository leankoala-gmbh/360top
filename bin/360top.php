<?php

include_once __DIR__ . '/../vendor/autoload.php';

use SelfUpdate\SelfUpdateCommand;
use Startwind\Top\Command\InitCommand;
use Startwind\Top\Command\RunCommand;
use Symfony\Component\Console\Application;

const TOP_VERSION = '##TOP_VERSION##';
const TOP_NAME = '360Top';
const TOP_NAME_LONG = '360top - Command Line Dashboard for 360 Monitoring';

$application = new Application();

$application->setName(TOP_NAME_LONG);
$application->setVersion(TOP_VERSION);

$command = new RunCommand();

$application->add($command);

$application->add(new InitCommand());

# Others
if (!str_contains(TOP_VERSION, '##TOP_VERSION')) {
    $application->add(new SelfUpdateCommand(TOP_NAME, TOP_VERSION, "leankoala-gmbh/360top"));
}

$application->setDefaultCommand($command->getName());

$application->run();
