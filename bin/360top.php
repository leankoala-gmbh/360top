<?php

include_once __DIR__ . '/../vendor/autoload.php';

use Startwind\Top\Command\RunCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$command = new RunCommand();

$application->add($command);

$application->setDefaultCommand($command->getName());

$application->run();
