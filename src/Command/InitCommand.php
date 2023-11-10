<?php

namespace Startwind\Top\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Question\Question;

#[AsCommand(name: 'init')]
class InitCommand extends TopCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        // @todo also accept arguments (e.g. for ansible)

        $serverId = $helper->ask($input, $output, new Question('Server ID: '));
        $apiToken = $helper->ask($input, $output, new Question('API token: '));

        $config = [
            'serverId' => $serverId,
            'apiToken' => $apiToken
        ];

        // @todo validate

        $configString = json_encode($config, JSON_PRETTY_PRINT);

        file_put_contents($this->getConfigFile(), $configString);

        $output->writeln(['', '<info>Configuration successfully stored</info>']);

        return Command::SUCCESS;
    }
}
