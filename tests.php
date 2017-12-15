<?php

include "app/view.php";

// load document





$data = [
  [
    'id' => '9876AB',
    'text' => 'First Item',
    'date' => [
      'formatted' => '10/22/83',
      'day'       => 'Tuesday',
      'month'     => 'October',
    ],
    'name' => [
      'first' => 'Dean',
    ],
  ],
  [
    'id' => '774343',
    'text' => 'Second Item',
    'date' => [
      'formatted' => '01/18/83',
      'day'       => 'Monday',
      'month'     => 'January',
    ],
    'name' => [
      'first' => 'Bean',
    ],
  ]
];

function getData(array $tree, array $data)
{
  // throw an exception if no key
  while ($key = array_shift($tree)) {
     $data = $data[$key];
  }
  return $data;
}



$time = microtime(true);

 
$layout   = new \app\view('view/layout.html');
$template = new \app\view('view/about.html');
$template->getSlugs();


// simulate cycle of dada
foreach ($data as $datum) { 
  foreach ($template->slugs as $slug) {
    $slug['node']->nodeValue =  getData($slug['scope'], $datum);
  }
  $layout->import($template);
}

echo $layout->render();

echo "TIME IS: " . (microtime(true) - $time) . "\n\n";

print_r($layout->getStubs('insert')[0]->nodeValue);