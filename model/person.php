<?php namespace Model;

/**
 * Person
 */
class Person extends \App\Model
{
  
  public function authenticate($id) {

    if ($token === $this->context['@access']) {
      return $this;
    }
    throw new \InvalidArgumentException("This token is no longer active", 3);
  }

}
