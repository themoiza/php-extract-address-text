#!/usr/bin/php -q
<?php

require_once 'Extract.php';

$ends = [
    'Avenida Master Clase,701, apt 402',
];

$PhpExtractAddress = new PhpExtractAddress;

foreach($ends as $string){

    $result = $PhpExtractAddress->extract($string);

    $join = [];
    foreach($result as $r){

        if($r !== ''){
            $join[] = $r;
        }
    }
    
    print $string.str_repeat('·', 50 - strlen($string)).implode(', ', $join).PHP_EOL;
}