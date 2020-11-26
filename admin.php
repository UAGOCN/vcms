<?php
register_shutdown_function('session_write_close');
session_set_cookie_params('3600', "/", '.san.tv', TRUE, TRUE, 'lax');
@session_start();
$params = session_get_cookie_params();
setcookie("PHPSESSID", session_id(), 0, $params["path"], $params["domain"], TRUE, TRUE, 'lax');
unset($params);
$_SESSION['AdminLogin'] = 1;
$_SESSION['host_key'] = ''; // 后台登陆域名,只允许唯一该域名访问后台管理界面
header("Location: ./index.php?s=Admin-Login"); 
?>