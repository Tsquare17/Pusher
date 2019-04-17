<?php

namespace Tsquare\Pusher;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddHostCommand extends Command
{
    public function configure()
    {
        $this->setName('add-host')
            ->setDescription('Add a new host')
            ->addArgument('host', InputArgument::REQUIRED)
            ->addArgument('user', InputArgument::REQUIRED)
            ->addArgument('pass', InputArgument::REQUIRED)
            ->addArgument('hostgroup', InputArgument::OPTIONAL)
            ->addOption('key', null, InputOption::VALUE_REQUIRED, 'Encryption key for the password.', 'null');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $host = $input->getArgument('host');
        $user = $input->getArgument('user');
        $cipher = "aes-128-gcm";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $tag = base64_encode($iv);
        $tag = $input->getOption('key') ? $tag : '';
        // $pass = $input->getOption('key') ? openssl_encrypt($input->getArgument('pass'), $cipher, $input->getOption('key'), OPENSSL_RAW_DATA, $iv, $tag) : $input->getArgument('pass');
        $pass = $input->getArgument('pass');
        $group = json_encode($input->getArgument('hostgroup'));

        // TODO add groups so we can push files to all hosts that belong to a certain group
        $this->database->query(
            'insert into host(host, user, pass, key, hostgroup) values(:host, :user, :pass, :tag, :group)',
            compact('host', 'user', 'pass', 'tag', 'group')
        );

        $output->writeln('<info>Host Added</info>');
    }
}
