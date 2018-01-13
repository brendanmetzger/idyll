<?php namespace App;

# CONFIGURE 
date_default_timezone_set ('America/Chicago');
define('ID', explode(':', getenv('ID')));

# REQUIREMENTS
foreach (['data', 'dom', 'io', 'locus', 'mvc'] as $file) require_once "../app/{$file}.php";

# AUTOLOAD non-essential classes organized by namespace
spl_autoload_register(function ($classname) {
  @include '../' . str_replace('\\', '/', strtolower($classname)) . '.php';
});

# INSTANTIATE the request.
$request = new Request( Method::New(getenv('REQUEST_METHOD')?:'CLI') );

$request->listen('http', function () {
  return $this->delegate('overview', 'index');
});

$request->listen('repl', function () {
  print_r($this);
  print_r($params);
  return "DONE";
});

try {
  
  echo $request->response();
  
} catch (\TypeError | \ReflectionException | \InvalidArgumentException $e) {
  echo (new View('layout/basic.html'))->set('content', 'content/error.html')->render([
    'message' => $e->getMessage(),
    'file'    => $e->getFile(),
    'line'    => $e->getLine(),
    'trace'   => array_reverse($e->getTrace()),
  ]);
  
} catch (\Exception $e) {
  echo "something worse?";
}