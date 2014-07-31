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
  const ROUTE_COMPONENT_NOT_FOUND = 'component not found';

  const BASE_FILE = 'index.php';  ## e.g. controller/[URL]/index.php

  private $dir;
  const DIR_CONTROLLER = 'controller';
  const DIR_MODEL = 'model';
  const DIR_VIEW = 'view';
  const DIR_HELPER = 'helper';
  const DIR_BEHAVIOR = 'behavior';
  const DIR_STATIC_FILES = 'files';
  const DIR_COMPONENT = 'component';
 
  function __construct($dir)
  {
    $this->dir = $dir;
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

    $file = sprintf('%s%s/%s', self::DIR_CONTROLLER, $url, self::BASE_FILE);
    if (locate_template($file, true) == '') {
      echo self::ROUTE_CONTROLLER_NOT_FOUND;
      exit;
    }

    return self::RET_OK;
  }

  public function callHelper($helperName, $funcName = FALSE, $args = FALSE)
  {
    $file = sprintf('%s/%s/%s.php', self::DIR_VIEW, self::DIR_HELPER, $helperName);
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
    $file = sprintf('%s/%s/%s', self::DIR_VIEW, $viewName, self::BASE_FILE);
    if (locate_template($file, true) == '') {
      echo self::ROUTE_VIEW_NOT_FOUND;
      exit;
    }

    return call_user_func($funcName, $args);
  }

  public function callModel($modelName, $funcName, $args = FALSE)
  {
    $file = sprintf('%s/%s/%s', self::DIR_MODEL, $modelName, self::BASE_FILE);
    if (locate_template($file, true) == '') {
      echo self::ROUTE_MODEL_NOT_FOUND;
      exit;
    }

    return call_user_func($funcName, $args);
  }

  public function callBehavior($behaviorName, $funcName, $args = FALSE)
  {
    $file = sprintf('%s/%s/%s.php', self::DIR_MODEL, self::DIR_BEHAVIOR, $behaviorName);
    if (locate_template($file, true) == '') {
      echo self::ROUTE_BEHAVIOR_NOT_FOUND;
      exit;
    }

    return call_user_func($funcName, $args);
  }

  public function callComponent($componentName, $funcName, $args = FALSE)
  {
    $file = sprintf('%s/%s/%s.php', self::DIR_CONTROLLER, self::DIR_COMPONENT, $componentName);
    if (locate_template($file, true) == '') {
      echo self::ROUTE_COMPONENT_NOT_FOUND;
      exit;
    }

    return call_user_func($funcName, $args);
  }

}
