<?php
namespace app;

/*
  TODO
  [ ] this needs to be an iterator/traversable 
  [ ] add adapters so that all data is treated the same (nodelist, array, etc.) in terms of the map/reduce type things
*/
class Data implements \iterator {
  
  private static $sources = [];
  
  // match/pair/tree lookup
  static public function PAIR(array $tree, $dataset)
  {
    while ($key = array_shift($tree)) {
       $data = $dataset[$key];
    }
    return $data;
  }
  
  static public function USE(string $source, ?string $path = null)
  {
    $document = self::$sources[$source] ?: self::$sources[$source] = new Document($source, ['validateOnParse' => true]);
    
    return $path ? new self($document->find($path), $source) : $document;
  }
  

  private $source,
          $dataset,
          $position  = 0,
          $callbacks = [];
  
  public function __construct(iterable $data, $source)
  {
    $this->dataset = $data;
    $this->source  = self::$sources[$source];
  }
  
  public function current()
  {
    $current = $this->dataset[$this->position];
    foreach ($this->callbacks as $callback) {
      $current = $callback($current);
    }
    return $current;
  }
  
  public function key()
  {
    return $this->position;
  }
  
  public function next()
  {
    return ++$this->position;
  }
  
  public function rewind()
  {
    $this->position = 0;
  }
  
  public function valid()
  {
    return isset($this->dataset[$this->position]);
  }
  
  // map, define callback
  public function map(callable $callback)
  {
    $this->callbacks[] = $callback;
    return $this;
  }
  
  public function sort(callable $callback)
  {
    $this->uasort($callback);
    return $this;
  }
  
  // filter
  public function filter(callable $callback)
  {
    return new \CallbackFilterIterator($this, $callback);
  }
  
  // limit
  public function limit($start, $length)
  {
    return new \LimitIterator($this, $start, $length);
  }
}