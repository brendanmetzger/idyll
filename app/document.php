<?php
namespace app;

/**
 * DOM Document Extension
 */

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

    foreach (array_merge($this->opts, $opts) as $p => $v) $this->{$p} = $v;

    $this->registerNodeClass('\DOMElement', '\app\Element');
    $this->registerNodeClass('\DOMComment', '\app\Comment');
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
