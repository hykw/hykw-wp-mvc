<?php

class sub_hykwWPData_url extends sub_baseHykwWPData
{
  /**
   * get_requestURL URLのパス部分を返す
   * 
   * 例）
   * <pre>
   *   $uri = self::get_requestURL();        /archives?code=1234
   *   $uri = self::get_requestURL(TRUE);    /archives?code=1234
   *   $uri = self::get_requestURL(FALSE);   /archives
   * </pre>
   * 
   * @param bool $isIncludesQueryString TRUE:QueryString付き, FALSE: QueryString無し
   * @return string URL
   */
  public static function get_requestURL($isIncludesQueryString = FALSE)
  {
    $uri = $_SERVER['REQUEST_URI'];
    if ($isIncludesQueryString)
      return $uri;

    $work = explode('?', $uri);
    return $work[0];
  }

}

