<?php

namespace Startwind\Top\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class InitCommand extends TopCommand
{
    const AGENT_INI_FILE = '/etc/agent360-token.ini';

    protected function configure()
    {
        $this->setName('init');
        $this->setDescription('Initialize the 360top CLI tool.');

        $this->addOption('token', 't', InputOption::VALUE_OPTIONAL, 'The 360 Monitoring API token.');
        $this->addOption('serverId', 's', InputOption::VALUE_OPTIONAL, 'The 360 Monitoring serverId.');

        $this->addOption('fromAgentConfig', null, InputOption::VALUE_NONE, 'Take server ID from 360 agent config.');
        // $this->addOption('serverId', 's', )
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $serverId = false;
        $serverIdSuggest = '';
        $serverIdSuggestString = '';

        if ($input->getOption('serverId')) {
            $serverId = $input->getOption('serverId');
        } else {
            if (file_exists(self::AGENT_INI_FILE)) {
                $config = parse_ini_file(self::AGENT_INI_FILE);
                $serverIdSuggest = $config['server'];
                $serverIdSuggestString = '(<info>default: ' . $serverIdSuggest . '</info>)';

                if ($input->getOption('fromAgentConfig')) {
                    $serverId = $serverIdSuggest;
                }
            }
        }

        if (!$serverId) {
            $serverId = $helper->ask($input, $output, new Question('Server ID ' . $serverIdSuggestString . ': ', $serverIdSuggest));
        }

        if ($input->getOption('token')) {
            $apiToken = $input->getOption('token');
        } else {
            $apiToken = $helper->ask($input, $output, new Question('API token: '));
        }

        $valid = $this->validate($output, $serverId, $apiToken);

        if (!$valid) {
            return Command::FAILURE;
        }

        $config = [
            'serverId' => $serverId,
            'apiToken' => $apiToken
        ];

        $configString = json_encode($config, JSON_PRETTY_PRINT);

        file_put_contents($this->getConfigFile(), $configString);

        $output->writeln(['', '<info>Configuration successfully stored</info>']);

        return Command::SUCCESS;
    }

    private function validate(OutputInterface $output, string $serverId, string $apiToken): bool
    {
        if (strlen($serverId) !== 24) {
            $output->writeln([
                '',
                '<error>                                                   ',
                '  The server ID must be exact 24 characters long.  ',
                '                                                   </error>',
            ]);
            return false;
        }

        if (strlen($apiToken) !== 64) {
            $output->writeln([
                '',
                '<error>                                                   ',
                '  The API token must be exact 64 characters long.  ',
                '                                                   </error>',
            ]);
            return false;
        }

        return true;
    }
}
