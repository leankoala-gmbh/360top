<?php

namespace Startwind\Top\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class InitCommand extends TopCommand
{
    const AGENT_INI_FILE = '/etc/agent360-token.ini';

    protected function configure()
    {
        $this->setName('init');
        $this->setDescription('Initialize the 360top CLI tool.');
        // $this->addOption('serverId', 's', )
        parent::configure(); // TODO: Change the autogenerated stub
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
        $helper = $this->getHelper('question');

        if (file_exists(self::AGENT_INI_FILE)) {
            $config = parse_ini_file(self::AGENT_INI_FILE);
            $serverIdSuggest = $config['server'];
            $serverIdSuggestString = '(<info>default: ' . $serverIdSuggest . '</info>)' ;
        } else {
            $serverIdSuggest = '';
            $serverIdSuggestString = '';
        }

        // @todo also accept arguments (e.g. for ansible)

        $serverId = $helper->ask($input, $output, new Question('Server ID ' . $serverIdSuggestString . ': ', $serverIdSuggest));
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
