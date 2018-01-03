<?php
namespace app;

class model implements \ArrayAccess {
  
  protected $context;

  public function __construct($context, $data = null)
  {
    if ($context instanceof Element) {
      $this->context = $context;
    } else if ($data === null && ! $this->context = data::use(static::SOURCE)->getElementById($context)){
      throw new \InvalidArgumentException("The item specified does not exist.", 1);
    } else {
      // TODO determine how to create a new item
    }
    
    if ($data) {
      // Context will be an element, and the element will control the merging, not the model
      $this->context->merge($data);
    }
  }
  
  static public function LIST(?string $path = null): \app\data
  {
    // return a data object thaht contains a bunch of self's
    return data::use(static::SOURCE, $path ?: static::PATH)->map(function($item) {
      return new static($item);
    });
  }
  
  public function offsetExists($offset)
  {
    return ! is_null($this->context);
  }

  public function offsetGet($offset)
  {
    return $this->context[$offset];
  }

  public function offSetSet($offset, $value)
  {
    return $this->context[$value];
  }

  public function offsetUnset($offset)
  {
    unset($this->context[$offset]);
    return true;
  }
}