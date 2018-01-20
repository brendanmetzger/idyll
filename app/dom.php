<?php namespace App; libxml_use_internal_errors(true);

/****          ************************************************************************ DOCUMENT */
class Document extends \DOMDocument {
  
  const   XMLDEC = LIBXML_COMPACT|LIBXML_NOBLANKS|LIBXML_NOENT|LIBXML_NOXMLDECL;
  private $xpath = null, $filepath = null,
          $opts  = [ 'preserveWhiteSpace' => false, 'formatOutput'    => true , 'encoding' => 'UTF-8', 
                     'resolveExternals'   => true , 'validateOnParse' => false ];
  
  function __construct($input, $opts = []) {
    parent::__construct('1.0', 'UTF-8');
    
    foreach (array_replace($this->opts, $opts) as $prop => $val) $this->{$prop} = $val;
    foreach (['Element','Text','Attr'] as $c ) $this->registerNodeClass("\\DOM{$c}", "\\App\\{$c}");

    if ($input instanceof Element) {
      $this->loadXML($input->ownerDocument->saveXML($input), self::XMLDEC);
    } else {
      $this->filepath = $input;
      $this->load($input, self::XMLDEC);
    }
  }

  public function save($path = null) {
    return file_put_contents($path ?? $this->filepath, $this->saveXML(), LOCK_EX);
  }

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
      // TODO deal with creating an attribute as necessary  `$this->setAttribute($offset, '')`
      return $this->getAttribute(substr($offset, 1));
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
    return ($parent = $this->parentNode) ? $parent->removeChild($this) : null;
  }
}