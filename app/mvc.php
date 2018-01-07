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
    return Data::use(static::SOURCE, $path ?: static::PATH)->map(function($item) {
      // this should be a factory: if path is not standard, may be a different model
      return new static($item);
    });
  }
  
  public function authenticate($criteria) {
    if (! $item = Data::use(static::SOURCE)->getElementById($criteria)) {
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

[ ] should deal with fragments
[x] placeholders can be scoped/nested 
[ ] falsy data should just put in an empty value
[ ] absent data should delete components in view
   [ ] regression: absent data cannot be removed immediately from template, as it will effect renderability of going forward
   [ ] an array of removals containting getNodePath()'s could be used to target removals and looped/reset effciently
   [ ] this would have to be done in reverse, as document would change as removals are processed
[x] should swap placeholder values with real values
[ ] A path (constructor) that does not load or is broken should throw exception
[ ] Make properties private/protected

merge method
[ ] should not be responsible for inserting - do it in render. Or get rid of the method.

render Method
[ ] remove nodes that have been slated for demo
[ ] run before/after filters

getTemplates Method
[ ] Make an element of the document object
[ ] See if finding all comments and filtering is comparable...
    $comments->filter(\app\text::hasPrefix('iterate')) seems way nicer (where hasPrefix returns a partially applied function)
    - realize this doesn't deal with nested iterations.. :-/

getSlugs Method
[ ] ? make a method of the element object (as this is finding elements);
[ ] would like the first foreach to be a map, spare the declarations
*/

/****      *************************************************************************************/
class View {
  public $document, $slugs = [], $templates = [];

  function __construct($input) {
    $this->document = new Document($input);
  }
  
  public function merge(Document $import, \DOMNode $ref) {
    return $ref->parentNode->replaceChild($this->document->importNode($import->documentElement, true), $ref);    
  }
  
  public function render($data = [], $parse = true): Document {
    
    foreach ($this->getTemplates('insert') as [$path, $ref]) {
      $this->merge((new View($path))->render($data, false), $ref);
    }
    
    foreach ($this->getTemplates('replace') as [$prop, $ref]) {
      if (isset($this->templates[$prop])) {
        $this->merge((new View($this->templates[$prop]))->render($data, false), $ref->nextSibling);
        $ref->parentNode->removeChild($ref);
      }
    } 
    
    foreach ($this->getTemplates('iterate') as [$key, $ref]) {
      $view = new View($ref->parentNode->removeChild($ref->nextSibling));
      foreach ($data[$key] as $datum) {
        $this->merge($view->render($datum), $ref->parentNode->insertBefore($ref->cloneNode(), $ref->nextSibling));
      }
    }
      
    if ($parse) {
      foreach ($this->getSlugs() as [$node, $scope]) {
        $node(Data::PAIR($scope, $data));
      }
    }
    
    return $this->document;
  }
  
  public function __set(string $key, string $path) {
    $this->templates[$key] = $path;
  }
  
  public function getTemplates($key) {
    $query = "./descendant::comment()[ starts-with(normalize-space(.), '{$key}')"
           . (($key == 'iterate') ? ']' : 'and not(./ancestor::*/preceding-sibling::comment()[iterate])]');
    
    // TODO would like to make this a map to spare the templates variable
    $templates = [];
    foreach ($this->document->find($query) as $stub) {
      $templates[] = [preg_split('/\s+/', trim($stub->nodeValue))[1], $stub];
    }
    
    return $templates;
  }
  
  public function getSlugs(): array {
    return $this->slugs ?: ( function (&$out) {
      $query = "substring(.,1,1)='[' and contains(.,'\$') and substring(.,string-length(.),1)=']' and not(*)";
      
      foreach ( $this->document->find("//*[ {$query} ] | //*/@*[ {$query} ]") as $slug ) {        
        preg_match_all('/\$+[\@a-z\_\:0-9]+\b/i', $slug(substr($slug, 1,-1)), $match, PREG_OFFSET_CAPTURE);
      
        foreach (array_reverse($match[0]) as [$key, $idx]) {
          $__ = $slug -> firstChild -> splitText($idx) -> splitText(strlen($key)) -> previousSibling;
          if (substr( $__( substr($__,1) ),0,1 ) != '$') $out[] = [$__, explode(':', $__)];
        }
      }
      return $out;

    })($this->slugs);
  }
  
}

/*************            *************************************************************************************/
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