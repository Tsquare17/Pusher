<?php

namespace Tsquare\Pusher;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PushCommand extends Command
{

    public function configure()
    {
        $this->setName('push')
            ->setDescription('Push files via FTP.')
            ->addArgument('host', InputArgument::OPTIONAL)
            ->addOption('files', 'f', InputOption::VALUE_REQUIRED, 'List of files separated by spaces.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $host = $input->getArgument('host') ?? null;
        $files = $input->getOption('files') ?? null;

        $this->pushFiles($input, $output, $host, $files);
    }

    public function pushFiles($input, $output, $host = null, $files = null)
    {
        if ($host) {
            $push = $this->push_from_stored_host($host, $this->database->getHost($host), $files);
            return ( $push === 'Success' ) ? $output->writeln($push) : $output->writeln('No matching hosts.');
        }

        if ($this->database->fetchAll('host')) {
            return $output->writeln(
                '<info>Still need to write logic for ftp using credentials from commandline</info>'
            );
        }
    }

    public function push_from_stored_host($output, $host, $files)
    {
        $success = false;
        $files = array($files);
        foreach ($files as $file) {
            $result = $this->push_single_file($output, $host, $file);
            if ($result === 'Success') {
                $success = true;
            }
        }

        $return = $success ? 'Success' : 'Fail';
        return $return;
    }

    public function push_single_file($output, $host, $file)
    {

        $server = $host[0]['host'];
        $user = $host[0]['user'];
        $pass = $host[0]['pass'];

        $conn = ftp_connect($server);
        $login_result = ftp_login($conn, $user, $pass);

        $put = ftp_put($conn, $file, $file, FTP_ASCII);

        ftp_close($conn);

        if ($put) {
            return 'Success';
        }
    }
}
