<?php
if (!defined("_VALID_PHP")) {
    die ('Direct access to this location is not allowed.');
}
session_start();
ini_set('session.gc_maxlifetime', 60 * 60 * 8);
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/misc/Registry.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/config.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

/*Database configuration*/
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/misc/Database.php");
Registry::set("database", new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME));
$db = Registry::get("database");
$db->connect();
/*System Manager configuration*/
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/sys/SystemManager.php");

Registry::set("sys", new SystemManager());
$sys = Registry::get("sys");
$sys->configure();

/*User configuration*/
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/user/User.php");
Registry::set("user", new User());
$user = Registry::get("user");
$user->configure();

if (!defined("NO_STRUCT")) {
    $sys->displayStruct("header");
}

/*login protect*/
if (defined('login-protect') && !$user->isLoggedIn()) {
    header("Location: /?ref=" . $_SERVER['REQUEST_URI']);
}
?>