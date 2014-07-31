<?php

$routes = array(
    '/' => '/top',
    'search' => '/search',
);

$noRoutes = array(
    '/archives' => '/',
);

$ret = $gobjMVC->routes($routes, $noRoutes);
if ($ret != hykwMVC::RET_OK) {
  echo $ret;
}

