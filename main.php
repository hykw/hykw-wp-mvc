<?php
  /*
    Plugin Name: HYKW MVC Plugin
    Plugin URI: https://github.com/hykw/hykw-wp-mvc
    Description: MVC プラグイン
    Author: hitoshi-hayakawa
    Version: 2.0.0
   */

require_once ('class/base.php');

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



  /**
   * routes 
   * 
   * @param mixed $routes 
   * @param array $noRoutes (keyの末尾に/を付けないように注意: OK:'/archive', NG='/archive/')
   * @param mixed $noContentsRoutes 
   * @param boolean $isUnitTest TRUEなら、unittest 用のリターン値を返す
   * @return string
   */
  public function routes($routes, $noRoutes = FALSE, $noContentsRoutes = FALSE, $isUnitTest = FALSE)
  {

    $url = sub_hykwWPData_url::get_requestURL(FALSE);

    ########## $noRoutes にマッチしたら、リダイレクトして終了(UnitTest もここで値を返す）
    if ( ($noRoutes != FALSE) && (count($noRoutes) > 0) ) {
      $routeURL = self::_get_routeURL_longest($noRoutes, $url);
      if ($routeURL != '') {
        if ($isUnitTest) {
          return $routeURL;
        } else {
          header(sprintf("Location: %s\n", $redirectURL_parent));
          exit;
        }
      }
    }

    ########## noContentsRoutes にマッチ？
    if ( ($noContentsRoutes != FALSE) && (count($noContentsRoutes) > 0) ) {
      $ncr_controller = self::_get_routeURL_longest($noContentsRoutes, $url);

      if ($ncr_controller != '') {
        if ($isUnitTest)
          return $ncr_controller;

        # 固定ページとして持ってるわけじゃないので、WordPressを騙す
        global $wp_query;
        $wp_query->is_404=null;
        status_header(200);

        if (self::_load_controller($ncr_controller, $isUnitTest) == FALSE)
          return self::ROUTE_CONTROLLER_NOT_FOUND;

        return self::RET_OK;
      }
    }

    ########## preview対応
    if (is_preview()) {
      $postid = get_the_ID();
      $posttype = get_post_type($postid);

      # 投稿の場合
      if ($posttype == 'post') {
        if (isset($routes[self::ROUTENAME_PREVIEW])) {
          $controller = $routes[self::ROUTENAME_PREVIEW];
          if (self::_load_controller($controller, $isUnitTest) == FALSE)
            return self::ROUTE_CONTROLLER_NOT_FOUND;
        }
      }
    }

    ##### コントローラ名を取得する
    $controller = self::_get_controllerName($routes);
    # 完全一致するコントローラがある場合
    if ($controller != '') {
      # $routesの定義と完全一致したのに、実際のファイルが無かった場合
      if (self::_load_controller($controller, $isUnitTest) == FALSE) {
        return self::ROUTE_CONTROLLER_NOT_FOUND;
      }

      # ロードできたので、帰る
      if ($isUnitTest)
        return $controller;

      return self::RET_OK;
    }

    # 完全一致するコントローラが無いので、デフォルトの探索ルールに従い、コントローラを探す
    $controller_byDefaultRule = self::_get_routeURL_defaultRules($routes, $url);
    if ($controller_byDefaultRule == '')
      return self::ROUTE_CONTROLLER_NOT_FOUND;

    if (self::_load_controller($controller_byDefaultRule, $isUnitTest) == FALSE)
      return self::ROUTE_CONTROLLER_NOT_FOUND;

      # ロードできたので、帰る
    if ($isUnitTest)
      return $controller_byDefaultRule;

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



  ##### ここ以下の関数は外部から使うことは想定しない(public なのは UnitTest 用)
  /**
   * _get_routeURL_longest $routes/$noRoutes のうち、最も長いURLにマッチした時の値(= コントローラ名)を返す
   *
   * @param array $routes
   * @param string $url 現在のURL
   * @return string コントローラ名(未定義の場合は"")
   */
  public function _get_routeURL_longest($routesArray, $url)
  {
    if ($routesArray != FALSE) {
      ### 完全一致
      if (isset($routesArray[$url])) {
        return $routesArray[$url];
      }

      ### 親にマッチ（例：/archives を定義すれば、/archives/12345 にもマッチさせられる）
      $url_split = explode('/', $url);
      array_shift($url_split);

      /*
        段々短くしてチェック
        /archives/category
        /archives
       */
      while (count($url_split) > 0) {
        array_pop($url_split);
        $parent_url = '/' . implode('/', $url_split);

        # / とマッチ = マッチしないのと同じ
        if ($parent_url == '/')
          return '';

        if (isset($routesArray[$parent_url])) {
          return $routesArray[$parent_url];
        }
      }
    }

    return '';
  }


  /**
   * _get_controllerName 呼び出すコントローラの名前を取得
   *
   * @param array $routes
   * @return string コントローラ名（見つからない・取得失敗時は"")
   */
  public function _get_controllerName($routes)
  {
    $controller = "";
    $url = sub_hykwWPData_url::get_requestURL(FALSE);

    # カスタム投稿タイプの場合、is_home() が TRUE になるケースが
    # あるので、ベタな方法で比較するのが安全
    if ( ($url == '/') || (preg_match('/^\/page\//', $url)) ) {
      return $routes[self::ROUTENAME_TOP];
    } elseif (is_search()) {
      return $routes[self::ROUTENAME_SEARCH];
    } else {
      # 完全一致
      $controller = self::_get_routeURL_longest($routes, $url);

      return $controller;
    }

    return $controller;
  }


  /**
   * _load_controller コントローラを読み込む
   * 
   * @param string $controller コントローラ名("top" のように、先頭に "/top" が付かないことを想定)
   * @param boolean $isUnitTest TRUEなら読み込まずに、ファイルの存在チェックのみ行う
   * @return boolean TRUE=読み込み成功, FALSE=読み込み失敗（もしくは指定コントローラのファイルが無い）
   */
  public function _load_controller($controller, $isUnitTest)
  {
    $file = sprintf('%s/%s/%s', self::DIR_CONTROLLER, $controller, self::BASE_FILE);

    $isExist = self::_isExistTemplate_childAndParent($file);
    if ($isExist == -1)
        return FALSE;

    if ($isUnitTest) {
      return TRUE;
    }

    locate_template($file, true);
    return TRUE;
  }

  /**
   * _isExistTemplate_childAndParent 子テンプレート、親テンプレートの順にファイルを探す
   * 
   * @param string $file テンプレートファイル名
   * @return integer 1=子テンプレートのとこにあった、2=親テンプレートのとこにあった、-1 = どっちにも無い
   */
  public function _isExistTemplate_childAndParent($file)
  {
    $path_child = sprintf('%s/%s', get_stylesheet_directory(), $file);
    if (file_exists($path_child))
      return 1;

    $path_parent = sprintf('%s/%s', get_template_directory(), $file);
    if (file_exists($path_parent))
      return 2;

    return -1;
  }


  /**
   * _get_routeURL_defaultRules デフォルトの探索ルールに従い、コントローラを探す（longest match)
   * 
   * @param array $routes 
   * @param string $url 現在のURL
   * @return string コントローラ名(未定義の場合は"")
   */
  public function _get_routeURL_defaultRules($routes, $url)
  {
    # 完全一致？(※URLの先頭には / が付いているので注意）
      $file = sprintf('%s%s/%s', self::DIR_CONTROLLER, $url, self::BASE_FILE);
      $isExist = self::_isExistTemplate_childAndParent($file);
      if ($isExist > -1) {
        # コントローラ名には先頭/末尾の / は不要
        $url_split = explode('/', $url);
        if (count($url_split) == 1)
          return $url_split;

        if ($url_split[0] == '')
          array_shift($url_split);

        if (count($url_split) > 1)
          if ($url_split[1] == '')
          array_pop($url_split);

        $url_joined = implode('/', $url_split);
        return $url_joined;
      }

      ### 親にマッチ
    $url_split = explode('/', $url);
    array_shift($url_split);

      /*
        段々短くしてチェック
        /archives/category
        /archives
       */
    while (count($url_split) > 0) {
      array_pop($url_split);
      $parent_url = implode('/', $url_split);

      if ($parent_url == '')
        return '';

      # index.php があったら、そこにマッチ
      $file = sprintf('%s/%s/%s', self::DIR_CONTROLLER, $parent_url, self::BASE_FILE);
      $isExist = self::_isExistTemplate_childAndParent($file);

      if ($isExist > -1)
        return $parent_url;
    }

    return '';
  }


}
