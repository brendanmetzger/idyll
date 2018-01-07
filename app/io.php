<?php namespace App;

/****         *************************************************************************************/
class Request {
  
  public  $start, $route, $params, $scheme, $method, $format, $redirect, $server,
          $listeners = [];

  /*
    TODO
    [ ] set up request based on time
    [ ] deal with cookies
    [ ] deal with post/get
  */
  public function __construct(array $server, array $request) {
    $this->start = microtime(true);
    if (! $this->method = @$server['REQUEST_METHOD']) {
      $this->method = 'CLI';
      $this->route  = preg_split('/\W/', $_SERVER['argv'][1]);
      $this->params = array_slice($_SERVER['argv'], 2);
      $this->format = 'txt';
      $this->scheme = 'repl';
    } else {
      $this->route  = array_filter($request['_r_']);
      $this->params = array_filter(explode('/', $request['_p_']));
      $this->format = $request['_e_'] ?: 'html';
      $this->scheme = 'http';
    }
    $this->server = $server;
  }

  /*
    TODO much tinkering to do here, not quite working [TYPES ARE AUTOMATICALLY CONVERTED]
  */
  private function filter(\ReflectionMethod $action, array $params) {
    foreach ($action->getParameters() as $index => $arg) {
      settype($params[$index] ?? $arg->getDefaultValue(), $arg->getType());
    }
    return $params;
  }
  
  public function listen (string $scheme, callable $callback): void {
    $this->listeners[$scheme] = $callback;
  }
  
  public function respond() {
    return $this->listeners[$this->scheme]->call($this);
  }
  
  /*
    TODO 
    [ ] get expected param types and typecast all data! ($action->getParameters(): [], param->getType())
    [ ] consider if it would be more elegant to have authenticate stay a method of the parent
        that executes the action of a child (which is allowed as a protected method).
  */
  public function delegate(array $route, array $params) {
    $controller = new \ReflectionClass('\\controller\\' . $route[0]);
    $action     = $controller->getMethod($this->method . $route[1]);
    $instance   = $controller->newInstance($this);
    
    if ( $action->isProtected() && $user = $instance->authenticate() ) {
      $action->setAccessible(true);
      array_unshift($params, $user);
    }
    
    return $action->invokeArgs($instance, $params);
  }
}



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
    return $this->request->respond();
  }
}