<?php
namespace app;

/**
* DOM Element Extension
*/

class Element extends \DOMElement implements \ArrayAccess
{
  public function insert(\DOMNode $parent)
  {
    return $parent->appendChild($this);
  }

  /*
    TODO 
    [ ] create recursive function to deal with paths insead of tags
       (ex, ->select('a/b[@c]') that doesn't exist should create and append <a><b c=""/></a>, )
  */
  public function select(string $tag, int $offset = 0): self
  {
    $nodes = $this->selectAll($tag);
    return $nodes->length > $offset ? $nodes[$offset] : $this->appendChild(new self($tag)); 
  }
  
  public function selectAll(string $path)
  {
    return $this->ownerDocument->find($path, $this);
  }
  
  public function merge($data)
  {
    // TODO figure out merge strategy
  }

  /*
    TODO test/make legit
  */
  public function offsetExists($offset)
  {
    return true;
  }

  public function offsetGet($offset)
  {
    print_r($offset);
    if (substr($offset, 0,1) === '@') {
      return $this->getAttribute(substr($offset, 1));
    } else {
      return $this->selectAll($offset);
    }
  }

  public function offSetSet($offset, $value)
  {
    if (substr($offset, 0,1) === '@') {
      return $this->setAttribute(substr($offset, 1), $value);
    } else {
      return $this->select($offset)($value);
    }
  }

  public function offsetUnset($offset)
  {
    return null;
  }

  public function getIndex()
  {
    $index = (int)preg_replace('/[^0-9]*([0-9]+)/', '$1', substr($this->getNodePath(), strlen($this->parentNode->getNodePath()), -1));
    return $index === 0 ? $index : $index - 1;
  }

  /*
    TODO should be handled in data object
  */
  public function replaceArrayValues(array $matches)
  {
    foreach ($matches as $key => &$match) {
      $match = htmlentities(\bloc\registry::getNamespace($match, $this), ENT_COMPAT|ENT_XML1, 'UTF-8', false);
    }
    return $matches;
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


  public function write($logging = false)
  {
    $output = $this->ownerDocument->saveXML($this);
    return $logging ? '<pre>'.htmlentities($output).'</pre>' : $output;
  }
}
