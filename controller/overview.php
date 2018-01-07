<?php namespace Controller;

class Overview extends \App\Controller {
  
  public function __construct() {
    # code...
  }
  
  public function authenticate(\app\request $request) {
    return false;
  }
  
  
  public function GETindex() {
    // singular
    $m = new \Model\Item('ABC');
        
    $layout   = new \App\View('layout.html');
    $layout->footer = 'footer.html';
    
    return $layout->render(['items' => \Model\Item::list('/items/item'), 'title' => 'Working Draft']);
  }
}