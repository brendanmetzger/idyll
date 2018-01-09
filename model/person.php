<?php namespace Model;

/**
 * Person
 */
class Person extends \App\Model
{
  const SOURCE = '../data/model.xml';
  const PATH   = '/model/person/item';  
  
  public function __toString() {
    return $this->context['@access'];
  }
}
