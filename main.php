<?php
  /**
   * @package HYKW MVC Plugin
   * @version 1.1
   */
  /*
    Plugin Name: HYKW MVC Plugin
    Plugin URI: https://github.com/hykw/hykw-wp-mvc
    Description: MVC プラグイン
    Author: hitoshi-hayakawa
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
  const ROUTE_UTIL_NOT_FOUND = 'util not found';

  const BASE_FILE = 'index.php';  ## e.g. controller/[URL]/index.php

  const ROUTENAME_TOP = '/';
  const ROUTENAME_SEARCH = 'search';
  const ROUTENAME_PREVIEW = 'preview';

  private $dir;
  const DIR_CONTROLLER = 'controller';
  const DIR_MODEL = 'model';
  const DIR_VIEW = 'view';
  const DIR_HELPER = 'helper';
  const DIR_BEHAVIOR = 'behavior';
  const DIR_STATIC_FILES = 'files';
  const DIR_COMPONENT = 'component';
  const DIR_UTIL = 'util';
 
  function __construct($dir)
  {
    $this->dir = $dir;
  }


  public function routes($routes, $noRoutes = FALSE, $noContentsRoutes = FALSE)
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
      $controller = $routes[self::ROUTENAME_TOP];
    } elseif (is_search()) {
      $controller = $routes[self::ROUTENAME_SEARCH];
    } else {
      $controller = hykwWPData::get_in_page_parent_permalink();

      if (isset($routes[$controller]))
        $controller = $routes[$controller];
    }

    if ($controller === FALSE) {
      # コントローラが見つからない場合
      if ($noContentsRoutes != FALSE) {
        $url = $_SERVER['REQUEST_URI'];
        if (substr($url, -1) != '/')
          $url .= '/';

        foreach ($noContentsRoutes as $key => $value) {
          $url_routes = (substr($key, -1) != '/') ? $key.'/' : $key;

          if (strpos($url, $url_routes) === 0) {
            $controller = $value;

            # 固定ページとして持ってるわけじゃないので、WordPressを騙す
            global $wp_query;
            $wp_query->is_404=null;
            status_header(200);

            break;
          }
        }
      }
      
      if ($controller === FALSE)
        return self::ROUTE_404;
    }

    # preview対応
    if (is_preview()) {
        $postid = get_the_ID();
        $posttype = get_post_type($postid);

        # 投稿の場合
        if ($posttype == 'post') {
          if (isset($routes[self::ROUTENAME_PREVIEW]))
            $controller = $routes[self::ROUTENAME_PREVIEW];
        }
    }

    # load controller
    $file = sprintf('%s/%s/%s', self::DIR_CONTROLLER, $controller, self::BASE_FILE);
    if (locate_template($file, true) == '') {
      echo self::ROUTE_CONTROLLER_NOT_FOUND;
      exit;
    }

    return self::RET_OK;
  }

  private function _callTemplates($loadFile, $args, $error_not_found, $onErrorExit = TRUE)
  {
    $funcName = array_shift($args);

    if (locate_template($loadFile, true) == '') {
      echo sprintf("%s (%s)\n", $error_not_found, $loadFile);
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
    $file = sprintf('%s/%s/%s.php', self::DIR_CONTROLLER, self::DIR_COMPONENT, 
        array_shift($args));

    return self::_callTemplates($file, $args, self::ROUTE_CONTROLLER_NOT_FOUND);
  }

  public function callView()
  {
    $args = func_get_args();
    $file = sprintf('%s/%s/%s', self::DIR_VIEW, 
        array_shift($args),
        self::BASE_FILE);

    return self::_callTemplates($file, $args, self::ROUTE_VIEW_NOT_FOUND);
  }

  public function callHelper()
  {
    $args = func_get_args();
    $file = sprintf('%s/%s/%s.php', self::DIR_VIEW, self::DIR_HELPER, 
        array_shift($args));

    return self::_callTemplates($file, $args, self::ROUTE_HELPER_NOT_FOUND);
  }

  public function callModel()
  {
    $args = func_get_args();
    $file = sprintf('%s/%s/%s', self::DIR_MODEL, 
        array_shift($args),
        self::BASE_FILE);

    return self::_callTemplates($file, $args, self::ROUTE_MODEL_NOT_FOUND);
  }

  public function callBehavior()
  {
    $args = func_get_args();
    $file = sprintf('%s/%s/%s.php', self::DIR_MODEL, self::DIR_BEHAVIOR, 
        array_shift($args));

    return self::_callTemplates($file, $args, self::ROUTE_BEHAVIOR_NOT_FOUND);
  }

  public function callUtil()
  {
    $args = func_get_args();
    $file = sprintf('%s/%s.php', self::DIR_UTIL, 
        array_shift($args));

    return self::_callTemplates($file, $args, self::ROUTE_UTIL_NOT_FOUND);
  }

}
