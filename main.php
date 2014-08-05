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


  private function _callTemplates($calledFile, $error_not_found, $funcName, $args, $onErrorExit = TRUE)
  {
    if (locate_template($calledFile, true) == '') {
      echo $error_not_found;
      if ($onErrorExit)
        exit;
    }

    if ($funcName != FALSE)
      return call_user_func($funcName, $args);

    return self::RET_OK;
  }

  public function callComponent($componentName, $funcName, $args = FALSE)
  {
    $file = sprintf('%s/%s/%s.php', self::DIR_CONTROLLER, self::DIR_COMPONENT, $componentName);
    return self::_callTemplates($file, self::ROUTE_COMPONENT_NOT_FOUND, $funcName, $args);
  }

  public function callView($viewName, $funcName, $args = FALSE)
  {
    $file = sprintf('%s/%s/%s', self::DIR_VIEW, $viewName, self::BASE_FILE);
    return self::_callTemplates($file, self::ROUTE_VIEW_NOT_FOUND, $funcName, $args);
  }

  public function callHelper($helperName, $funcName = FALSE, $args = FALSE)
  {
    $file = sprintf('%s/%s/%s.php', self::DIR_VIEW, self::DIR_HELPER, $helperName);
    return self::_callTemplates($file, self::ROUTE_HELPER_NOT_FOUND, $funcName, $args);
  }

  public function callModel($modelName, $funcName, $args = FALSE)
  {
    $file = sprintf('%s/%s/%s', self::DIR_MODEL, $modelName, self::BASE_FILE);
    return self::_callTemplates($file, self::ROUTE_MODEL_NOT_FOUND, $funcName, $args);
  }

  public function callBehavior($behaviorName, $funcName, $args = FALSE)
  {
    $file = sprintf('%s/%s/%s.php', self::DIR_MODEL, self::DIR_BEHAVIOR, $behaviorName);
    return self::_callTemplates($file, self::ROUTE_BEHAVIOR_NOT_FOUND, $funcName, $args);
  }


}
