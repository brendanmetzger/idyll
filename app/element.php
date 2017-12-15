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


  public function getFirst($nodeName, $offset = 0, $append = true)
  {
    $result = $this->getElementsByTagName($nodeName);
    if ($offset >= 0 && $result->length > $offset) {
      return $result->item($offset);
    } else {
      $elem = new self($nodeName, null);
      if ($append) {
        $this->appendChild($elem);
      }
      return $elem;
    }
  }

  public function getElement($nodeName, $offset = 0, $append = true)
  {
    return $this->getFirst($nodeName, $offset, $append);
  }

  public function offsetExists($offset)
  {
    return true;
  }

  public function offsetGet($offset)
  {
    if (substr($offset, 0,1) === '@') {
      return $this->getAttribute(substr($offset, 1));
    } else {
      return new Iterator($this->getElementsByTagName($offset));
    }
  }

  public function offSetSet($offset, $value)
  {
    if (substr($offset, 0,1) === '@') {
      return $this->setAttribute(substr($offset, 1), $value);
    } else {
      return $this->getFirst($offset)->setNodeValue($value);
    }
  }

  public function setNodeValue($string)
  {
    if (empty($string)) return;
    $this->nodeValue = htmlentities($string, ENT_COMPAT|ENT_XML1, 'UTF-8', false);
    return $this;
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

  public function find($expression)
  {
    return $this->ownerDocument->find($expression, $this);
  }

  public function __toString()
  {
    return $this->nodeValue;
  }


  public function write($logging = false)
  {
    $output = $this->ownerDocument->saveXML($this);
    return $logging ? '<pre>'.htmlentities($output).'</pre>' : $output;
  }
}
