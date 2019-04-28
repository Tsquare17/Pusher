<?php

namespace Tsquare\Pusher;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteFileCommand extends Command
{

    public function configure()
    {
        $this->setName('delete')
            ->setDescription('Delete remote files.')
            ->addArgument('host', InputArgument::REQUIRED)
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Remote path to file.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $host = $input->getArgument('host');
        $path = $input->getOption('path');

        $host = $this->database->getHost($host);
        $conn = $this->connect($host);
        $file = $this->deleteFile($conn, $path);
        $this->disconnect($conn);

        if ($file) {
            $output->writeln("<info>Removed $path</info>");
        } else {
            $output->writeln("<info>Could not remove $path</info>");
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

    public function deleteFile($conn, $path)
    {
        // todo ui: Add check if is file or directory use the appropriate command
        $files = ftp_delete($conn, $path);
        return $files;
    }

    public function disconnect($conn)
    {
        ftp_close($conn);
    }
}
