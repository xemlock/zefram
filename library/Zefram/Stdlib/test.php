<?php

require 'PeekableIterator.php';
require 'PeekableArrayIterator.php';

$arr = array(1, 2, 'three' => 3, 'false' => false, 'A', 'B', 'obj' => new stdClass);
$it = new Zefram_Stdlib_PeekableArrayIterator($arr);

foreach ($it as $value) {
    echo str_pad($it->key(), 5), ': ', json_encode($value), ', hasNext: ', json_encode($it->hasNext()), ', next: ', json_encode($it->peek()), "\n";
}

