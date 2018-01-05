<?php namespace App;


/* TODO
[ ] this needs to be an iterator/traversable 
[ ] add adapters so that all data is treated the same (nodelist, array, etc.) in terms of the map/reduce type things
*/


/****      *************************************************************************************/
class Data implements \iterator {
  
  static private $sources = [];

  static public function PAIR(array $namespace, $data) {
    while ($key = array_shift($namespace)) $data = $data[$key];
    return $data;
  }
  
  static public function USE(string $source, ?string $path = null) {
    $document = self::$sources[$source] ?: self::$sources[$source] = new Document($source, ['validateOnParse' => true]);
    return $path ? new self($document->find($path), $source) : $document;
  }
  

  private $source, $dataset,
          $cursor = 0,
          $maps   = [];
  
  
  public function __construct(iterable $data, $source)
  {
    $this->dataset = $data;
    $this->source  = self::$sources[$source];
  }
  
  public function current()
  {
    $current = $this->dataset[$this->cursor];
    foreach ($this->maps as $callback) $current = $callback($current);
    return $current;
  }
  
  public function key()
  {
    return $this->cursor;
  }
  
  public function next()
  {
    return ++$this->cursor;
  }
  
  public function rewind()
  {
    $this->cursor = 0;
  }
  
  public function valid()
  {
    return isset($this->dataset[$this->cursor]);
  }
  
  public function map(callable $callback)
  {
    $this->maps[] = $callback;
    return $this;
  }
  
  public function sort(callable $callback)
  {
    $this->uasort($callback);
    return $this;
  }
  
  public function filter(callable $callback)
  {
    return new \CallbackFilterIterator($this, $callback);
  }

  public function limit($start, $length)
  {
    return new \LimitIterator($this, $start, $length);
  }
}