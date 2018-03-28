<?php namespace Controller;


class Project extends \App\Controller {
  use configuration;
  
  public function GETindex() {
    return new \App\View('line-ball.html');
  }

}