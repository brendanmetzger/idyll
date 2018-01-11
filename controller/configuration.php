<?php namespace Controller;

trait Configuration {
  
  /* TODO
    [ ] add a method to the person model, something like 'implements email' so that you can just go '$model->send('email') ??
    [ ] redirect properly to the page requested
    [ ] consider putting most of the login stuff in the authorize method
    [ ] make a proper response to form submission
    [ ] login page should be invalid for logged-in users
  */
  public function GETLogin(?string $model = null, ?string $message = null, ?string $redirect = null)
  {
    if ($model && $message) {
      $this->response->authorize($model, $message, date_sunset(time(), SUNFUNCS_RET_TIMESTAMP));
    }

    $route = array_combine(['controller', 'action'], $this->request->method->route);
    return ( new \App\View('layout.html') )->set('content', 'login.html')->render($route);
  }
  
  
  public function POSTLogin(\App\Data $post, string $model) {

    $body   = (new \App\View('layout/basic.html'))->set('content', 'transaction.html')->render($this->merge([
      'token' => urlencode($this->request->token->encode(date_sunset(time(), SUNFUNCS_RET_TIMESTAMP), $post['@id'])),
      'host'  => $this->request->method->host,
      'model' => $model,
      'redir' => '',
    ]));
    
    $result = \App\email(\App\Model::New($model, $post['@id']), 'Your Login Awaits..', $body);
    return "<pre>".print_r($result, true)."</pre>";
    
  }
}