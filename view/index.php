<?php namespace App;

# CONFIGURE (Apache and php.ini are where majority of configuration occurs)
define('ID', explode(':', (getenv('ID') ?: ':::')));

# REQUIRE application files
foreach (['data', 'dom', 'io', 'locus', 'mvc'] as $file) require_once "../app/{$file}.php";

# AUTOLOAD non-essential classes organized by namespace
spl_autoload_register(function ($classname) {
  @include '../' . str_replace('\\', '/', strtolower($classname)) . '.php';
});

# INSTANTIATE the request.
$request = new Request( Method::New(getenv('REQUEST_METHOD')?:'CLI') );

$request->handle('http', function () {
  $layout = new View('layout/full.html');
  return $this->delegate('overview', 'index');
});

$request->handle('repl', function () {
  return $this->delegate('overview', 'examine');
});


# RESPOND with some output
try {
  
  echo $request->response();
  
} catch (\TypeError | \ReflectionException | \InvalidArgumentException $e) {
  echo (new View('layout/basic.html'))->set('content', 'content/error.html')->render([
    'message' => $e->getMessage(),
    'file'    => $e->getFile(),
    'line'    => $e->getLine(),
    'trace'   => array_reverse($e->getTrace()),
  ]);
  
} catch (\Exception | \Error $e) {
  echo (new View('layout/basic.html'))->set('content', 'content/error.html')->render([
    'message' => $e->getMessage(),
    'file'    => $e->getFile(),
    'line'    => $e->getLine(),
    'trace'   => array_reverse($e->getTrace()),
  ]);
  ;
} finally {
  
}



/*
TODO
[ ] Determine a factory/configuration class that acts as a way to construct/instantiate common objects (Notably Models and views)
    - Classes that can be 'factoried have a trait of 'factory' or implement a factory interface that accept a factory object
    - this is like new Factory("\App\View", 'layout/%s.html) provides a way to run View::Make('basic')->set('content', '...');
                                                               which is currently: (new View('layout/basic.html))->set('content', '...');  
    - The opposition to just making a static 'layout' method of the View class is that while convenient, it's not necessary.
      Convenience as a pattern is against principles, but if the Model and Controller could all apply an interface/trait that produces a factory
      applied universally, then there may be an opportunity to wind up writing less code, which is a principle that is acceptable.
    - Could be Model::Make('person', $id);
    -          Controller::Make(['class', 'action'], $request); NOTE[ this factory is way more involved that the other two, could be problematic]
[ ] Work on calendaring
[X] Throw an exception if a view template is not found or improper
[ ] Show Sunrise/Sunset/Weather
[ ] Determine how models accept and merge input
[ ] The `Model::sign` method should ~accept~ and return a token
[ ] The token cookie value should represent the model it is storing a value for
[ ] Use TRY/CATCH/FINALLLY to render output
[ ] Should not be able to go to login page if already authenticated, and not unless sent there by the application
[ ] Consider using typecasted controller params instantiating the classes they represent... `function GETEdit(\person $user, $type, \Factory $id)
[ ] Controller should return a partial view. Application (index.php) can determine layout and merge rendered view.
[ ] Look up http headers on sending custom data.
[ ] Use __callStatic in a factory context. now, Model::Make($type, $id) goes to Model::$type($id);  

*/