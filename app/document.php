<?php
namespace app;

/**
 * DOM Document Extension
 */

class Document extends \DOMDocument
{
  
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

    foreach (array_merge($this->opts, $opts) as $prop => $value) $this->{$prop} = $value;

    $this->registerNodeClass('\DOMElement', '\app\Element');
    $this->registerNodeClass('\DOMComment', '\app\Comment');
  }

  public function save($path = null)
  {
    return file_put_contents($path ?? $this->filepath, $this->saveXML(), LOCK_EX);
  }
  

  public function find(string $expression, \DOMElement $context = null)
  {
    if ($this->xpath === null) {
      $this->xpath = new \DOMXpath($this);
    }
    
    return $this->xpath->query($expression, $context ?: $this->documentElement);
  }

  public function errors($out = false)
  {
    $errors = libxml_get_errors();
    libxml_clear_errors();
    return $out ? print_r($errors, true) : $errors;
  }
}
