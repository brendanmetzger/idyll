<?php namespace Controller;

trait Configuration {

  public function GETLogin(?string $model = null, ?string $message = null)
  {
    // TODO consider putting all of this in the authorize method
    if ($model && $message) {
      $token = new \App\Token(ID);
      $hash  = urldecode($message);
      $id    = $token->decode($hash);

      if ($token->validate($hash, date('z'), $id)) {
        $this->response->authorize($token, \App\Model::New($model, $id));
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
    $instance = \App\Model::New($model, $post['@id']);

    [$controller, $action] = $method->route;


    $token = urlencode((new \App\Token(ID))->encode(date('z'), $post['@id']));
    $link  = sprintf('<a href="%s/%s/%s/%s/%s">login</a>', $method->host, $controller, $action, $model, $token);
    
    $result = \App\email((string)$instance, 'login link', $link);
    
    echo "<pre>".print_r($result)."</pre>";
    
  }
}