<?php namespace Model;

class Project extends \App\Model {
  const SRC = '../data/model.xml';
  const PATH = '/model/project/item';
  
  protected function fixture(): array  {
    return [
      '@title'    => '',
      '@id'       => new \App\Slug($this, '@title'),
      '@created'  => new \App\Clock,
      '@updated'  => new \App\Clock,
      '@duration' => 8,
      '@status'   => '', // represents a choice of (complete|underway|pending),
      '@schedule' => new \App\Clock,
      '@exec'     => '', // this is a list of ids, which would be people
      'log'       => [],
      'agenda'    => [],
    ];
  }
  
}