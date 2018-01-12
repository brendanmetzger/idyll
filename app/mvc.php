<?php namespace App;

/*************       *************************************************************************************/
abstract class Model implements \ArrayAccess {
  abstract protected function fixture(array $data): array;
  protected $context;

  public function __construct($context, array $data = []) { 
    
    if ($context instanceof Element) {
      $this->context = $context;
    } else if (empty($data) && ! $this->context = Data::USE(static::SOURCE)->getElementById($context)){
      throw new \InvalidArgumentException("Unable to locate the requested resource ({$context}). (TODO, better exceptinon type, log this, inform it was logged)", 1);
    } else {
      // create from a fixture
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
  
  static public function New(string $classname, $params) {
    $Classname = "\\Model\\{$classname}";
    return new $Classname($params);
  }
  
  public function offsetExists($offset) {
    return ! is_null($this->context);
  }

  public function offsetGet($offset) {
    $method  = "get{$offset}";
    return method_exists($this, $method) ? $this->{$method}($this->context) : $this->context[$offset];
  }

  public function offSetSet($offset, $value) {
    return $this->context[$offset] = $value;
  }

  public function offsetUnset($offset) {
    unset($this->context[$offset]);
    return true;
  }
  
  public function __invoke(?array $keys = null): array
  {
    return [(string)$this, $this['@id']];
  }
  
  public function __toString() {
    return $this->context['@id'];
  }
}

/********       **********************************************************************************/
interface Agent {
  public function contact(string $subject, string $message);
  public function signature();
}

/****      ***************************************************************************************/
class View {
  private $parent, $document, $slugs = [], $templates = [];
  
  public function __construct($input, ?self $parent = null) {
    $this->document = new Document($input);
    $this->parent   = $parent;
  }
  
  public function render($data = [], bool $parse = true): Document {
    
    foreach ($this->getTemplates('insert') as [$path, $ref]) {
      $this->import((new Self($path, $this))->render($data, false), $ref);
    }
    
    foreach ($this->getTemplates('replace') as [$prop, $ref]) {
      if (isset($this->templates[$prop])) {
        $this->import((new Self($this->templates[$prop], $this))->render($data, false), $ref->nextSibling);
        $ref -> parentNode -> removeChild($ref);
      }
    } 
    
    foreach ($this->getTemplates('iterate') as [$key, $ref]) {
      $view = new Self( $ref -> parentNode -> removeChild( $ref -> nextSibling ), $this);
      foreach ($data[$key] ?? [] as $idx => $datum) {
        $view->cleanup($this->import($view->render($datum), $ref, 'insertBefore'), $idx+1);
      }
      $ref->parentNode->removeChild($ref);
    }
      
    if ($parse) {
      foreach ($this->getSlugs() as [$node, $scope]) { try {
        $node(Data::PAIR($scope, $data));
      } catch (\UnexpectedValueException $e) {
        $list = $this->cleanup($node->parentNode);
      }}
      
      if (! $this->parent instanceof self) {
        $this->cleanup($this->document->documentElement, 1);
      }
    }
    return $this->document;
  }
  
  public function set(string $key, $path): self {
    $this->templates[$key] = $path;
    return $this;
  }
  
  private function cleanup(\DOMNode $node, ?int $idx = null): void {
    static $remove = [];
    if ($idx) {
      while ($path = array_shift($remove)) {
        $list = $node->ownerDocument->find("..{$path}", $node);
        if ($list->length == $idx) $list[$idx-1]->remove();
      }
    } else {
      $remove[] = $node->getNodePath();
    }
  }
  
  private function getTemplates($key): iterable {
    $query = "./descendant::comment()[ starts-with(normalize-space(.), '{$key}')"
           . (($key == 'iterate') ? ']' : 'and not(./ancestor::*/preceding-sibling::comment()[iterate])]');

    return (new Data($this -> document -> find( $query )))->map( function ($stub) {
      return [preg_split('/\s+/', trim($stub->nodeValue))[1], $stub];
    });    
  }
  
  private function getSlugs(): iterable {
    return $this->slugs ?: ( function (&$out) {
      $query = "substring(.,1,1)='[' and contains(.,'\$') and substring(.,string-length(.),1)=']' and not(*)";
      foreach ( $this -> document -> find("//*[{$query}]|//*/@*[{$query}]") as $slug ) {        
        preg_match_all('/\$+[\@a-z\_\:0-9]+\b/i', $slug( substr($slug, 1,-1) ), $match, PREG_OFFSET_CAPTURE);
      
        foreach (array_reverse($match[0]) as [$k, $i]) {
          $___ = $slug -> firstChild -> splitText($i) -> splitText(strlen($k)) -> previousSibling;
          if (substr( $___( substr($___,1) ),0,1 ) != '$') $out[] = [$___, explode(':', $___)];
        }
      }
      return $out;

    })($this->slugs);
  }
  
  private function import(Document $import, \DOMNode $ref, $swap = 'replaceChild'): \DOMNode {
    return $ref -> parentNode -> {$swap}( $this -> document -> importNode($import->documentElement, true), $ref );    
  }
}

/*************            ***************************************************************************************/
abstract class Controller {
  use Registry;
  
  protected $request;
  protected $response;
  
  abstract public function GETLogin(?string $model = null, ?string $message = null, ?string $redirect = null);
  abstract public function POSTLogin(\App\Data $post, string $model, string $path);
  
  static final public function Make(Request $request, string $class, string $method): array {

    $class  = "\controller\\{$class}";
    $method = new \ReflectionMethod($class, $request->method . $method);
    
    if ($method->isProtected() && ! $request->authenticate($method)) {
      $method = new \ReflectionMethod($class, $request->method . 'login');
    }
    return [(new \ReflectionClass($class))->newInstance($request), $method];
  }
  
  final public function __construct($request) {
    $this->request  = $request;
    $this->response = new \App\Response($request);
    [$this->controller, $this->action] = $request->method->route;
  }
  
  protected function proxy(\ReflectionMethod $method, ...$params)
  {
    /*
    thinking it out. logins are special, and you only login if you need to, so it needs to function
    like a switch. no/invalid data should land you at this proxy method, which will
    A) render a login page unless the number of params does not match.
    */
  }
  
  final public function output($message): Response {
    // would be beneficial to render the data here if message instance of view;
    // should be sending the response after setting the body, filters, etc.
    // the response can be dealt with further or converted to a string.
    $this->response->setContent($message instanceof View ? $message->render($this->store) : $message);
    return $this->response;
  }
}