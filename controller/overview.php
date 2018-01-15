<?php namespace Controller;

class Overview extends \App\Controller {
  use configuration;
  
  
  public function GETindex($id = null) {

    if ($id) {
      $m = new \Model\Item($id);
    }
    
    $this->items = \Model\Item::list('/items/item');
    $this->title = 'Working Draft';
    return new \App\View('layout/full.html');
  }
  
  protected function GETcalendar(\Model\Person $user) {
    return (new \App\View('layout/full.html'))->set('content', 'content/calendar.html');
  }
  
  protected function GETedit(\Model\Person $person, ?string $type = null, ?string $id = null) {
    $view = new \App\View('layout/full.html');
    if ($type === null) {
      $this->types = (new \App\Data(['person', 'inventory', 'project', 'task']))->map(function($item) {
        return ['type' => $item];
      });
      $view->set('content', 'content/manage.html');
    }
    
    
    if ($type !== null) {
      $this->people = \Model\Person::list();
      $this->type = $type;
      $view->set('content', 'component/list.html');
    }
    
    $this->title = 'working still';
    $this->person = $person;
    
    return $view;
  }
  
  
  public function CLIexamine()
  {
    // getelementbyid vs xpath

    $start = microtime(true);
    
    
    

  
    
    return print_r(serialize($this->request->token));
  }
  
}