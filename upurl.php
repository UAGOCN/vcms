<?php 
header("Content-type:text/html;charset=utf-8");
date_default_timezone_set("PRC");

$id = !empty($_GET['id'])?intval($_GET['id']):1;
$end = !empty($_GET['end'])?intval($_GET['end']):1;

if(!empty($id)&&$id<=$end){
    $SqlID = 'UPDATE ff_vod SET vod_url=\'https://bitdash-a.akamaihd.net/content/sintel/hls/video/250kbit.m3u8?'.time().'\', vod_continu=\'HD1280\' WHERE vod_id='.$id;
    getInsert('127.0.0.1','root','root','root',$SqlID);
    echo '正在 第'.$id.'条 更新<script>setTimeout(function(){window.location.href="/upurl.php?id='.($id+1).'&end='.$end.'";}, 1000);</script>';
} else {
    echo '更新完毕';
}

function getInsert($host, $user, $passwd, $db, $sql){
    try {
        $conn = new PDO('mysql:host='.$host.';port=3306;dbname='.$db, $user, $passwd);
        $conn->query("set names utf8");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec($sql);
        $conn = null;
        return "新记录插入成功";
    } catch(PDOException $e) {
        $conn = null;
        return $sql . "<br>" . $e->getMessage();exit();
    }
}
