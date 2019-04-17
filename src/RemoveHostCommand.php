<?php

namespace Tsquare\Pusher;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveHostCommand extends Command
{
    public function configure()
    {
        $this->setName('remove-host')
            ->setDescription('Remove a host by the hostname')
            ->addArgument('host', InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $host = $input->getArgument('host');

        $this->database->query('delete from host where host = :host', compact('host'));

        $output->writeln('<info>Host Removed</info>');
    }
}
