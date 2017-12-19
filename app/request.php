<?php

namespace app;

class Request {
  
  private $protocol,
          $method,
          $redirect,
          $uri,
          $listeners = [];

  public function __construct(array $server, array $request)
  {
    $this->protocol = 'http';
    $this->method   = $server['REQUEST_METHOD'] ?? 'CLI';
    $this->redirect = $server['HTTP_REFERER'] ?? '/';
    $this->uri      = $server['REQUEST_URI'];
  }

  /*
    TODO much tinkering to do here
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
  public function delegate(string $class, string $method, array $params)
  {
    $controller = new \ReflectionClass('\\controller\\' . $class);
    $action     = $controller->getMethod($this->method . $method);
    // $params     = $this->filter($action, $params);
    $instance   = $controller->newInstance($this);
    
    if ( $action->isProtected() && $user = $instance->authenticate($this->request) ) {
      $action->setAccessible(true);
      array_unshift($params, $user);
    }
    
    return $action->invokeArgs($instance, $params);
  }
}