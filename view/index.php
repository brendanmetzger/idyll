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
  return $this->delegate(array_replace(['overview', 'index'], $this->route), $this->params);
});


try {
  echo new Response($request);
} catch (\TypeError $e) {
  /*
   TODO
   [ ] Show appropriate message when controller not found/incorrect
   [ ] "                        for action
   [ ] "                        for protected methods 
  
  */
  print_r($e);
} catch (\Exception $e) {
  print_r($e);
}

