<?php

class UT_hykwMVC extends hykwEasyUT {
  private $routes;
  private $noRoutes;
  private $noContentsRoutes;

  public function setUp()
  {
    $routes = array(
      hykwMVC::ROUTENAME_TOP => 'top',
      hykwMVC::ROUTENAME_SEARCH => 'search',
      hykwMVC::ROUTENAME_PREVIEW => 'archives',

      '/parent' => 'page',
      '/sample-age-2' => 'page',

    );

    $noRoutes = array(
      #
    );

    $noContentsRoutes = array(
      #
    );

    $this->routes = $routes;
    $this->noRoutes = $noRoutes;
    $this->noContentsRoutes = $noContentsRoutes;
  }

  public function test_routes()
  {
    global $gobjMVC;

    ### 前処理でQueryStringが除去されているため、QueryString付きのテストは不要
    $this->assertEquals('archives', $gobjMVC->_get_routeURL_defaultRules($this->routes, '/archives/category/01'));
#    $this->assertEquals('archives', $gobjMVC->_get_routeURL_defaultRules($this->routes, '/archives/category/01?code=3'));
    $this->assertEquals('archives', $gobjMVC->_get_routeURL_defaultRules($this->routes, '/archives/category'));
    $this->assertEquals('archives', $gobjMVC->_get_routeURL_defaultRules($this->routes, '/archives/category/'));
#    $this->assertEquals('archives', $gobjMVC->_get_routeURL_defaultRules($this->routes, '/archives/category?code=3'));
#    $this->assertEquals('archives', $gobjMVC->_get_routeURL_defaultRules($this->routes, '/archives/category/?code=3'));
    $this->assertEquals('archives', $gobjMVC->_get_routeURL_defaultRules($this->routes, '/archives/4'));
#    $this->assertEquals('archives', $gobjMVC->_get_routeURL_defaultRules($this->routes, '/archives/4?code=3'));

    $this->assertEquals('archives', $gobjMVC->_get_routeURL_defaultRules($this->routes, '/archives/'));
    $this->assertEquals('archives', $gobjMVC->_get_routeURL_defaultRules($this->routes, '/archives'));
    # 
#    $this->assertEquals('archives', $gobjMVC->_get_routeURL_defaultRules($this->routes, '/archives?code=3'));
#    $this->assertEquals('archives', $gobjMVC->_get_routeURL_defaultRules($this->routes, '/archives/?code=3'));


    $arRoutes = array($this->routes, $this->noRoutes, $this->noContentsRoutes);

    self::_goto_assert($gobjMVC, '/', 'top', $arRoutes);
    self::_goto_assert($gobjMVC, '/parent', 'page', $arRoutes);

    self::_goto_assert($gobjMVC, '/archives/category/01', 'archives', $arRoutes);
    self::_goto_assert($gobjMVC, '/archives/category/01?code=3', 'archives', $arRoutes);
    self::_goto_assert($gobjMVC, '/archives/category', 'archives', $arRoutes);
    self::_goto_assert($gobjMVC, '/archives/category/', 'archives', $arRoutes);

    self::_goto_assert($gobjMVC, '/archives/', 'archives', $arRoutes);
    self::_goto_assert($gobjMVC, '/archives', 'archives', $arRoutes);
  }

  private function _goto_assert($objMVC, $url, $expect, $arRoutes)
  {
    $this->go_to($url);

    $routes = $arRoutes[0];
    $noRoutes = $arRoutes[1];
    $noContentsRoutes = $arRoutes[2];

    $ret = $objMVC->routes($routes, $noRoutes, $noContentsRoutes, TRUE);
    $this->assertEquals($expect, $ret);
  }

  public function test_preview()
  {
    global $gobjMVC;

    $url = '/parent?preview=true&preview_id=2119';
    $this->go_to($url);

    $ret = $gobjMVC->routes($this->routes, $this->noRoutes, $this->noContentsRoutes, TRUE);
    $this->assertEquals('page', $ret);
  }



  public function test_noRoutes()
  {
    global $gobjMVC;

    ### 完全一致
    $url = '/archives/category/01';
    $this->go_to($url);
    $noRoutes = array(
      '/archives/category/01' => '/about',
    );

    $ret = $gobjMVC->routes($this->routes, $noRoutes, $this->noContentsRoutes, TRUE);
    $this->assertEquals('/about', $ret);

    ### 親に一致
    $url = '/archives/category/01?code=3';
    $this->go_to($url);

    $noRoutes = array(
      '/archives' => '/parent',
    );

    $ret = $gobjMVC->routes($this->routes, $noRoutes, $this->noContentsRoutes, TRUE);
    $this->assertEquals('/parent', $ret);


    ### もろもろ
    $noRoutes = array(
      '/archives' => '/ret1',
      '/parent/child' => '/ret2/child',
    );

    $ret = $gobjMVC->_get_routeURL_longest($noRoutes, '/');
    $this->assertEquals('', $ret);

    $ret = $gobjMVC->_get_routeURL_longest($noRoutes, '/archives');
    $this->assertEquals('/ret1', $ret);

    $ret = $gobjMVC->_get_routeURL_longest($noRoutes, '/archives/1234');
    $this->assertEquals('/ret1', $ret);

    $ret = $gobjMVC->_get_routeURL_longest($noRoutes, '/archives/1234/567');
    $this->assertEquals('/ret1', $ret);

    $ret = $gobjMVC->_get_routeURL_longest($noRoutes, '/parent/child/gchild');
    $this->assertEquals('/ret2/child', $ret);
  }


  public function test_get_controllerName()
  {
    global $gobjMVC;

    ### top
    $url = '/';
    $this->go_to($url);

    $ret = $gobjMVC->_get_controllerName($this->routes);
    $this->assertEquals('top', $ret);

    ### search
    $url = '/?s=test';
    $this->go_to($url);

    $ret = $gobjMVC->_get_controllerName($this->routes);
    $this->assertEquals(hykwMVC::ROUTENAME_SEARCH, $ret);


    ### page(parent)
    $url = '/parent';
    $this->go_to($url);

    $ret = $gobjMVC->_get_controllerName($this->routes);
    $this->assertEquals('page', $ret);

    ### page(child)
    $url = '/parent/子ページ２';
    $this->go_to($url);

    $ret = $gobjMVC->_get_controllerName($this->routes);
    $this->assertEquals('page', $ret);

    $url = '/parent/static1?code=3';
    $this->go_to($url);

    $ret = $gobjMVC->_get_controllerName($this->routes);
    $this->assertEquals('page', $ret);


    $url = '/archives/category/01?code=3';
    $this->go_to($url);

    $ret = $gobjMVC->_get_controllerName($this->routes);
    $this->assertEquals('', $ret);

    # error
    $url = '/xxxxxx';
    $this->go_to($url);
    $ret = $gobjMVC->_get_controllerName($this->routes);
    $this->assertEquals('', $ret);

    $url = '/xxxxxx/xxxxx';
    $this->go_to($url);
    $ret = $gobjMVC->_get_controllerName($this->routes);
    $this->assertEquals('', $ret);


    ##### 親子孫とか
    $urls = array(
      '/level-1',
      '/level-1/level-2',
      '/level-1/level-2/level-3',
      '/level-1/level-2/level-3/level-3b',
    );

    # 1
    $routes = array(
      '/level-1' => 'l1',
    );
    foreach ($urls as $url) {
      $this->go_to($url);
      $ret = $gobjMVC->_get_controllerName($routes);
      $this->assertEquals('l1', $ret);
    }

    # 2
    $routes = array(
      '/level-1' => 'l1',
      '/level-1/level-2' => 'l12',
    );
    foreach ($urls as $url) {
      $this->go_to($url);
      $ret = $gobjMVC->_get_controllerName($routes);

      if ($url == '/level-1')
        $this->assertEquals('l1', $ret);
      else
        $this->assertEquals('l12', $ret);
    }

    # 3
    $routes = array(
      '/level-1' => 'l1',
      '/level-1/level-2' => 'l12',
      '/level-1/level-2/level-3' => 'l123',
    );
    foreach ($urls as $url) {
      $this->go_to($url);
      $ret = $gobjMVC->_get_controllerName($routes);

      switch ($url) {
      case '/level-1':
        $this->assertEquals('l1', $ret);
        break;
      case '/level-1/level-2':
        $this->assertEquals('l12', $ret);
        break;
      default:
        $this->assertEquals('l123', $ret);
        break;
      }
    }
  }


  public function test_noContentsRoutes()
  {
    global $gobjMVC;


    $noContentsRoutes = array(
      '/dist' => 'dist',
      '/parent2/child' => 'pc',
    );

    $url = '/dist';
    $this->go_to($url);
    $ret = $gobjMVC->routes($this->routes, $this->noRoutes, $noContentsRoutes, TRUE);
    $this->assertEquals('dist', $ret);

    $url = '/parent2/child';
    $this->go_to($url);
    $ret = $gobjMVC->routes($this->routes, $this->noRoutes, $noContentsRoutes, TRUE);
    $this->assertEquals('pc', $ret);

    $url = '/parent2';
    $this->go_to($url);
    $ret = $gobjMVC->routes($this->routes, $this->noRoutes, $noContentsRoutes, TRUE);
    $this->assertEquals(hykwMVC::ROUTE_CONTROLLER_NOT_FOUND, $ret);

    $url = '/parent2?code=3';
    $this->go_to($url);
    $ret = $gobjMVC->routes($this->routes, $this->noRoutes, $noContentsRoutes, TRUE);
    $this->assertEquals(hykwMVC::ROUTE_CONTROLLER_NOT_FOUND, $ret);

  }



}
