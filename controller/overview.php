<?php namespace Controller;

class Overview extends \App\Controller {
  use configuration;
  
  
  public function GETindex($id = null) {

    $layout   = new \App\View('layout.html');
    
    if ($id) {
      $m = new \Model\Item($id);
    }
    
    return $layout->render(['items' => \Model\Item::list('/items/item'), 'title' => 'Working Draft']);
  }
  
  protected function GETedit(\Model\Person $person, ?string $type = null, ?string $id = null) {
    $view = new \App\View('layout.html');

    if ($type === null) {
      $this->types = (new \App\Data(['person', 'inventory', 'project', 'task']))->map(function($item) {
        return ['type' => $item];
      });
      $view->set('content', 'manage.html');
    }
    
    
    if ($type !== null) {
      $this->people = \Model\Person::list();
      $this->type = $type;
      $view->set('content', 'list.html');
    }
    
    $this->title = 'working still';
    $this->person = $person;
    
    return $view;
  }
  
  
}