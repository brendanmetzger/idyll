<?php namespace Controller;


class Visual extends \App\Controller {
  use configuration;
  
  public function GETindex() {
    // make two sections, business and pleasure
    return new \App\View('visual/cubism.html');
  }

}