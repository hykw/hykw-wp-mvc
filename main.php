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
  const ROUTE_VIEW_NOT_FOUND = 'view not found';
  const ROUTE_MODEL_NOT_FOUND = 'model not found';
  const ROUTE_BEHAVIOR_NOT_FOUND = 'behavior not found';

  const BASE_FILE = 'index.php';  ## e.g. controller/[URL]/index.php

  private $dir;
  private $dir_controller, $dir_model, $dir_view, $dir_helper, $dir_behavior, $dir_files;
  
  function __construct($dir, $dir_args = FALSE)
  {
    $this->dir = $dir;
    $this->dir_controller = isset($dir_args['dir_controller']) ? $dir_args['dir_controller'] : 'controller';
    $this->dir_model = isset($dir_args['dir_model']) ? $dir_args['dir_model'] : 'model';
    $this->dir_view = isset($dir_args['dir_view']) ? $dir_args['dir_view'] : 'view';
    $this->dir_helper = isset($dir_args['dir_helper']) ? $dir_args['dir_helper'] : 'helper';
    $this->dir_behavior = isset($dir_args['dir_behavior']) ? $dir_args['dir_behavior'] : 'behavior';
    $this->dir_files = isset($dir_args['dir_files']) ? $dir_args['dir_files'] : 'files';
  }

  public function routes($routes)
  {
    if (is_home()) {
      $url = $routes['/'];
    } elseif (is_search()) {
      $url = $routes['search'];
    } else {
      $url = hykwWPData::get_in_page_parent_permalink();
    }

    if ($url == FALSE)
      return self::ROUTE_404;

    $file = sprintf('%s%s/%s', $this->dir_controller, $url, self::BASE_FILE);
    if (locate_template($file, true) == '') {
      echo self::ROUTE_CONTROLLER_NOT_FOUND;
      exit;
    }

    return self::RET_OK;
  }

  public function callHelper($helperName, $funcName = FALSE, $args = FALSE)
  {
    $file = sprintf('%s/%s/%s.php', $this->dir_view, $this->dir_helper, $helperName);
    if (locate_template($file, true) == '') {
      echo self::ROUTE_HELPER_NOT_FOUND;
      exit;
    }

    if ($funcName != FALSE)
      return call_user_func($funcName, $args);

    return self::RET_OK;
  }

  public function callView($viewName, $funcName, $args = FALSE)
  {
    $file = sprintf('%s/%s/%s', $this->dir_view, $viewName, self::BASE_FILE);
    if (locate_template($file, true) == '') {
      echo self::ROUTE_VIEW_NOT_FOUND;
      exit;
    }

    return call_user_func($funcName, $args);
  }

  public function callModel($modelName, $funcName, $args = FALSE)
  {
    $file = sprintf('%s/%s/%s', $this->dir_model, $modelName, self::BASE_FILE);
    if (locate_template($file, true) == '') {
      echo self::ROUTE_MODEL_NOT_FOUND;
      exit;
    }

    return call_user_func($funcName, $args);
  }

  public function callBehavior($behaviorName, $funcName, $args = FALSE)
  {
    $file = sprintf('%s/%s/%s.php', $this->dir_model, $this->dir_behavior, $behaviorName);
    if (locate_template($file, true) == '') {
      echo self::ROUTE_BEHAVIOR_NOT_FOUND;
      exit;
    }

    return call_user_func($funcName, $args);
  }

}
