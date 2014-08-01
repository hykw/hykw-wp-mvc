<?php

function getHeader()
{
  $title = 'mvctest';
  $css = hykwWPData::get_url_stylecss_parent('/files/css/dummy.css', '20140731');

  $ret = '';

  $ret .= <<<EOL
<!DOCTYPE html>
<html lang="ja" dir="ltr">
<head>
<meta charset="UTF-8">
<title>{$title}</title>
<link rel="stylesheet" type="text/css" href="{$css}" media="all" />


EOL;

  $ret .= hykwWPData::get_wp_head();

  $ret .= <<<EOL

</head>
<body>
header

EOL;

  return $ret;
}
