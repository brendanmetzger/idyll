<?php namespace Model;

/**
 * Person
 */
class Person extends \App\Model
{
  const SOURCE = '../data/model.xml';
  const PATH   = '/model/person/item';
  
  protected function fixture(array $data): array  {
    return array_merge_recursive([
      '@title'   => '',
      '@id'      => '', // generate id with some sort of invokable/tostring object. new Slug($this, '@title') would have a __toString() that could turn title into an id based on input, otherwise it could be random/numeric.
      '@access'  => '',
      '@created' => time(),
      '@updated' => time(),
      'log'      => [] // should grab the fixture from log. maybe this needs to be static but then knowing about the instance would be difficult
    ], $data);
  }
  
  public function __toString() {
    return $this->context['@access'];
  }
}
