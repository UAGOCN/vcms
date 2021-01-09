<?php
// ThinkPHP环境探针布署模式
// Author：caolei@topthink.com
//检查当前脚本环境
function check_env(){
    $moban =<<<HTML
<!DOCTYPE html>
<!--STATUS OK-->
<html lang="cmn-Hans">
<head>
<meta charset="utf-8">
<meta http-equiv="x-UA-Compatible" content="IE=Edge">
<title>环境检测</title>
</head>
<body>
    <div class="title">环境检测</div>
HTML;
    $moban .="</body></html>";
    return $moban;
}
?>