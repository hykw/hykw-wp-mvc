<?php


/**
 * 外部依存性を無くすため、hykw-wpdata(https://github.com/hykw/hykw-wpdata)のサブセット版を用意
 */


$files = glob(__DIR__.'/*.php');
foreach ($files as $file) {
  require_once($file);
}

class sub_baseHykwWPData
{

  /**
   * _pruneQueryString URLから ?code=1234 みたいなパラメータを除去
   * 
   * @param string $url URL
   * @return string 変換後のURL
   */
  /*
  protected static function _pruneQueryString($url)
  {
    $url = preg_replace('/^([^?].*)\?.*$/', '$1', $url);
    return $url;
  }
   */

}

