<?php

namespace Tsquare\Pusher;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListDirectoryCommand extends Command
{

    public function configure()
    {
        $this->setName('list')
            ->setDescription('List remote files.')
            ->addArgument('host', InputArgument::REQUIRED)
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Remote path to list.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $host = $input->getArgument('host') ?? null;
        $path = $input->getOption('path') ?? null;

        $host = $this->database->getHost($host);
        $conn = $this->connect($host);
        $list = $this->listFiles($conn, $path);
        $this->disconnect($conn);

        foreach ($list as $file) {
            $output->writeln("<info>$file</info>");
        }
    }

    public function connect($host)
    {
        if (isset($host[0])) {
            $server = $host[0]['host'];
            $user = $host[0]['user'];
            $pass = $host[0]['pass'];
        } else {
            $server = $host['host'];
            $user = $host['user'];
            $pass = $host['pass'];
        }

        $conn = ftp_connect($server);
        $login_result = ftp_login($conn, $user, $pass);

        return $conn;
    }

    public function listFiles($conn, $path)
    {
        $files = ftp_nlist($conn, $path);
        return $files;
    }

    public function disconnect($conn)
    {
        ftp_close($conn);
    }
}
