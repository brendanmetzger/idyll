<?php namespace Model;

class Project extends \App\Model {
  const SRC = ['../data/model.xml', '/model/project/item'];
  
  protected function fixture(): array  {
    return [
      '@title'    => '',
      '@id'       => new \App\Slug($this, '@title'),
      '@access'   => '',
      '@created'  => new \App\Clock,
      '@updated'  => new \App\Clock,
      '@duration' => '',
      '@status'   => '', // represents a choice of (complete|underway|pending),
      'log'       => [],
      'agenda'    => [],
    ];
  }
  
}