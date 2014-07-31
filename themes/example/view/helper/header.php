<?php

$title = 'mvctest';

?>
<!DOCTYPE html>
<html lang="ja" dir="ltr">
<head>
<meta charset="UTF-8">
<title>{$title}</title>

<link rel="stylesheet" type="text/css" href="<?php echo hykwWPData::get_url_stylecss_parent('/files/css/dummy.css', '20140731');?>" media="all" />

<?
wp_head();

echo <<<EOL
</head>
<body>
header

EOL;


