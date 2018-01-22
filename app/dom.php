<?php namespace App; libxml_use_internal_errors(true);

/****          ************************************************************************ DOCUMENT */
class Document extends \DOMDocument {
  
  private $xpath   = null, $input = null,
          $options = [ 'preserveWhiteSpace' => false, 'formatOutput' => true ,
                       'resolveExternals'   => true , 'encoding'     => 'UTF-8',
                     ];
  
  function __construct($input, $options = [], $method = 'load') {
    parent::__construct('1.0', 'UTF-8');
    
    $this->input = $input;
    foreach (array_replace($this->options, $options) as $property => $value)
      $this->{$property} = $value;
    
    foreach (['Element','Text','Attr'] as $classname)
      $this->registerNodeClass("\\DOM{$classname}", "\\App\\{$classname}");
    
    if ($input instanceof Element) {
      $this->input = $input->ownerDocument->saveXML($input);
      $method = 'loadXML';
    }

    if (! $this->{$method}($this->input, LIBXML_COMPACT|LIBXML_NOBLANKS|LIBXML_NOENT|LIBXML_NOXMLDECL)) {
      $errors = $this->errors()->map(function ($error) { return (array) $error; });
      $this->appendChild($this->importNode(View::Error('markup')->render(['errors' => $errors])->documentElement, true));
    }
  }

  public function save($path = null) {
    return file_put_contents($path ?: $this->input, $this->saveXML(), LOCK_EX);
  }

  public function query(string $path, \DOMElement $context = null): \DOMNodeList {
    return ($this->xpath ?: ($this->xpath = new \DOMXpath($this)))->query($path, $context);
  }
  
  public function claim(string $id): \DOMElement {
    return $this->getElementById($id);
  }

  public function errors(): Data {
    return new Data(libxml_get_errors());
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
  
  public function select(string $tag, int $offset = 0): self {
    $nodes = $this->selectAll($tag);
    return $nodes->length <= $offset ? $this->appendChild(new self($tag)) : $nodes[$offset]; 
  }
  
  public function selectAll(string $path) {
    return $this->ownerDocument->query($path, $this);
  }
  
  public function merge(array $data) {
    foreach ($data as $key => $value) {
      if (!is_array($value)) {
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
      // TODO create recursive function to deal with paths insead of tags, ie. ->select('a/b[@c]') if !exist must create/append <a><b c=""/></a>
      // TODO (?) this should be responsible for making an empty node if necessary , possibly with a created="(now)" updated="0" attributes
      return $this->select($offset);
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