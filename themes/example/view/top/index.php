<?php

function view($args)
{
	$ret = '';

	$imgPath = $args['imgPath'];
	$contents = $args['contents'];

	$ret .= <<<EOL
<h1>mvctest</h1>
<p>
This is a test: {$contents}
</p>
EOL;

	return $ret;
}
