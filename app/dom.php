<?php namespace App; libxml_use_internal_errors(true);

/****          ************************************************************************ DOCUMENT */
class Document extends \DOMDocument {
  const   XMLDEC   = LIBXML_COMPACT|LIBXML_NOBLANKS|LIBXML_NOENT|LIBXML_NOXMLDECL;
  private $xpath   = null, $input = null,
          $options = [ 'preserveWhiteSpace' => false, 'formatOutput' => true ,
                       'resolveExternals'   => true , 'encoding'     => 'UTF-8',
                     ];
  
  function __construct($input, $opts = [], $method = 'load') { parent::__construct('1.0', 'UTF-8');
    
    $this->input = $input;
    
    foreach (array_replace($this->options, $opts) as $property => $value)
      $this->{$property} = $value;
    
    foreach (['Element','Text','Attr'] as $classname)
      $this->registerNodeClass("\\DOM{$classname}", "\\App\\{$classname}");
    
    if ($input instanceof Element) {
      $this->input = $input->ownerDocument->saveXML($input);
      $method = 'loadXML';
    }

    if (! $this->{$method}($this->input, self::XMLDEC)) {
      $view = View::Error('markup')->render(['errors' => $this->errors()]);
      $this->appendChild($this->importNode($view->documentElement, true));
    }
  }

  public function save($path = null) {
    return $this->validate() && file_put_contents($path ?: $this->input, $this->saveXML(), LOCK_EX);
  }

  public function query(string $path, \DOMElement $context = null): \DOMNodeList {
    return ($this->xpath ?: ($this->xpath = new \DOMXpath($this)))->query($path, $context);
  }
  
  public function claim(string $id): \DOMElement {
    return $this->getElementById($id);
  }

  public function errors(): Data {
    return (new Data(libxml_get_errors()))->map(function ($error) { return (array) $error; });
  }
  
  public function __toString() {
    return $this->saveXML();
  }  
}

/****           ********************************************************************** INVOCABLE */
trait invocable {
  public function __invoke(?string $input): self {
    $this->nodeValue = htmlentities($input, ENT_COMPAT|ENT_XML1, 'UTF-8', false);
    return $this;
  }
  
  public function __toString(): string {
    return $this->nodeValue;
  }
}

/****      ******************************************************************************** TEXT */
class Text extends \DOMText {
  use invocable;
}

/****      ******************************************************************************** ATTR */
class Attr extends \DOMAttr {
  use invocable;
  public function remove() {
    return ($elem = $this->ownerElement) ? $elem->removeAttribute($this->nodeName) : null;
  }
}

/****         *************************************************************************** ELEMENT */
class Element extends \DOMElement implements \ArrayAccess {
  use invocable;
  
  public function selectAll(string $path) {
    return new Data($this->ownerDocument->query($path, $this));
  }
  
  public function merge(array $data) {
    foreach ($data as $key => $value) {
      if (is_array($value)) {
        // this is a live document, so each find would result in the index being correct after the append
        foreach ($value as $idx => $v) {
          $nodes = $this->offsetGet($key, true, $idx);
          if ($nodes->count() > $idx) {
            // figure out a way to remove extra nodes - ie., deleting content
          }
          $nodes[$idx] = $v;
        }
      } else {
        $this[$key] = $value;
      }
    }
  }

  public function offsetExists($key) {
    return $this->selectAll($key)->count() > 0;
  }

  public function offsetGet($key, $create = false, $index = 0) {    
    if (($nodes = $this->selectAll($key)) && ($nodes->count() > $index))
      return $nodes;
    else if ($create)
      return $this->appendChild(($key[0] == '@') ? new Attr(substr($key, 1)) : new Element($key));
    else 
      throw new \UnexpectedValueException($key);
  }

  public function offsetSet($key, $value) {
    return $this->offsetGet($key, true)($value);
  }

  public function offsetUnset($key) {
    return $this[$key]->remove();
  }
  
  public function remove() {
    return ($parent = $this->parentNode) ? $parent->removeChild($this) : null;
  }
  
  public function __call($key, $args): \DOMNode {
    return $this->offsetSet($key, ...$args);
  }
}