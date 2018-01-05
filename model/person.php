<?php namespace Models;

/**
 * Person
 */
class Person extends \App\Model
{
  
  public function authenticate($token)
  {
    if ($token === $this->context['@access']) {
      return $this;
    }
    throw new \InvalidArgumentException("This token is no longer active", 3);
  }

}
