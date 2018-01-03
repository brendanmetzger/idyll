<?php
namespace controller;


class Overview extends \app\controller {
  
  public function __construct()
  {
    # code...
  }
  
  public function authenticate(\app\request $request)
  {
    return false;
  }
  
  public function GETspecial(int $int, int $string, float $float)
  {
    echo "<pre>";
    
    echo $int . "\n\n";
    
    echo $string . "\n\n";
    
    echo $float . "\n\n";
    
    echo "</pre>";
    
    return '';
  }
  
  public function GETindex(string $number = '0')
  {
    // singular
    $m = new \model\item('ABC');
        
    $layout   = new \app\view('layout.html');
    $template = new \app\view('about.html');
    $template->getSlugs();


    // simulate cycle of data
    foreach (\model\item::list('/items/item') as $item) {
      foreach ($template->slugs as $slug) {
        $slug['node'](\app\data::PAIR($slug['scope'], $item));
      }
      $layout->merge($template);
    }
    
    
    return $layout->render();
  }
}