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


  public function routes($routes, $noRoutes = FALSE)
  {
    # $noRoutes にマッチしたURLは、指定URLへリダイレクト
    if ($noRoutes != FALSE) {
      $parent_url = hykwWPData::get_page_parent_permalink();

      if (isset($noRoutes[$parent_url])) {
        header(sprintf("Location: %s\n", $noRoutes[$parent_url]));
        exit;
      }
    }

    $controller = FALSE;
    if (is_home()) {
      $controller = $routes['/'];
    } elseif (is_search()) {
      $controller = $routes['search'];
    } else {
      $controller = hykwWPData::get_in_page_parent_permalink();

      if (isset($routes[$controller]))
        $controller = $routes[$controller];
    }

    if ($controller == FALSE)
      return self::ROUTE_404;

    $file = sprintf('%s/%s/%s', self::DIR_CONTROLLER, $controller, self::BASE_FILE);
    if (locate_template($file, true) == '') {
      echo self::ROUTE_CONTROLLER_NOT_FOUND;
      exit;
    }

    return self::RET_OK;
  }

  private function _callTemplates($dir, $args, $onErrorExit = TRUE)
  {
    $file = array_shift($args);
    $funcName = array_shift($args);

    switch($dir) {
        case self::DIR_COMPONENT:
          $file = sprintf('%s/%s/%s.php', self::DIR_CONTROLLER, self::DIR_COMPONENT, $file);
          $error_not_found = self::ROUTE_CONTROLLER_NOT_FOUND;
          break;

        case self::DIR_VIEW:
          $file = sprintf('%s/%s/%s', self::DIR_VIEW, $file, self::BASE_FILE);
          $error_not_found = self::ROUTE_VIEW_NOT_FOUND;
          break;

        case self::DIR_HELPER:
          $file = sprintf('%s/%s/%s.php', self::DIR_VIEW, self::DIR_HELPER, $file);
          $error_not_found = self::ROUTE_HELPER_NOT_FOUND;
          break;

        case self::DIR_MODEL:
          $file = sprintf('%s/%s/%s', self::DIR_MODEL, $file, self::BASE_FILE);
          $error_not_found = self::ROUTE_MODEL_NOT_FOUND;
          break;

        case self::DIR_BEHAVIOR:
          $file = sprintf('%s/%s/%s.php', self::DIR_MODEL, self::DIR_BEHAVIOR, $file);
          $error_not_found = self::ROUTE_BEHAVIOR_NOT_FOUND;
          break;

        default:
          echo self::ROUTE_404;
          exit;
    }

    if (locate_template($file, true) == '') {
      echo $error_not_found;
      if ($onErrorExit)
        exit;
    }

    if ($funcName != FALSE)
      return call_user_func_array($funcName, $args);

    return self::RET_OK;
  }
  
  public function callComponent()
  {
    $args = func_get_args();
    return self::_callTemplates(self::DIR_COMPONENT, $args);
  }

  public function callView()
  {
    $args = func_get_args();
    return self::_callTemplates(self::DIR_VIEW, $args);
  }

  public function callHelper()
  {
    $args = func_get_args();
    return self::_callTemplates(self::DIR_HELPER, $args);
  }

  public function callModel()
  {
    $args = func_get_args();
    return self::_callTemplates(self::DIR_MODEL, $args);
  }

  public function callBehavior()
  {
    $args = func_get_args();
    return self::_callTemplates(self::DIR_BEHAVIOR, $args);
  }
}
