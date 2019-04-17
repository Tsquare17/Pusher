<?php

namespace Tsquare\Pusher;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListHostsCommand extends Command
{

    public function configure()
    {
        $this->setName('list-hosts')
            ->setDescription('Show all hosts.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->showHosts($output);
    }

    public function showHosts($output)
    {
        if (! $hosts = $this->database->fetchAll('host')) {
            return $output->writeln('<info>No hosts stored.</info>');
        }

        $table = new Table($output);

        $table->setHeaders(['Id', 'Host', 'User', 'Pass', 'Key', 'Group'])
            ->setRows($hosts)
            ->render();
    }
}
