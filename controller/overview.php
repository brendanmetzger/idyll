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
    $template = new \App\View('about.html');
    $template->getSlugs();


    // simulate cycle of data
    foreach (\Model\Item::list('/items/item') as $item) {
      foreach ($template->slugs as $slug) {
        $slug['node'](\App\Data::PAIR($slug['scope'], $item));
      }
      $layout->merge($template);
    }
    
    
    return $layout->render();
  }
}