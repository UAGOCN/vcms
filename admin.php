<?php
register_shutdown_function('session_write_close');
session_set_cookie_params('3600', "/", $_SERVER['HTTP_HOST'], TRUE, TRUE);
@session_start();
$_SESSION['AdminLogin'] = 1;
$_SESSION['host_key'] = ''; // 后台登陆域名,只允许唯一该域名访问后台管理界面
header("Location: ./index.php?s=Admin-Login"); 
?>