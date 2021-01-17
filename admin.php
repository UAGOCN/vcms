<?php
register_shutdown_function('session_write_close');
session_set_cookie_params(['lifetime' => time()+900,'path' => '/','domain' => $_SERVER['HTTP_HOST'],'secure' => FALSE,'httponly' => TRUE,'samesite' => 'lax']);
session_name('__cflb');
session_start();
$_SESSION['AdminLogin'] = 1;
$_SESSION['host_key'] = ''; // 后台登陆域名,只允许唯一该域名访问后台管理界面
header("Location: ./index.php?s=Admin-Login"); 
?>