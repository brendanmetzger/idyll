<?php namespace model;

class Log extends \App\Model {
  protected function fixture(): array {
    return [
      '@created' => new \App\Clock,
      '@updated' => new \App\Clock,
      '@file'    => '',
      'CDATA'    => '',
    ];
  }
}