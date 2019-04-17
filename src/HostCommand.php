<?php

// add to groups
// change password
// change user

namespace Tsquare\Pusher;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HostCommand extends Command
{

    public function configure()
    {
        $this->setName('host')
            ->setDescription('Set of commands pertaining to host modification.')
            ->addOption('change', 'c', InputOption::VALUE_REQUIRED, '-c old-host,new-host', null);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $hosts = explode(',', $input->getOption('change'));
        $change = $this->change_hostname($hosts);
        if ($change) {
            $output->writeln("<info>Changed $hosts[0] to $hosts[1]</info>");
        }
    }

    public function change_hostname($hosts)
    {
        return $this->database->changeHost($hosts[0], $hosts[1]);
    }
}
