<?php namespace Model;

class item extends \app\model {
  const SOURCE = '../data/item.xml';
  const PATH   = '/items/item';
  
  protected function fixture(array $data): array  {
    return $data;
  }
}