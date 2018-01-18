<?php namespace App;

# CONFIGURE (Apache and php.ini are where majority of configuration occurs)
define('ID', explode(':', (getenv('ID') ?: ':::')));

# REQUIRE application files
foreach (['structure', 'dom', 'io', 'locus', 'mvc'] as $file) require_once "../app/{$file}.php";

# AUTOLOAD non-essential classes organized by namespace
spl_autoload_register(function ($classname) {
  @include '../' . str_replace('\\', '/', strtolower($classname)) . '.php';
});

# INSTANTIATE the request.
$request = new Request( Method::New(getenv('REQUEST_METHOD')?:'CLI') );


# HANDLE different scenarios
$request->handle('http', function () {
  $controller = $this->delegate('overview', 'index');
  
  $ajax = false; // Full layout unnecessary w/ ajax
  if (!$ajax) {
    $controller->response->setTemplate(new View('layout/full.html'));
    // add before/after filter to move html around     
  }
  
  return $controller;
});

// FUTURE

// $response->handle('http', function($request) {
//   $somethin_prob_controller = $request->delegate('overview', 'index');
//   return '??';
// });
// 

// $response->handle('error', function($request){
//   if ($request->method == 'cli') {
//     # code...
//   }
// });

// try {
//   $response->prepare();
// } catch (\Exception $e) {
//   $response->prepare('error');
// } finally {
//   echo $response;
// }




$request->handle('console', function () {
  return $this->delegate('overview', 'examine');
});


# RESPOND with some output
try {
  
  $controller = $request->respond();
  $output     = $controller->response->compose($controller->data);
  
  // $debugging
  if ($output instanceof \DOMDocument) {
    $timestamp = microtime(true) - $controller->request->method->start;
    $output->documentElement->appendChild(new \DOMElement('script', "console.log({$timestamp});"));
  }
  
} catch (\TypeError | \ReflectionException | \InvalidArgumentException | \Exception | \Error $e) {
  // Need a controller here.
  // $controller = $request->respond('error');
  
  $data = [
    'trace'   => array_reverse($e->getTrace()),
    'file'    => $e->getFile(),
    'line'    => $e->getLine(),
    'message' => $e->getMessage(),
  ];
  
  if ($request->method == 'CLI') {
    print_r($data);
  } else {
    echo View::layout('basic')->set('content', 'content/error.html')->render($data);
  }
  exit();
  

} finally {
  // produce output
  echo $controller->response;
}



/*
TODO

[/] Determine a factory/configuration class that acts as a way to construct/instantiate common objects (Notably Models and views)
[ ] Work on calendaring
[X] Throw an exception if a view template is not found or improper
[ ] Show Sunrise/Sunset/Weather
[ ] Determine how models accept and merge input
[ ] The `Model::sign` method should ~accept~ and return a token
[ ] The token cookie value should represent the model it is storing a value for
[/] Use TRY/CATCH/FINALLLY to render output
[ ] Should not be able to go to login page if already authenticated, and not unless sent there by the application
[ ] Consider using typecasted controller params instantiating the classes they represent... `function GETEdit(\person $user, $type, \Factory $id)
[X] Controller should return a partial view. Application (index.php) can determine layout and merge rendered view.
[ ] Look up http headers on sending custom data.
[X] Use __callStatic in a factory context. now, Model::Make($type, $id) goes to Model::$type($id);  
[ ] Rethinking all the request/response shuffling around. Response is tied to a controller, but that seems unnecessary
*/