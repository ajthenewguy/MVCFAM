<?php namespace App;
/**
 * Application data persistence configuration
 */

$db = array();
$db['enable'] = !!$_ENV['DB_ENABLED'];
$db['driver'] = $_ENV['DB_DRIVER'];
$db['host'] = $_ENV['DB_HOST'];
$db['name'] = $_ENV['DB_NAME'];
$db['user'] = $_ENV['DB_USER'];
$db['pass'] = $_ENV['DB_PASS'];


return $db;