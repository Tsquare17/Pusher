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
            ->addOption('files', 'f', InputOption::VALUE_REQUIRED, 'List of files separated by spaces.')
            ->addOption('group', 'g', InputOption::VALUE_REQUIRED, 'Group of hosts to push files to.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $host = $input->getArgument('host') ?? null;
        $files = $input->getOption('files') ?? null;

        if (!$files) {
            return $output->writeln('You must specify files to be pushed with -f');
        }

        $files = $this->getAllFilesIfDirectory($files);

        if ($input->getOption('group')) {
            $group = $input->getOption('group');
            $this->pushFilesToGroup($input, $output, $group, $files);
        } else {
            $this->pushFiles($input, $output, $host, $files);
        }
    }

    public function getAllFilesIfDirectory($files)
    {
        $return = [];
        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_dir($file)) {
                    $return = array_merge($return, $this->getDirContents($file));
                } else {
                    $return[] = $file;
                }
            }
        } else {
            if (is_dir($files)) {
                $return = array_merge($return, $this->getDirContents($files));
            } else {
                $return[] = $files;
            }
        }
        return $return;
    }

    public function getDirContents($dir, &$results = array())
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = $dir.DIRECTORY_SEPARATOR.$value;

            if (!is_dir($path)) {
                $results[] = $path;
            } elseif ($value != "." && $value != "..") {
                $this->getDirContents($path, $results);
                $results[] = $path;
            } elseif ($value === '.') {
                $results[] = $dir;
            }
        }

        return $results;
    }

    public function pushFilesToGroup($input, $output, $group, $files)
    {
        // get the hosts that belong to the group
        // loop through the hosts and push the files
        $hosts = $this->getHostsByGroup($group);
        // return $output->writeln(json_encode($hosts));
        $arrayOfSuccess = [];

        foreach ($hosts as $host) {
            if (is_array($files)) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $arrayOfSuccess[] = $this->push_single_file($host, $file);
                    } else {
                        $arrayOfSuccess[] = $this->create_directory($host, $file);
                    }
                }
            } else {
                if (is_file($files)) {
                    $arrayOfSuccess[] = $this->push_single_file($host, $files);
                } else {
                    $arrayOfSuccess[] = $this->create_directory($host, $files);
                }
            }
        }
        return $output->writeln(json_encode($arrayOfSuccess));
    }

    public function getHostsByGroup($group)
    {
        $hosts = $this->database->fetchAll('host');
        $wantedHosts = [];

        foreach ($hosts as $host) {
            $hostGroups = json_decode($host['hostgroup']);
            // return $hostGroups;
            if (is_array($hostGroups)) {
                foreach ($hostGroups as $groups) {
                    if ($group === $groups) {
                        $wantedHosts[] = $host;
                    }
                }
            } else {
                if ($group === $hostGroups) {
                    $wantedHosts[] = $host;
                }
            }
        }
        return $wantedHosts;
    }

    public function pushFiles($input, $output, $host = null, $files = null)
    {
        if ($host) {
            $push = $this->push_from_stored_host($host, $this->database->getHost($host), $files);
            return $output->writeln("<info>$push</info>");
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
        if (!is_array($files)) {
            $files = array($files);
        }
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $result = $this->push_single_file($host, $file);
            } elseif (is_dir($file)) {
                // create the directory
                $result = $this->create_directory($host, $file);
            } elseif (is_array($file)) {
                return "Failed to locate files";
            } else {
                return "Failed to locate $file";
            }
            if ($result === 'Success') {
                $success = true;
            }
        }

        $return = $success ? 'Success' : 'Fail';
        return $return;
    }

    public function push_single_file($host, $file)
    {
        $conn = $this->ftp_connect($host);

        // debugging getting the absolute path rather than relative
        // die(var_dump($file));
        $put = ftp_put($conn, $file, $file, FTP_ASCII);

        ftp_close($conn);

        if ($put) {
            return 'Success';
        } else {
            return $put;
        }
    }

    public function create_directory($host, $directory)
    {
        $conn = $this->ftp_connect($host);

        $result = @ftp_mkdir($conn, $directory);

        ftp_close($conn);

        if ($result) {
            return 'Success';
        } else {
            return "Failed to create remote directory $directory";
        }
    }

    public function ftp_connect($host)
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

        if ($login_result) {
            return $conn;
        } else {
            return false;
        }
    }
}
