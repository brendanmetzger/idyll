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
  
  public function GETindex()
  {
    $data = [
      [
        'id' => '9876AB',
        'text' => 'First Item',
        'date' => [
          'formatted' => '10/22/83',
          'day'       => 'Tuesday',
          'month'     => 'October',
        ],
        'name' => [
          'first' => 'Dean',
        ],
      ],
      [
        'id' => '774343',
        'text' => 'Second Item',
        'date' => [
          'formatted' => '01/18/83',
          'day'       => 'Monday',
          'month'     => 'January',
        ],
        'name' => [
          'first' => 'Bean',
        ],
      ]
    ];
    
    $layout = new \app\view('layout.html');
    $template = new \app\view('about.html');
    $template->getSlugs();


    // simulate cycle of dada
    foreach ($data as $datum) {
      foreach ($template->slugs as $slug) {
        $slug['node']->nodeValue =  \app\data::PAIR($slug['scope'], $datum);
      }
      $layout->merge($template);
    }
    
    
    return $layout->render();
  }
}