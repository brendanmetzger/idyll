<?php namespace Controller;


class Overview extends \App\Controller {
  use configuration;
  
  
  public function GETindex($id = null) {

    if ($id) {
      $m = new \Model\Item($id);
      print_r($m);
    }
    
    $this->items = \Model\Item::List('/items/*');
    $this->title = 'Working Draft';
  }
  
  protected function GETcalendar(\Model\Person $user) {
    return new \App\View('content/calendar.html');
  }
  
  protected function GETedit(\Model\Person $person, ?string $type = null, ?string $id = null) {

    if ($type === null) {
      $this->types = (new \App\Data(['person', 'inventory', 'project', 'task']))->map(function($item) {
        return ['type' => $item];
      });
      $path = 'content/manage.html';
    }
    
    if ($type !== null) {
      $this->people = \Model\Person::List('/model/person/*');
      $this->type = $type;
      $path = 'component/list.html';
    }
    
    $this->title = 'working still';
    $this->person = $person;
    
    return new \App\View($path);
  }
  
  
  public function CLIexamine()
  {

    $start = microtime(true);
    $count = 0;
    
    
    // $name = new \ReflectionClass('\\model\\item');
    // $name = '\\model\\item';
    $factory = \App\Factory::Model('item');
    for ($i=0; $i < 1000; $i++) {
      // $item = new $name('ABC');
      $item = $factory->newInstance('ABC');
      if ('ABC' == $item['@id']) {
        $count++;
      }
    }

    
    
    
    return (microtime(true) - $start) . ' - ' . $count . "\n";
  }
  
}
