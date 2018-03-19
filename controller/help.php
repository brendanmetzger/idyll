<?php namespace Controller;


class Help extends \App\Controller {
  use configuration;
  
  public function GETindex() {
    return new \App\View('help/overview.html');
  }
  
  public function CLIindex() {
    
  }
  
  public function CLIgenerate(string $filetype) {
    // generate controller
    // generate model
  }
}