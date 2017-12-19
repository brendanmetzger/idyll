<?php

namespace app;

class Request {
  
  public  $route,
          $params,
          $protocol,
          $method,
          $type,
          $redirect,
          $uri,
          $listeners = [];

  /*
    TODO 
    [ ] deal with cookies
    [ ] deal with post/get
  */
  public function __construct(array $server, array $request)
  {
    $this->route    = array_filter($request['_r_']);
    $this->params   = array_filter(explode('/', $request['_p_']));
    $this->type     = $request['_e_'] ?: 'html';
    print_r($this->type);
    $this->protocol = 'http';
    $this->method   = $server['REQUEST_METHOD'] ?? 'CLI';
    $this->uri      = $server['REQUEST_URI'];
  }

  /*
    TODO much tinkering to do here, not quite working [TYPES ARE AUTOMATICALLY CONVERTED]
  */
  private function filter(\ReflectionMethod $action, array $params)
  {
    foreach ($action->getParameters() as $index => $arg) {
      settype($params[$index] ?? $arg->getDefaultValue(), $arg->getType());
    }
    return $params;
  }
  
  public function listen (string $protocol, callable $callback) {
    $this->listeners[$protocol] = $callback;
  }
  
  /*
    TODO Catch typeerrors here
  */
  public function respond()
  {
    return $this->listeners[$this->protocol]->call($this, ['0.6']);
  }
  
  /*
    TODO 
    [ ] get expected param types and typecast all data! ($action->getParameters(): [], param->getType())
    [ ] consider if it would be more elegant to have authenticate stay a method of the parent
        that executes the action of a child (which is allowed as a protected method).
  */
  public function delegate(array $route, array $params)
  {
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