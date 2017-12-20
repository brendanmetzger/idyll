<?php
namespace app;
/**
* Comment
*/
class Text extends \DOMTExt
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