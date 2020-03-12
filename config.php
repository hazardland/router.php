<?php

function db()
{
    static $pdo;
    if ($pdo===null)
    {
        $pdo = new \PDO('mysql:host=127.0.0.1;dbname=test', 'root', '1234');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
    }
    return $pdo;
}
