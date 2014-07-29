<?php
  /**
   * @package HYKW MVC Plugin
   * @version 0.1
   */
  /*
    Plugin Name: HYKW MVC Plugin
    Plugin URI: https://github.com/hykw/hykw-wp-mvc
    Description: MVC プラグイン
    Author: hitoshi-hayakawa
    Version: 0.1
  */

class hykwMVC
{
  const ROUTE_OK = 'OK';
  const ROUTE_404 = '404';
  const ROUTE_CONTROLLER_NOT_FOUND = 'controller not found';

  private $parent_dir;
  private $child_dir;
  private $dir_controller;
  private $dir_model;
  private $dir_view;
  
  /*
    親テーマの場合、$parent_dirに自分のディレクトリを、$child_dirはFALSE
    子テーマの場合、$parent_dirに親のディレクトリを、$child_dirに自分のディレクトリを指定する
*/
  function __construct($parent_dir, $child_dir = FALSE, $dir_controller = '', $dir_model = '', $dir_view = '')
  {
    if ($dir_controller == '')
      $dir_controller = 'controller';
    if ($dir_model == '')
      $dir_model = 'model';
    if ($dir_view == '')
      $dir_view = 'view';

    $this->parent_dir = $parent_dir;
    $this->child_dir = $child_dir;
    $this->dir_controller = $dir_controller;
    $this->dir_model = $dir_model;
    $this->dir_view = $dir_view;
  }

  # 子に指定ファイルが無かったら親を探す
  public function get_requireFile($file, $subdir)
  {
    if ($this->child_dir != FALSE) {
      # 子テーマ
      $controller = sprintf('%s/%s%s.php', $this->child_dir, $subdir, $file);
      if (file_exists($controller))
	return $controller;
    }

    # 親テーマ
    $controller = sprintf('%s/%s%s.php', $this->parent_dir, $subdir, $file);
    if (file_exists($controller))
      return $controller;

    return FALSE;
  }    

  public function routes($routes_top = '/top')
  {
    if (is_home()) {
      $url = $routes_top;
    } else {
      $url = hykwWPData::get_in_page_parent_permalink();
    }

    if ($url == FALSE)
      return self::ROUTE_404;

    $controller = $this->get_requireFile($url, $this->dir_controller);
    if ($controller == FALSE)
      return self::ROUTE_CONTROLLER_NOT_FOUND;

    require_once($controller);
    return self::ROUTE_OK;
  }

}
