<?php

use Medoo\Medoo;

class Dbs
{
    /**
     * @var Db Singleton instance
     */
    private static $instance;

    /**
     * @var Medoo\Medoo
     */
    private $db;

    /**
     * Get the configuration instance
     * @return static
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new static();
            static::$instance->connect();
        }
        return static::$instance;
    }

    public function connect()
    {
        return $this->db = new Medoo([
            'database_type'     => 'mysql',
            'charset'           => cfg('db.mysql.charset'),
            'server'            => cfg('db.mysql.host'),
            'database_name'     => cfg('db.mysql.name'),
            'username'          => cfg('db.mysql.user'),
            'password'          => cfg('db.mysql.pass'),
            'option' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        ]);
    }

    public function db()
    {
        return $this->db;
    }

    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}
}