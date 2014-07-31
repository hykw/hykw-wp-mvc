<?php

global $gobjMVC;
$gobjMVC->callHelper('header');

$imgPath = $gobjMVC->callBehavior('urls', 'get_imgPath');


$contents = $gobjMVC->callModel('top', 'getContents', 'aaa');

$args = array(
    'imgPath' => $imgPath,
		'contents' => $contents,
);

echo $gobjMVC->callView('top', 'view', $args);

$gobjMVC->callHelper('footer');
