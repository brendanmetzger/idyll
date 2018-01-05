<?php
namespace app;

class Document extends \DOMDocument
{
  const LOAD_OPTS = LIBXML_COMPACT|LIBXML_NOBLANKS|LIBXML_NOXMLDECL|LIBXML_NOENT;
  
  private $xpath = null,
          $opts  = [
            'encoding'           => 'UTF-8',
            'preserveWhiteSpace' => false,
            'validateOnParse'    => false,
            'formatOutput'       => true,
            'resolveExternals'   => true,
          ];
  
  function __construct($path = null, $opts = [])
  {
    libxml_use_internal_errors(true);
    parent::__construct('1.0', 'UTF-8');

    foreach (array_replace($this->opts, $opts) as $p => $v) $this->{$p} = $v;

    $this->registerNodeClass('\DOMElement', '\app\Element');
    $this->registerNodeClass('\DOMComment', '\app\Comment');
    $this->registerNodeClass('\DOMText', '\app\Text');
    $this->registerNodeClass('\DOMAttr', '\app\Attr');
    
    if ($path) {
      $this->load($path, self::LOAD_OPTS);
    }
  }

  public function save($path = null)
  {
    return file_put_contents($path ?? $this->filepath, $this->saveXML(), LOCK_EX);
  }

  public function find(string $path, \DOMElement $context = null): \DOMNodeList
  {
    return ($this->xpath ?: ($this->xpath = new \DOMXpath($this)))->query($path, $context);
  }

  public function errors($out = false)
  {
    $errors = libxml_get_errors();
    libxml_clear_errors();
    return $out ? print_r($errors, true) : $errors;
  }
}


/**
* Node
*/
class Node extends \DOMNode
{
}

/**
* Text
*/
class Text extends \DOMText
{
  
  public function __invoke(?string $string): self
  {
    $this->nodeValue = strip_tags($string);
    return $this;
  }
  
  static public function hasPrefix(string $prefix)
  {
    $prefix = trim($prefix);
    return function($text) {
      return substr((string) $text, strlen($prefix)) == $prefix;
    };
  }
}

/**
* Attr
*/
class Attr extends \DOMAttr
{
  public function __invoke(string $value)
  {
    $this->value = $value;
  }
}

/**
* Comment
*/
class Comment extends \DOMComment
{

}

/**
* DOM Element Extension
*/
class Element extends \DOMElement implements \ArrayAccess
{
  public function insert(\DOMNode $parent)
  {
    return $parent->appendChild($this);
  }

  public function select(string $tag, int $offset = 0): self
  {
    $nodes = $this->selectAll($tag);
    return $nodes->length <= $offset ? $this->appendChild(new self($tag)) : $nodes[$offset]; 
  }
  
  public function selectAll(string $path)
  {
    return $this->ownerDocument->find($path, $this);
  }
  
  public function merge($data)
  {
    // TODO figure out merge strategy
  }

  public function offsetExists($offset)
  {
    return $this->selectAll($offset)->length > 0;
  }

  public function offsetGet($offset)
  {
    if ($offset[0] === '@') {
      return $this->getAttribute(substr($offset, 1)) ?: $this->setAttribute($offset, '');
    } else {
      // TODO 
      //  [ ] create recursive function to deal with paths insead of tags, ie. ->select('a/b[@c]') if !exist must create/append <a><b c=""/></a>
      //  [ ] this should be responsible for making an empty node if necessary, possibly with a created="(now)" updated="0" attributes
      return $this->select($offset);
    }
  }

  public function offSetSet($offset, $value)
  {
    return $this[$offset]($value);
  }

  public function offsetUnset($offset)
  {
    throw new \Exception("TODO: implement deleting node values", 1);
    // will need to removeChild if it is an element, and removeAttribute if...
    return false;
  }

  public function getIndex()
  {
    $index = (int)preg_replace('/[^0-9]*([0-9]+)/', '$1', substr($this->getNodePath(), strlen($this->parentNode->getNodePath()), -1));
    return $index === 0 ? $index : $index - 1;
  }
  

  public function __toString()
  {
    return $this->nodeValue;
  }
  
  public function __invoke(string $string): self
  {
    $this->nodeValue = htmlentities($string, ENT_COMPAT|ENT_XML1, 'UTF-8', false);
    return $this;
  }
}