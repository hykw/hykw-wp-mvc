hykw-wp-mvc
===========

    /controller/[URL]/index.php
    
    /model/[URL]/index.php
           behavior/xxx.php
    
    /view/[URL]/index.php
          helper/xxxx.php
    
    /files/css
           js
           images
    
    - / は top に対応する（/ -> /controller/top/index.php)

# 呼び出し例
- functions.php
    $gobjMVC = new hykwMVC(__DIR__);

- /index.php
    $routes = array(
      '/' => '/top',
      'search' => '/search',
    );

    $noRoutes = array(
      '/archives' => '/',
    );
    
    $ret = $gobjMVC->routes($routes, $noRoutes);
    if ($ret != hykwMVC::RET_OK) {
      echo $ret;
    }

- controller/[URL]/index.php
    global $gobjMVC;
    $gobjMVC->callHelper('header');

    $imgPath = $gobjMVC->callBehavior('urls', 'get_imgPath');
    $html_sidebar = $gobjMVC->callHelper('sidebar', 'get_sidebarHTML', $imgPath);

    $contents = $gobjMVC->callModel('column', 'get_columnContents');

    $args = array(
      'imgPath' => $imgPath,
      'contents' => $contents,
      'html_sidebar' => $html_sidebar,
    );
    echo $gobjMVC->callView('column', 'view', $args);

    $gobjMVC->callHelper('footer');
