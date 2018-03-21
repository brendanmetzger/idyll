<?php namespace Model;
/****        **************************************************************************** PERSON */
class Note extends \App\Model {
  const SRC  = '../data/docs.xml';
  const PATH = '/docs/notes/note';
  
  protected function fixture(): array  {
    return [
      '@title'   => '',
      '@id'      => '',
      '@created' => new \App\Clock,
      '@updated' => new \App\Clock,
      'CDATA'    => '',
    ];
  }
  
  
}