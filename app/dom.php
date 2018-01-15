<?php namespace App;

libxml_use_internal_errors(true);

/****          ***********************************************************************************/
class Document extends \DOMDocument {
  
  private $xpath = null,
          $opts  = [ 'preserveWhiteSpace' => false, 'formatOutput'    => true , 'encoding' => 'UTF-8', 
                     'resolveExternals'   => true , 'validateOnParse' => false ];
  
  function __construct($X, $opts = []) {
    parent::__construct('1.0', 'UTF-8');
    
    foreach (array_replace($this->opts, $opts) as $prop => $val) $this->{$prop} = $val;
    foreach (['Element','Text','Attr'] as $c ) $this->registerNodeClass("\\DOM{$c}", "\\App\\{$c}");
    
    if (! $raw = ($X instanceof Element ? $X->ownerDocument->saveXML($X) : file_get_contents($X)))
      throw new \Error("{$xml} cannot be parsed", 1);
    
    $this->loadXML($raw, LIBXML_COMPACT|LIBXML_NOBLANKS|LIBXML_NOENT|LIBXML_NOXMLDECL);
  }

  public function save($path = null) {
    return file_put_contents($path ?? $this->filepath, $this->saveXML(), LOCK_EX);
  }

  // TODO find should return a data object, so it can be filtered/mapped, etc.
  public function find(string $path, \DOMElement $context = null): \DOMNodeList {
    return ($this->xpath ?: ($this->xpath = new \DOMXpath($this)))->query($path, $context);
  }

  public function errors() {
    return libxml_get_errors();
  }
  
  public function __toString() {
    return $this->saveXML();
  }
}

trait invocable {
  public function __invoke(?string $input): self {
    $this->nodeValue = htmlentities($input, ENT_COMPAT|ENT_XML1, 'UTF-8', false);
    return $this;
  }
  
  public function __toString(): string {
    return $this->nodeValue;
  }
}

/****      *************************************************************************************/
class Text extends \DOMText {
  use invocable;
}

/****      *************************************************************************************/
class Attr extends \DOMAttr {
  use invocable;
  public function remove() {
    if ($this->ownerElement) {
      return $this->ownerElement->removeAttribute($this->nodeName);
    }
  }
}

/****         *************************************************************************************/
class Element extends \DOMElement implements \ArrayAccess {
  use invocable;
  
  // TODO this will be obsolete if I can figure out how to automatically return the first element if no index is specified
  public function select(string $tag, int $offset = 0): self {
    $nodes = $this->selectAll($tag);
    return $nodes->length <= $offset ? $this->appendChild(new self($tag)) : $nodes[$offset]; 
  }
  
  public function selectAll(string $path) {
    return $this->ownerDocument->find($path, $this);
  }
  
  public function merge(array $data) {
    // TODO figure out merge strategy
  }

  public function offsetExists($offset) {
    return $this->selectAll($offset)->length > 0;
  }

  public function offsetGet($offset) {
    if ($offset[0] === '@') {
      $offset = substr($offset, 1);
      // TODO deal with creating an attribute as necessary  `$this->setAttribute($offset, '')`
      return $this->getAttribute($offset);
    } else {
      // TODO create recursive function to deal with paths insead of tags, ie. ->select('a/b[@c]') if !exist must create/append <a><b c=""/></a>
      // TODO (?) this should be responsible for making an empty node if necessary , possibly with a created="(now)" updated="0" attributes
      return $this->select($offset);
    }
  }

  public function offSetSet($offset, $value) {
    return $this[$offset]($value);
  }

  public function offsetUnset($offset) {
    return $this[$offset]->remove();
  }
  
  public function remove() {
    if ($this->parentNode) {
      return $this->parentNode->removeChild($this);
    }
  }

}