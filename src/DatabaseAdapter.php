<?php

namespace Tsquare\Pusher;

use PDO;

class DatabaseAdapter
{
    protected $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function fetchAll($tableName)
    {
        return $this->connection->query('select * from ' . $tableName)->fetchAll();
    }

    public function query($sql, $parameters)
    {
        return $this->connection->prepare($sql)->execute($parameters);
    }

    public function getHost($host)
    {
        return $this->connection->query("select * from `host` where host = '$host'")->fetchAll();
    }
    public function changeHost($host, $newHost)
    {
        return $this->connection->query("update `host` set host = '$newHost' where host = '$host'");
    }
}
