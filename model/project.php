<?php namespace Model;

class Project extends \App\Model {
  const SRC = ['../data/model.xml', '/model/project/item'];
  
  protected function fixture(): array  {
    return [
      '@title'   => '',
      '@id'      => '', // generate id with some sort of invokable/tostring object. new Slug($this, '@title') would have a __toString() that could turn title into an id based on input, otherwise it could be random/numeric.
      '@access'  => '',
      '@created' => time(),
      '@updated' => time(),
      'log'      => [] // should grab the fixture from log. maybe this needs to be static but then knowing about the instance would be difficult
    ];
  }
  
}