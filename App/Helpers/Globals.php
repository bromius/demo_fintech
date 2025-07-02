<?php

define('APP_DIR', __DIR__ . '/../');

/**
 * Configuration data
 * @return mixed
 */
function cfg($name) 
{
    return \Config::getInstance()->get($name);
}

/**
 * Slim framework
 * @return \Slim\Slim
 */
function app() 
{
    return \Bootstrap::getInstance()->app();
}

/**
 * Database connection object
 * @return \Medoo\Medoo
 */
function db() 
{
    return \Dbs::getInstance()->db();
}

/**
 * Symphony dumper
 * @var $data Data
 */
function dd($data) 
{
    dump($data);
    die();
}