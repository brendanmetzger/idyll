<?php namespace App;

abstract class Method {
  public $env, $start, $route, $scheme = 'http', $format = 'txt', $params, $data;
  
  static public function FACTORY() {
    $class = '\\App\\'.$_SERVER['REQUEST_METHOD'] ?? 'CLI';
    $instance = new $class();
    $instance->start = $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
    return $instance;
  }
  
  public function __toString(): string {
    $name = static::class;
    return substr($name, strrpos($name, '\\')+1);;
  }
  
}

class CLI extends Method {
  public $scheme = 'repl';
  public function __construct() {
    $this->route  = preg_split('/\W/', $_SERVER['argv'][1]);
    $this->params = array_slice($_SERVER['argv'], 2);
  }
}

class GET extends Method {
  public function __construct() {
    $this->route  = array_filter($_GET['_r_']);
    $this->params  = array_filter(explode('/', $_GET['_p_']));
    $this->format = $_GET['_e_'] ?: 'html';
    $this->scheme = 'http';
  }
}

class POST extends GET {
  public function __construct() {
    parent::__construct();
    $this->data = $_POST;
  }
}



/****         *************************************************************************************/
class Request {
  public  $start, $route, $params, $scheme, $method, $format, $server,
          $listeners = [];

  /*
    TODO
    [ ] set up request based on time
    [ ] deal with cookies
    [ ] deal with post/get
    [ ] fix this crappy constructor
  */
  public function __construct(array $server, array $request) {
    $this->method = Method::FACTORY();
  }

  
  public function listen (string $scheme, callable $callback): void {
    $this->listeners[$scheme] = $callback;
  }
  
  public function authenticate(\ReflectionMethod $method): bool {
    $model = $method->getParameters()[0]->getType();
    // use $_COOKIE
    // need to return an instance of the model sent in
    // need to add the model to the beginning of the params
    // array_unshift($this->params, $instance);
    
    if (true) {
      $method->setAccessible(true);
    }
    
    return false;
  }
  
  public function respond() {
    return $this->listeners[$this->method->scheme]->call($this);
  }
  
  /*
    TODO 
    [ ] get expected param types and typecast all data! ($action->getParameters(): [], param->getType())
    [ ] consider if it would be more elegant to have authenticate stay a method of the parent
        that executes the action of a child (which is allowed as a protected method).
  */
  public function delegate(array $route) {
    $route = array_replace($route, $this->method->route);
    [$instance, $method] = Controller::FACTORY($this, ...$route);
    return $method->invokeArgs($instance, $this->method->params);
  }
}


/*
[ ] response should be in control of filtering/reordering DOM
[ ] response should be in charge of caching
[ ] response should be able to return a partial if request is ajax.
*/

/****          *************************************************************************************/
class Response {
  
  static public function redirect($location_url, $code = 302) {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Location: {$location_url}", false, $code);
    exit();
  }
  
  private $request;
  
  public $content = [
    'html' => 'Content-Type: application/xhtml+xml; charset=utf-8',
    'json' => 'Content-Type: application/javascript; charset=utf-8',
    'xml'  => 'Content-Type: text/xml; charset=utf-8',
    'svg'  => 'Content-Type: image/svg+xml; charset=utf-8',
    'jpg'  => 'Content-Type: image/jpeg',
    'js'   => 'Content-Type: application/javascript; charset=utf-8',
    'css'  => 'Content-Type: text/css; charset=utf-8'
  ];
  
  public $status = [
    'unauthorized' => 'HTTP/1.0 401 Unauthorized',
    'incorrect'    => '404',
  ];
  
  public function __construct(Request $request) {
    $this->request = $request;
  }
  
  public function package() {
    // set headers (if HTTP [content-type, status, etc]);
    $out = $this->request->respond();
    echo microtime(true) - $this->request->method->start;
    return $out;
  }
}