<?php

namespace models;

/**
 * Person
 */
class Person extends \app\model
{
  
  public function authenticate($token)
  {
    if ($token === $this->context['@access']) {
      return $this;
    }
    throw new \InvalidArgumentException("This token is no longer active", 3);
  }

}
