<?php
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/vendor/tiqr/tiqr-server-libphp/library/tiqr/Tiqr/AutoLoader.php';

//$proto = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : (isset($_SERVER['HTTPS']) ? "https" : "http");

function proto() {
    if( array_key_exists('HTTP_X_FORWARDED_PROTO', $_SERVER))
        return $_SERVER['HTTP_X_FORWARDED_PROTO'];
    if( array_key_exists('HTTPS', $_SERVER))
        return "on" === $_SERVER['HTTPS'] ? "https" : "http";
    return "http";
}

$options = array(
    //"identifier"      => "pilot.stepup.coin.surf.net",
    "name"            => "SURFconext Strong Authentication", // todo i18n
    "auth.protocol"       => "tiqrauth",
    "enroll.protocol"     => "tiqrenroll",
    "ocra.suite"          => "OCRA-1:HOTP-SHA1-6:QH10-S",
    "logoUrl"         => proto() . "://" . $_SERVER['HTTP_HOST'] . "/tiqrRGB.png",
    "infoUrl"         => proto() . "://" . $_SERVER['HTTP_HOST'] . "/info.html",
    "tiqr.path"         => __DIR__ . '/vendor/tiqr/tiqr-server-libphp/library/tiqr/',
    'phpqrcode.path' => '.',
    'zend.path' => __DIR__ . '/vendor/zendframework/zendframework1/library',
    'statestorage'        => array("type" => "file"),
    'userstorage'         => array("type" => "file", "path" => "/tmp", "encryption" => array('type' => 'dummy')),
    "usersecretstorage" => array("type" => "file"),
    "apns.certificate" => '',
    "apns.environment" => 'production',
    "debug" => false,
    "default_locale" => 'en',
    "translation"  =>  array(
        "en" => true,
        "nl" => true,
    ),
    'domain' => '', // The domain for this application, used for the 'stepup_locale' cookie
    "loghandler" => new Monolog\Handler\ErrorLogHandler(),
    "default_timezone" => "Europe/Amsterdam",
);

// override options locally. TODO merge with config
if( file_exists(dirname(__FILE__) . "/local_options.php") ) {
    include(dirname(__FILE__) . "/local_options.php");
}

set_include_path(get_include_path() . PATH_SEPARATOR . $options['zend.path']);

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

$autoloader = Tiqr_AutoLoader::getInstance($options); // needs {tiqr,zend,phpqrcode}.path
$autoloader->setIncludePath();

$userStorage = Tiqr_UserStorage::getStorage($options['userstorage']['type'], $options['userstorage']);

function generate_id($length = 4) {
    return base_convert(time(),10,36) . '-' . base_convert(rand(0, pow(36,$length)),10,36);
}
