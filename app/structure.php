<?php namespace App;

/****         ************************************************************************** FACTORY */
class Factory {
  static public function __callStatic($namespace, $params) {
    return new \ReflectionClass("\\{$namespace}\\{$params[0]}");
  }
}

class Slug {
  private $model, $key;
  
  private function slugify(string $input) {
    // TODO: during slugify, validate model to see if slug is unique
    return strtolower(preg_replace('/\W/', '-', $input));
  }
  
  public function __construct(Model $model, string $key) {
    $this->model = $model;
    $this->key   = $key;
  }
  
  public function __toString() {
    return $this->slugify($this->model[$this->key]);
  }
}

/****      ******************************************************************************** DATA */
class Data extends \ArrayIterator {
  
  static private $store = [];
  
  static public function PAIR(array $namespace, $data) {
    while ($key = array_shift($namespace)) {
      if (! isset($data[$key]) && ! array_key_exists($key, $data)) {
        throw new \UnexpectedValueException($key);
      }
      $data = $data[$key];      
    }
    return $data;
  }
  
  static public function Use(string $source, ?string $path = null) {
    $document = self::$store[$source] ?? self::$store[$source] = new Document($source, ['validateOnParse' => true]);
    return $path ? new self($document->query($path)) : $document;
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
  
  public function merge(array $data) {
    // this will be called when element is being merged against a list of data. 
  }
  
  public function __invoke($param) {
    return $this->current()($param);
  }
  
  public function __toString() {
    return (string) $this->current();
  }
}

/****          ************************************************************************* REGISTRY */
trait Registry {
  public $data = [];
  public function __get($key) {
    return $this->data[$key] ?? null;
  }
  
  public function __set($key, $value) {
    $this->data[$key] = $value;
  }
  
  public function merge(array $data) {
    return array_merge($this->data, $data);
  }
}

class Help implements \ArrayAccess {
  public function offsetExists ($key) {}
  public function offsetSet ($key, $value) {}
  public function offsetUnset ($key) {}
  public function offsetGet ($method) {
    if (! is_callable($method)) throw new \Exception("{$method} is not callable");
    return function (array $value) {
      $method(...$value);
    };
  }
    
}


/*

helper methods in template? Say I wanted to the first character of a word—to accomplish, I've 
always just generated a method in each individual model that might do a one off. (this can mildly bloat models, though this is a really really small problem)

<p>[$item:firstletteroftitle]</p>

this requires adding a method to every model whenever that feature is needed. something like

<p>[$help\substr\item:title|0|1]</p>

would attempt to do the function call automatically through composition of functions.


the pair method would have to do something like:
if(is_callable($out)) {
  $out = $out(self::pair([$namespace, $data), ...explode('|', $namespace))]);
}

Thoughts: the template syntax, while intriguing, is looking a bit messy. I like the recursion of the
PAIR method, and think that might work out elegantly. Continuing to ponder...
*/