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
    return $this->ownerDocument->query($path, $this);
  }
  
  public function merge(array $data) {
    foreach ($data as $key => $value) {
      if (is_array($value)) {
        // something like... perhaps
        // $this[$key]->merge($value);
      } else {
        $this[$key] = $value;
      }
    }
  }

  public function offsetExists($offset) {
    return $this->selectAll($offset)->length > 0;
  }

  public function offsetGet($offset) {
    
    if ($offset[0] === '@' && $offset = substr($offset, 1)) {
      
      if (! $this->hasAttribute($offset)) {
        $this->setAttributeNode(new Attr($offset)) ;
      }
      return $this->getAttributeNode($offset);
    } else {
      $nodes = $this->selectAll($offset);
      return $nodes->length <= $offset ? $this->appendChild(new self($offset)) : $nodes[$offset];
    }
  }

  public function offsetSet($offset, $value) {
    return $this->offsetGet($offset)($value);
  }

  public function offsetUnset($offset) {
    return $this[$offset]->remove();
  }
  
  public function remove() {
    return ($parent = $this->parentNode) ? $parent->removeChild($this) : null;
  }
}