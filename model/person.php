<?php namespace Model;

/**
 * Person
 */
class Person extends \App\Model
{
  const SOURCE = '../data/model.xml';
  const PATH   = '/model/person/item';  
  
  public function authenticate($criteria) {

    if (! $context = \App\Data::USE(static::SOURCE)->getElementById($criteria)) {
      throw new \Exception("Unable to locate the requested resource ({$context}). (TODO, better exceptinon type)", 1);
    }
    
    return $context;
    
  }

}
