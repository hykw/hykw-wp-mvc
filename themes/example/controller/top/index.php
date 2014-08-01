<?php

global $gobjMVC;
echo $gobjMVC->callHelper('header', 'getHeader');

$imgPath = $gobjMVC->callBehavior('urls', 'get_imgPath');


$contents = $gobjMVC->callModel('top', 'getContents', 'aaa');

$args = array(
    'imgPath' => $imgPath,
		'contents' => $contents,
);

echo $gobjMVC->callView('top', 'view', $args);

echo $gobjMVC->callHelper('footer', 'getFooter');
