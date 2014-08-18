<?php

# 通常
$routes = array(
    '/' => '/top',
    'search' => '/search',
);

# 指定URLへのアクセスはリダイレクト(/archives -> / にリダイレクト）
$noRoutes = array(
    '/archives' => '/',
);

# 固定ページを作らずに、特定URLへのアクセスを指定コントローラに渡す場合
# アンテナ/RSS 用URLなどを想定
$noContentsRoutes = array(
   '/hogehoge' => 'hogeController',
);

$ret = $gobjMVC->routes($routes, $noRoutes, $noContentsRoutes);
if ($ret != hykwMVC::RET_OK) {
  echo $ret;
}

