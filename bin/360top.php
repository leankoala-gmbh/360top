<?php

include_once __DIR__ . '/../vendor/autoload.php';

use Startwind\Top\Command\InitCommand;
use Startwind\Top\Command\RunCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$command = new RunCommand();

$application->add($command);

$application->add(new InitCommand());

$application->setDefaultCommand($command->getName());

$application->run();
