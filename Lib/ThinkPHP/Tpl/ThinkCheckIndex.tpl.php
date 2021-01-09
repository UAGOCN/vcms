<?php
// ThinkPHP环境探针布署模式
// Author：caolei@topthink.com
class IndexAction extends Action
{
    public function index()
    {
        header("Content-Type:text/html; charset=utf-8");
        $env_table = $this->check_env();
        echo $env_table;
    }

    public function check_env(){
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
    <div class="title">^_^ Hello</div>
HTML;
        $moban .="</body></html>";
        return $moban;
    }
}
?>