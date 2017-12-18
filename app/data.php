<?php
namespace app;

/*
  TODO 
  [ ] may need some adapters so that all data is treated the same (nodelist, array, etc.) in terms of the map/reduce type things
*/
class Data {
  public function __construct($data)
  {
    // wrap whatever data that comes... must be iterable.
  }
  
  // Connect/load
  
  // match/pair/tree lookup
  static public function PAIR(array $tree, array $data)
  {
    while ($key = array_shift($tree)) {
       $data = $data[$key];
    }
    return $data;
  }
  
  // map
  
  // reduce
  
  // filter
  
  // limit
}