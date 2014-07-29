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

    $this->dir_controller = sprintf('%s/%s', $dir, $dir_controller);
    $this->dir_model = sprintf('%s/%s', $dir, $dir_model);
    $this->dir_view = sprintf('%s/%s', $dir, $dir_view);
  }

  public function routes($routes_top = '/top')
  {
    if (is_home()) {
      $url = $routes_top;
    } else {
      $url = hykwWPData::get_in_page_parent_permalink();
    }

    if ($url == FALSE)
      return hykwMVC::ROUTE_404;

    $dir_controller = sprintf('%s%s.php', $this->dir_controller, $url);
    if (!file_exists($dir_controller))
      return hykwMVC::ROUTE_CONTROLLER_NOT_FOUND;

    require_once($dir_controller);
    return hykwMVC::ROUTE_OK;
  }
}
