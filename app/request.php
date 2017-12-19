<?php

namespace app;

class Request {
  
  private $type,
          $redirect,
          $uri,
          $listeners = [];

  public function __construct(array $server, array $request)
  {
    $this->type     = $server['REQUEST_METHOD'] ?? 'CLI';
    $this->redirect = $server['HTTP_REFERER'] ?? '/';
    $this->uri      = $server['REQUEST_URI'];
  }
  
  public function listen (string $type, callable $callback) {
    $this->listeners[$type] = $callback;
  }
  
  public function respond()
  {
    return $this->listeners['http']->call($this, ['data']);
  }
  
  /*
    TODO 
    [ ] get expected param types and typecast all data!
    [ ] consider if it would be more elegant to have authenticate stay a method of the parent
        that executes the action of a child (which is allowed as a protected method).
  */
  public function delegate(string $class, string $method, array $params)
  {
    $controller = new \ReflectionClass('\\controller\\' . $class);
    $action     = $controller->getMethod($this->type . $method);
    $instance   = $controller->newInstance($this);
    
    if ( $action->isProtected() && $user = $instance->authenticate($this->request) ) {
      $action->setAccessible(true);
      array_unshift($params, $user);
    }
    
    return $action->invokeArgs($instance, $params);
  }
}