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
  const RET_OK = 'OK';
  const ROUTE_404 = '404';
  const ROUTE_CONTROLLER_NOT_FOUND = 'controller not found';
  const ROUTE_HELPER_NOT_FOUND = 'helper not found';

  private $dir;
  private $dir_controller;
  private $dir_model;
  private $dir_view;
  
  function __construct($dir, $dir_controller = '', $dir_model = '', $dir_view = '')
  {
    if ($dir_controller == '')
      $dir_controller = 'controller';
    if ($dir_model == '')
      $dir_model = 'model';
    if ($dir_view == '')
      $dir_view = 'view';

    $this->dir = $dir;
    $this->dir_controller = $dir_controller;
    $this->dir_model = $dir_model;
    $this->dir_view = $dir_view;
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

    $file = sprintf('%s%s', $this->dir_controller, $url);
    get_template_part($file);

    return self::RET_OK;
  }

  public function callHelper($helperName, $dir_helper = 'helper')
  {
    $file = sprintf('%s/%s/%s', $this->dir_view, $dir_helper, $helperName);
    get_template_part($file);
    return self::RET_OK;
  }

}
