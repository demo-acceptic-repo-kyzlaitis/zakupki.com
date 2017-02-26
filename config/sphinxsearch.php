<?php

return [
    'host'    => env('SPHINX_HOST', '127.0.0.1'),
    'port'    => 9312,
    'timeout' => 30,
    'indexes' => array(
        'tenders' => array('table' => 'tenders', 'column' => 'id'),
        'tenders_dev' => array('table' => 'tenders', 'column' => 'id'),
    ),
    'mysql_server' => array(
        'host' => env('SPHINX_HOST', '127.0.0.1'),
        'port' => 9306
    )
];
