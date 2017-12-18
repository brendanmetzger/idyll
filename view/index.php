<?php
namespace app;

# CONFIGURE 
define('PATH', realpath('../') . '/');
define('MODE', getenv('MODE') ?: 'production');

date_default_timezone_set ('America/Chicago');

# AUTOLOAD classes organized by namespace
spl_autoload_register(function ($classname) {
  @include PATH . str_replace('\\', '/', $classname) . '.php';
});

# EXECUTE the application.
$request = new Request($_SERVER, $_REQUEST);
$request->listen('http', function ($params) {
  return $this->delegate('overview', 'index', $params);
});


echo new Response($request);
