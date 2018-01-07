<?php namespace App;

/****       *************************************************************************************/
class Model implements \ArrayAccess {
  
  protected $context;

  public function __construct($context, array $data = [])
  {
    if ($context instanceof Element) {
      $this->context = $context;
    } else if (empty($data) && ! $this->context = $this->authenticate($context)){
      throw new \InvalidArgumentException("The item specified does not exist.", 1);
    } else {
      // TODO determine how to create a new item
    }
    
    if ($data) {
      // Context will be an element, and the element will control the merging, not the model
      $this->context->merge($data);
    }
  }
  
  static public function LIST(?string $path = null): \App\Data {
    return Data::USE(static::SOURCE, $path ?: static::PATH)->map(function($item) {
      // this should be a factory: if path is not standard, may be a different model
      return new static($item);
    });
  }
  
  public function authenticate($criteria) {
    if (! $item = Data::USE(static::SOURCE)->getElementById($criteria)) {
      throw new \Exception("Unable to locate the requested resource ({$context}). (TODO, better exceptinon type)", 1);
    }
    return $item;
  }
  
  public function offsetExists($offset) {
    return ! is_null($this->context);
  }

  public function offsetGet($offset) {
    $method  = "get{$offset}";
    $context = $this->context[$offset];
    return method_exists($this, $method) ? $this->{$method}($context) : $context;
  }

  public function offSetSet($offset, $value) {
    return $this->context[$offset] = $value;
  }

  public function offsetUnset($offset) {
    unset($this->context[$offset]);
    return true;
  }
}

/* TODO
[x] falsy data should just put in an empty value
[x] absent data should delete components in view
   [x] regression: absent data cannot be removed immediately from template, as it will effect renderability of going forward
   [x] an array of removals containting getNodePath()'s could be used to target removals and looped/reset effciently
       [x] this would have to be done in reverse, as document would change as removals are processed
   [ ] Removal needs adjusting, as it is only applied to iterated methonds at the moment
render Method
[/] remove nodes that have been slated for demo
[ ] run before/after filters

*/

/****      *************************************************************************************/
class View {
  private $document, $slugs = [], $templates = [];
  
  public function __construct($input) {
    $this->document = new Document($input);
  }
  
  public function render($data = [], bool $parse = true): Document {
    
    foreach ($this->getTemplates('insert') as [$path, $ref]) {
      $this->import((new Self($path))->render($data, false), $ref);
    }
    
    foreach ($this->getTemplates('replace') as [$prop, $ref]) {
      if (isset($this->templates[$prop])) {
        $this->import((new Self($this->templates[$prop]))->render($data, false), $ref->nextSibling);
        $ref -> parentNode -> removeChild($ref);
      }
    } 
    
    foreach ($this->getTemplates('iterate') as [$key, $ref]) {
      $view = new Self( $ref -> parentNode -> removeChild( $ref -> nextSibling ));
      foreach ($data[$key] as $datum) {
        $view->cleanup($this->import($view->render($datum), $ref, 'insertBefore'));
      }
      $ref->parentNode->removeChild($ref);
    }
      
    if ($parse) {
      foreach ($this->getSlugs() as [$node, $scope]) {
        try {
          $node(Data::PAIR($scope, $data));
        } catch (\UnexpectedValueException $e) {
          $this->cleanup($node);
        }
      }
    }

    return $this->document;
  }
  
  public function __set(string $key, string $path) {
    $this->templates[$key] = $path;
  }
  
  private function cleanup(?\DOMNode $node): void {
    static $remove = [];
    if ($node instanceof \DOMElement) {
      while($path = array_pop($remove)) {
        $item = $node->ownerDocument->find($path, $node)[0];
        $item->parentNode->removeChild($item);
      }
    } else {
      $remove[] = '/'.$node->getNodePath().'/parent::*';
    }
  }
  
  private function getTemplates($key) {
    $query = "./descendant::comment()[ starts-with(normalize-space(.), '{$key}')"
           . (($key == 'iterate') ? ']' : 'and not(./ancestor::*/preceding-sibling::comment()[iterate])]');

    return (new Data($this -> document -> find( $query )))->map( function ($stub) {
      return [preg_split('/\s+/', trim($stub->nodeValue))[1], $stub];
    });    
  }
  
  private function getSlugs(): array {
    return $this->slugs ?: ( function (&$out) {
      $query = "substring(.,1,1)='[' and contains(.,'\$') and substring(.,string-length(.),1)=']' and not(*)";
      foreach ( $this -> document -> find("//*[{$query}]|//*/@*[{$query}]") as $slug ) {        
        preg_match_all('/\$+[\@a-z\_\:0-9]+\b/i', $slug( substr($slug, 1,-1) ), $match, PREG_OFFSET_CAPTURE);
      
        foreach (array_reverse($match[0]) as [$k, $i]) {
          $___ = $slug -> firstChild -> split($i) -> split($k) -> previousSibling;
          if (substr( $___( substr($___,1) ),0,1 ) != '$') $out[] = [$___, explode(':', $___)];
        }
      }
      return $out;

    })($this->slugs);
  }
  
  private function import(Document $import, \DOMNode $ref, $swap = 'replaceChild') {
    return $ref -> parentNode -> {$swap}( $this -> document -> importNode($import->documentElement, true), $ref );    
  }
  
  
}

/*************            ***************************************************************************************/
abstract class Controller {
  abstract public function authenticate(Request $request);
  abstract public function GETindex();
  
  public function GETlogin() {
    
    # code...
    
  }
  
  public function POSTlogin() {
    
    # code...
    
  }
  
  public function GETlogout() {
    
    # code...
    
  }
  
  // pondering still..
  public function compose(self $controller, $action) {
    
    // before/after stuff, runs like $instance->compose($instance, $action)
    
  }
}