<?php namespace model;

class Agenda extends \App\Model {
  protected function fixture(): array {
    return [
      '@when' => '', // DSNSLNREQ: represent a choice, in this case the options are before/after new \App\Options('before', 'after') or \App\Data::select('before', 'after');
      '@refs' => '', // DSNSLNREQ: represent a collection of other models by id \App\Data::Group(); Models are all converted to their id, so this should be easy to serialize.
    ];
  }
}