<?php

function hasPrefix(string $prefix)
{
  $prefix = trim($prefix);
  return function($text) {
    return substr((string) $text, strlen($prefix)) == $prefix;
  };
}

?>