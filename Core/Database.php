<?php

namespace Core;

use PDO;

class Database
{
    private $connection;

    private $statement;

    public function __construct($config)
    {
        $dsn = "pgsql:" . http_build_query($config, '', ';');

        $this->connection = new PDO($dsn, 'postgres', 'Eduardo130#', [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public function get()
    {
        return $this->statement->fetchAll();
    }

    public function Query($query, $params = [])
    {
        $this->statement = $this->connection->prepare($query);

        $this->statement->execute($params);

        return $this;
    }

    public function find()
    {
        return $this->statement->fetch();
    }

    public function FindOrFail()
    {
        $result = $this->find();

        if (!$result)
        {
            abort();
        }

        return $result;
    }
}