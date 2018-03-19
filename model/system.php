<?php namespace Model;
/****        **************************************************************************** PERSON */
class system extends \App\Model implements \App\Agent {
  const SRC  = '../data/docs.xml';
  const PATH = '/docs/framework/note';
  
  protected function fixture(): array  {
    return [
      '@title'    => '',
      '@id'       => new \App\Slug($this, '@title'),
      '@created'  => new \App\Clock,
      '@updated'  => new \App\Clock,
    ];
  }
  
  
}