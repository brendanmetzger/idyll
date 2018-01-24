<?php namespace App;

/********       ************************************************************************* AGENT */
interface Agent {
  public function contact(string $subject, string $message);
  public function sign(Token $token);
}

/*************       ********************************************************************* MODEL */
abstract class Model implements \ArrayAccess {
  abstract protected function fixture(): array;
  
  protected $context;

  public function __construct($input) { 
    $this->context = ($input instanceof Element) ? $input : Data::use(static::SRC)->claim($input);
  }
  
  static public function List(string $path): \App\Data {
    return Data::USE(static::SRC, $path)->map(function($item) {
      return new static($item);
    });
  }
  
  static public function Create($type, array $data = []) {
    $model  = Factory::Model($type);
    $src    = $model->getConstant('SRC');
    [$path, $tag] = preg_split('/\/(?!.*\/)/', $model->getConstant('PATH'));
    
    return $model->newInstance(Data::Use($src)->query($path)[0]->appendChild(new Element($tag)));
  }
  
  public function load(array $data = []) {
    $input = array_replace_recursive($this->fixture(), array_filter($data));

    $this->context->merge($input);
      // echo htmlentities($this->context->ownerDocument->saveXML($this->context));
    return $input;
  }
  
  public function save() {
    $source = Data::Use(static::SRC);
    return $source->save() ?: $source->errors();
  }
  
  public function offsetExists($offset) {
    return ! is_null($this->context);
  }

  public function offsetGet($offset) {
    $get  = "get{$offset}";
    return method_exists($this, $get) ? $this->{$get}($this->context) : $this->context[$offset];
  }

  public function offSetSet($offset, $value) {
    return $this->context[$offset] = $value;
  }

  public function offsetUnset($offset) {
    unset($this->context[$offset]);
    return true;
  }
  
  final public function __toString() {
    return $this->context['@id'];
  }
}

/****      ******************************************************************************** VIEW */
class View {
  
  private $parent, $document, $slugs = [], $templates = [];
  
  static public function __callStatic(string $dir, $path): self {
    return new Self(sprintf('%s/%s.html', $dir, implode('/', $path)));
  }

  public function __construct($input, ?self $parent = null) {
    $this->document = new Document($input);
    $this->parent   = $parent;
  }
  
  public function render($data = [], $parse = true): Document {
    
    foreach ($this->getTemplates('insert') as [$path, $ref]) {
      $this->import((new Self($path, $this))->render($data, false), $ref);
    }
    
    foreach ($this->getTemplates('replace') as [$prop, $ref]) {
      if (isset($this->templates[$prop]) && $template = $this->templates[$prop]) {
        if (! $template instanceof Document) {
          $template = (new Self($this->templates[$prop], $this))->render($data, false);
        }
        $this->import($template, $ref->nextSibling);
        $ref->parentNode->removeChild($ref);
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
      while ($path = array_pop($remove)) {
        $list = $node->ownerDocument->query("..{$path}", $node);
        if ($list->length == $idx) $list[$idx-1]->remove();
      }
    } else $remove[] = $node->getNodePath();
  }
  
  private function getTemplates($key): iterable {
    $query = "./descendant::comment()[ starts-with(normalize-space(.), '{$key}')"
           . (($key == 'iterate') ? ']' : 'and not(./ancestor::*/preceding-sibling::comment()[iterate])]');

    return (new Data($this->document->query( $query )))->map( function ($stub) {
      return [preg_split('/\s+/', trim($stub->nodeValue))[1], $stub];
    });    
  }
  
  private function getSlugs(): iterable {
    return $this->slugs ?: ( function (&$out) {
      $query = "substring(.,1,1)='[' and contains(.,'\$') and substring(.,string-length(.),1)=']' and not(*)";
      foreach ( $this->document->query("//*[{$query}]|//*/@*[{$query}]") as $slug ) {        
        preg_match_all('/\$+[\@a-z_:|0-9]+\b/i', $slug( substr($slug, 1,-1) ), $match, PREG_OFFSET_CAPTURE);
      
        foreach (array_reverse($match[0]) as [$k, $i]) {
          $___ = $slug->firstChild->splitText($i)->splitText(strlen($k))->previousSibling;
          if (substr( $___( substr($___,1) ),0,1 ) != '$') $out[] = [$___, explode(':', str_replace('|', '/', $___))];
        }
      }
      return $out;

    })($this->slugs);
  }
  
  private function import(Document $import, \DOMNode $ref, $swap = 'replaceChild'): \DOMNode {
    return $ref->parentNode->{$swap}( $this->document->importNode($import->documentElement, true), $ref );    
  }
}

/*************            ************************************************************************** CONTROLLER */
abstract class Controller {
  use Registry;
  
  protected $request, $response;
  
  abstract public function GETLogin(?string $model = null, ?string $message = null, ?string $redirect = null);
  abstract public function POSTLogin(\App\Data $post, string $model, string $path);
  
  
  final public function __construct($request, $response) {
    $this->request  = $request;
    $this->response = $response;
    [$this->controller, $this->action] = $request->method->route;
  }
  
  final public function output($msg): self {
    $this->response->setContent($msg instanceof View ? $msg->render($this->data) : $msg);
    return $this;
  }
}