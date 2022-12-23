<?php
ini_set('display_errors', true);
ini_set('display_startup_errors', true);
ini_set('html_errors', true);
ini_set('session.gc_maxlifetime', 60*60*12);

session_set_cookie_params(60*60*12);

require_once(dirname(__FILE__) . '/config.php');


spl_autoload_register('my_autoload');

session_start();

$isLoginScript = preg_match('#/login.php$#', $_SERVER['SCRIPT_NAME']);
if (!isset($_SESSION['user_id']) && !$isLoginScript) {
    header('Location: login.php');
    die;
}

if (!$isLoginScript) {
    $db = connectDb(config()->db);
    $user = getUser($db);
}

function getUser($db) {
    $user = \model\EntityRepository::byName($db, 'user')->loadEntityById((int)$_SESSION['user_id']);
    return $user;
}

function my_autoload($className) {
    $fileName = config()->fs->classPath . '/' . preg_replace('#\\\#', '/', $className) . '.php';
    if ( file_exists($fileName) ) {
        require_once( $fileName );
    }

    if ( !class_exists($className) ) {
        // die('autoloader was unable to load class ' . $className);
    }
    
}

function CONFIG() {
    global $CONFIG;
    return $CONFIG;
}

function _echo($string) {
    echo _html($string, ENT_QUOTES, 'UTF-8');
}

function _html($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function connectDb($config) {
    $db = new \PDO('mysql:host=' . $config->server . ';dbname=' . $config->name, $config->user, $config->pass);
    // new \PDO($this->type.":host=".$server.";dbname=".$database, $username, $password
    // $db->connect($config->server, $config->user, $config->pass, $config->name);
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    return $db;
}

function _debug($msg, $file='debug.log') {
    file_put_contents($file, $msg, FILE_APPEND);
}
