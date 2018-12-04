<?php

    function db()
    {
        static $pdo;
        if ($pdo===null)
        {
            $pdo = new \PDO ();
        }
        return $pdo;
    }
