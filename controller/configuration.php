<?php namespace Controller;

trait Configuration {

  public function GETLogin(?string $model = null, ?string $message = null)
  {
    if ($model && $message) {
      [$id, $token] = explode('.', urldecode($message));
      
      if ($this->request->method->token(date('z'), $token)) {
        $this->response->authorize(\App\Model::FACTORY($model, $id));
      }
    }
    $route = array_combine(['controller', 'action'], $this->request->method->route);
    return ( new \App\View('layout.html') )->set('content', 'login.html')->render($route);
  }
  
  
  /*
    TODO
    [ ] Instead of sending the access variable, send the model to instantiate. make factory method in base model
    [x] Perhaps employ a __toString on the model so any model can have some kind of semi-unique identifier
    [ ] add a method to the person model, something like 'implements email' so that you can just go '$model->send('email') ??
    [ ] redirect properly to the page requested
  */
  public function POSTLogin(\App\Data $post, string $model) {
    
    $method   = $this->request->method;
    $instance = \App\Model::Factory($model, $post['@id']);

    [$controller, $action] = $method->route;
    
    $token = urlencode("{$post['@id']}.{$method->token(date('z'))}");
    $link  = sprintf('<a href="%s/%s/%s/%s/%s">login</a>', $method->host, $controller, $action, $model, $token);
    
    $result = \App\email((string)$instance, 'login link', $link);
    
    echo "<pre>".print_r($result)."</pre>";
    
  }
}