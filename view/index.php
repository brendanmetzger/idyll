<?php
namespace app;

# CONFIGURE 
define('PATH', realpath('../') . '/');
define('MODE', getenv('MODE') ?: 'production');

date_default_timezone_set ('America/Chicago');

// AUTOLOAD classes using the namespace 
spl_autoload_register(function ($class) {
  @include PATH . str_replace('\\', '/', $class) . '.php';
});



// Application (these are factories)
$http = Request::listen('http', function ($params) {

});

$cli = Request::listen('cli', function ($params) {

});

$layout = new view('layout.html');


echo $layout->render();


