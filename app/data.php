<?php namespace App;

/* TODO 
[ ] data object should implement ArrayAccess
[ ] devise way so that when invoked without an index number, it returns the first item.
*/
/****      *************************************************************************************/
class Data extends \ArrayIterator {
  
  static private $sources = [];

  static public function PAIR(array $namespace, $data) {
    while ($key = array_shift($namespace)) $data = $data[$key];
    return $data;
  }
  
  static public function USE(string $source, ?string $path = null) {
    $document = self::$sources[$source] ?: self::$sources[$source] = new Document($source, ['validateOnParse' => true]);
    return $path ? new self($document->find($path)) : $document;
  }

  private $maps = [];
  
  public function __construct(iterable $data) {
    parent::__construct(! is_array($data) ? iterator_to_array($data) : $data);
  }
    
  public function current() {
    $current = parent::current();
    foreach ($this->maps as $callback) $current = $callback($current);
    return $current;
  }
  
  public function map(callable $callback) {
    $this->maps[] = $callback;
    return $this;
  }
  
  public function sort(callable $callback) {
    $this->uasort($callback);
    return $this;
  }
  
  public function filter(callable $callback) {
    return new \CallbackFilterIterator($this, $callback);
  }

  public function limit($start, $length) {
    return new \LimitIterator($this, $start, $length);
  }
}