<?php namespace Controller;

trait Configuration {
  
  /* TODO
    [ ] add a method to the person model, something like 'implements email' so that you can just go '$model->send('email') ??
    [ ] redirect properly to the page requested
    [ ] consider putting most of the login stuff in the authorize method
    [ ] make a proper response to form submission
    [ ] login page should be invalid for logged-in users
    [ ] make this method private, and switch access during login, so user can't go here twice.
  */
  public function GETLogin(?string $model = null, ?string $message = null, ?string $redirect = null) {

    if ($model && $message) {
      $this->response->authorize($model, $message, date_sunset(time(), SUNFUNCS_RET_TIMESTAMP));
    }
    
    $data = ['path' => urlencode(base64_encode($this->request->method->path))];
    return ( new \App\View('layout.html') )->set('content', 'transaction/login.html')->render($this->merge($data));
  }
  
  
  public function POSTLogin(\App\Data $post, string $model, string $path) {
    if (! $this->request->method->direct) $this->response->redirect('/');
    
    $body   = (new \App\View('layout/basic.html'))->set('content', 'transaction/email.html')->render($this->merge([
      'token' => urlencode($this->request->token->encode(date_sunset(time(), SUNFUNCS_RET_TIMESTAMP), $post['@id'])),
      'host'  => $this->request->method->host,
      'model' => $model,
      'path' => $path,
    ]));
    
    \App\Model::New($model, $post['@id'])->contact('Your Login Awaits..', $body);
    
    return "<pre>".print_r($result, true)."</pre>";
  }
}