<?php namespace Controller;

class Overview extends \App\Controller {
  
  public function __construct() {
    # code...
  }
  
  public function authenticate(\app\request $request) {
    return false;
  }
  
  
  public function GETindex($id = null) {

    $layout   = new \App\View('layout.html');
    $layout->footer = 'footer.html';
    
    if ($id) {
      $m = new \Model\Item($id);
    }
    
        
    
    return $layout->render(['items' => \Model\Item::list('/items/item'), 'title' => 'Working Draft']);
  }
}