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

[ ] should accept a valid template
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

render Method
[ ] controlled by response object
[ ] remove nodes that have been slated for demo
[ ] run before/after filters

getStubs Method
[ ] Make an element of the document object
[ ] See if finding all comments and filtering is comparable...
    $comments->filter(\app\text::hasPrefix('iterate')) seems way nicer (where hasPrefix returns a partially applied function)
    - realize this doesn't deal with nested iterations.. :-/

getSlugs Method
[ ] ? make a method of the element object (as this is finding elements);
*/

/****      *************************************************************************************/
class View {
   
  public $document, $slugs = [];

  function __construct(string $path) {
    $this->document = new \app\document($path);
  }
  
  public function merge(self $view, \DOMNode $reference = null) {
    $view = $this->document->importNode($view->document->documentElement, true);
    if ($reference) {
      $reference->parentNode->insertBefore($view, $reference->nextSibling);
    } else {
      $this->document->documentElement->appendChild($view);
    }
    
  }
  
  public function render(): string {
    
    $this->getStubs('insert');
    
    return $this->document->saveXML();
  }
  
  public function getStubs($prefix) {
    $query = "./descendant::comment()[ starts-with(normalize-space(.), '%s') ]";

    if ( $prefix == 'iterate' ) {
      $query = substr($query, 0, -1) . 'and not(./ancestor::*/preceding-sibling::comment()[iterate]) ]';
    }
    
    foreach ($this->document->find(sprintf($query, $prefix)) as $stub) {
      [$method, $path] = preg_split('/\s+/', trim($stub->nodeValue));
      $this->{$method}($path, $stub);
    }
    return true;
  }
  
  public function getSlugs(): array {
    return $this->slugs ?: ( function (&$out) {
      $query = "substring(.,1,1)='[' and contains(.,'\$') and substring(.,string-length(.),1)=']' and not(*)";
      foreach ( $this->document->find("//*[ {$query} ] | //*/@*[ {$query} ]") as $slug ) {        
        preg_match_all('/\$+[\@a-z\_\:0-9]+\b/i', $slug(substr($slug, 1,-1)), $match, PREG_OFFSET_CAPTURE);
        foreach (array_reverse($match[0]) as [$key, $pos]) { // start from end b/c of numerical offsets
          $var = $slug->firstChild->splitText($pos)->splitText(strlen($key))->previousSibling;
          if (substr($var(substr($var, 1)), 0, 1) != '$')
            $out[] = ['node' => $var, 'scope' => explode(':', $var)];
        }
      }
      return $out;
      
    })($this->slugs);
  }
  
  private function insert(string $path, \DOMComment $reference) {
    // make new self, insert!
    $this->merge(new self($path), $reference);
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
}