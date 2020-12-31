<?php
header("Content-Type: text/html; charset=utf-8");

function getData() {
    $data = "{id:'2884035','limit':10,'offset':0}";
    $detail = getJson('https://music.163.com/weapi/playlist/detail?csrf_token=',$data);
    if(empty(json_decode($detail[0],true)['result'])||strpos($detail[0],'"code":406')!==false) {
        exit(header("Refresh:0"));
    }
    $detail = json_decode($detail[0],true)['result']['tracks'];
    $detail = $detail[array_rand($detail,1)];
    $comments = getJson('https://music.163.com/weapi/v1/resource/comments/R_SO_4_'.$detail['id'],$data);
    if(empty(json_decode($comments[0],true)['hotComments'])||strpos($comments[0],'"code":406')!==false) {
        exit(header("Refresh:0"));
    }
    $comments = json_decode($comments[0],true)['hotComments'];
    $comments = $comments[array_rand($comments,1)];
    foreach($detail['artists'] as $value){
        $singer[] = strip_tags($value['name']);
    }
    $bit_rate = 320000;
    $data = "{'ids': [".$detail['id']."], 'br': ".$bit_rate.", 'csrf_token': ''}";
    $player = getJson('http://music.163.com/weapi/song/enhance/player/url?csrf_token=',$data);
    if(empty(json_decode($player[0],true)['data'][0])){
        exit(header("Refresh:0"));
    }
    $player = json_decode($player[0],true)['data'][0]['url'];
    return array('id' => ceil($detail['id']),
                 'name' => strip_tags($detail['name']),
                 'singer' => strip_tags(implode(',',$singer)),
                 'content' => strip_tags($comments['content']),
                 'nickname' => strip_tags($comments['user']['nickname']),
                 'player' => strip_tags($player)
            );
}

function encrypted_request($data){
    $_PUBKEY = "65537";
    $_NONCE = "0CoJUm6Qyw8W8jud";
    $_MODULUS = '157794750267131502212476817800345498121872783333389747424011531025366277535262539913701806290766479189477533597854989606803194253978660329941980786072432806427833685472618792592200595694346872951301770580765135349259590167490536138082469680638514416594216629258349130257685001248172188325316586707301643237607';
    $secKey = randString(16);
    $encText = aesEncrypt( aesEncrypt($data, $_NONCE), $secKey );
    $pow = bchexdec( bin2hex( strrev($secKey) ) );
    $encKeyMod = bcpowmod($pow, $_PUBKEY, $_MODULUS);
    $encSecKey = bcdechex($encKeyMod);
    $data = array(
        'params' => $encText,
        'encSecKey' => $encSecKey
    );
    return $data;
}

function randString($length){
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $result = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++){
        $result .= $chars[rand(0, $max)];
    }
    return $result;
}

function aesEncrypt($data, $secKey){
    $cip = openssl_encrypt($data, 'aes-128-cbc', pack('H*', bin2hex($secKey)), OPENSSL_RAW_DATA, '0102030405060708');
    $cip = base64_encode($cip);
    return $cip;
}

function bcdechex($dec){
    $hex = '';
    do {
        $last = bcmod($dec, 16);
        $hex = dechex($last).$hex;
        $dec = bcdiv(bcsub($dec, $last), 16);
    } while($dec>0);
    return $hex;
}

function bchexdec($hex){
    if(strlen($hex) == 1) {
        return hexdec($hex);
    } else {
        $remain = substr($hex, 0, -1);
        $last = substr($hex, -1);
        return bcadd(bcmul(16, bchexdec($remain)), hexdec($last));
    }
}

function getJson($url, $data) {
    $randIP = getRandIP();
    $data = http_build_query(encrypted_request($data));
    $user_agent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1';
    $ch = curl_init();
    $options =  array(
        CURLOPT_URL => $url,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_NOBODY => FALSE,
        CURLOPT_HEADER => FALSE,
        CURLOPT_REFERER => $url,
        CURLOPT_ENCODING => "gzip",
        CURLOPT_USERAGENT => $user_agent,
        CURLOPT_POST => TRUE,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_FOLLOWLOCATION => TRUE,
        CURLOPT_SSL_VERIFYPEER => FALSE,
        CURLOPT_SSL_VERIFYHOST => FALSE,
        CURLOPT_HTTPHEADER => array('Accept:application/json', 'Accept-Encoding:gzip, deflate, br', 'Accept-Language:zh-CN,zh;q=0.8,gl;q=0.6,zh-TW;q=0.4', 'Accept-Type:application/x-www-form-urlencoded', 'Origin:https://music.163.com', 'X-FORWARDED-FOR:' . $randIP, 'CLIENT-IP:' . $randIP),
    );
    curl_setopt_array($ch, $options);
    $result = curl_exec ($ch);
    $Code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return array($result, $Code);
}

function getRandIP() {
    $ip2id= round(rand(600000, 2550000) / 10000);
    $ip3id= round(rand(600000, 2550000) / 10000);
    $ip4id= round(rand(600000, 2550000) / 10000);
    $_array = array('218','218','66','66','218','218','60','60','202','204','66','66','66','59','61','60','222','221','66','59','60','60','66','218','218','62','63','64','66','66','122','211');
    $randarr= mt_rand(0,count($_array)-1);
    $ip1id = $_array[$randarr];
    return $ip1id.'.'.$ip2id.'.'.$ip3id.'.'.$ip4id;
}

function get_musicID($url){
    $music = '';
    if (preg_match('/http://music.163.com/\#/song\?id=(\d+)/i', $url, $matches)) {
        return $matches[1];
    }
    return $music;
}

$name = getData();
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8" />
<title>网易云热评墙_记录伤心生活</title>
<style>
*{margin: 0;padding: 0}
.myaudio{width:100%;height:60px;position: fixed;bottom:0;}
.myaudio>.myaudio-center{max-width:500px;height:100%;width:100%;position: absolute;top:0 }
.myaudio>.myaudio-center>.aud-btn{height:50px;width:50px;position: absolute;top:5px;left:10px}
.myaudio>.myaudio-center>.aud-btn>img{height:100%;width:100%;}
.myaudio>.myaudio-center>.aud-show{height:100%;position: absolute;left:65px;}
.myaudio>.myaudio-center>.aud-show>#Progress-time,.myaudio>.myaudio-center>.aud-show>#Progress-end{width:40px; height:100%;font-size: 13px;line-height: 60px;text-align: center;}
.myaudio>.myaudio-center>.aud-show>#Progress-time{position: absolute;left:0;}
.myaudio>.myaudio-center>.aud-show>#Progress{position: absolute;left:50px;top:28px;height:6px;background: #8cd287}
.myaudio>.myaudio-center>.aud-show>#Progress-end{position: absolute;right:0;}
.myaudio>.myaudio-center>.aud-show>#Progress>#jin{width:6px;height:6px;background-color: blue;position: absolute;left:0px;;}
.myaudio>.myaudio-center>.aud-show>#Progress>#yuan{width:9px;height:9px;background-color: #fff;border: 1px solid #92d79b;border-radius: 6px;position: absolute;left:-3px;top:-2px;}
.container {width: 60%;margin: 10% auto 0;background-color: #f0f0f0;padding: 2% 5%;border-radius: 10px}
ul {padding-left: 20px;}
ul li {line-height: 2.3}
a {text-decoration-line: none;color: #20a53a}
</style>
<script type="text/javascript" src="/Public/jquery/jquery.min.js" charset="utf-8"></script>
</head>
<body>
<div class="container">
    <h2><a href="https://music.163.com/song?id=<?php echo $name['id'];?>"><?php echo $name['name'];?> - <?php echo $name['singer'];?></a></h2>
    <p><?php echo $name['content'];?></p>
    <p style="text-align:right;">来自:<?php echo $name['nickname'];?></p>
    <p style="text-align:center;"><a href="https://beian.miit.gov.cn/" target="_blank">ICP备20201111号</a></p>
</div>
<div class="myaudio">
    <div class="myaudio-center">
        <audio id="aud">
            <source src='<?php echo $name['player'];?>' type='audio/ogg' width='300px' >
            <source src='<?php echo $name['player'];?>' type='audio/mpeg' width='300px' >
            <source src='<?php echo $name['player'];?>' type='audio/wav' width='300px' >
            浏览器不支持此格式
        </audio>
        <div class="aud-btn">
            <img src="images/play.png" id="play" />
        </div>
        <div class="aud-show">
            <div id="Progress-time">00:00</div> 
                <div id="Progress">
                    <span id="jin"></span><span id="yuan"></span> 
                </div> 
                <div id="Progress-end">00:00</div> 
            </div>
        </div>
</div>
<script type="text/javascript">
var footerW = $('.myaudio').width();
var audioW = $('.myaudio-center').width();
$('.myaudio-center').css({'left':(footerW-audioW)/2});
$('.aud-show').css({'width':audioW-70});
$('#Progress').css({'width':audioW-170});
var i=0;
$('#play').click(function () {
    i++;
    if (i % 2 != 0) {
        $(this).attr('src', 'images/pause.png');
        aud_play();
    } else {
        $(this).attr('src', 'images/play.png');
        aud_pause();
    }
})
var music;
var audio = document.getElementById("aud");
function aud_play(q=0) {
    audio.currentTime = q;
    audio.play();
    music = setInterval(function () {
        var curtime = audio.currentTime.toFixed(2);
        var durtime = audio.duration.toFixed(2);
        var str = "00:00";
        var time = formatSeconds(curtime);
        var time1 = str.substring(0, str.length - formatSeconds(durtime).length) + formatSeconds(durtime);
        $('#Progress-time').html(time);
        $('#Progress-end').html(time1);
        $width = curtime / durtime * (audioW - 181);
        $('#jin').css({width: $width});
        $('#yuan').css({left: $width});
    }, 100);
}
function aud_pause() {
    document.getElementById("aud").pause();
    clearInterval(music);
}
function formatSeconds(value) {
    var theTime = parseInt(value);// 秒
    var theTime1 = 0;// 分
    var theTime2 = 0;// 小时
    if (theTime > 60) {
        theTime1 = parseInt(theTime / 60);
        theTime = parseInt(theTime % 60);
        if (theTime1 > 60) {
            theTime2 = parseInt(theTime1 / 60);
            theTime1 = parseInt(theTime1 % 60);
        }
    }
    var result = "" + theTime;
    result  =   (result.length==1)?'0'+result:result;
    if (theTime1 > 0) {
        theTime1  =   (theTime1.length==1)?'0'+theTime1:theTime1;
        result = "" + theTime1 + ":" + result;
    }
    if (theTime2 > 0) {
        theTime2  =   (theTime2.length==1)?'0'+theTime2:theTime2;
        result = "" + theTime2 + ":" + result;
    }
    result =   (result.length==2)?'00:'+result:result;
    return result;
}
var cont = $("#yuan");
var contW = $("#yuan").width();
var startX, sX, moveX, disX;
var winW = $('#Progress').width();
$("#yuan").on({
    touchstart: function (e) {
        startX = e.originalEvent.targetTouches[0].pageX;
        sX = $(this).offset().left-110;
        leftX = startX - sX;
        rightX = winW - contW + leftX;
    },
    touchmove: function (e) {
        e.preventDefault();
        moveX = e.originalEvent.targetTouches[0].pageX;
        if(moveX<leftX){moveX=leftX;}
        if(moveX>rightX){moveX=rightX;}
        $(this).css({
            "left":moveX+sX-startX,
        });
        $('#jin').width($(this).width()+moveX+sX-startX);
       var w = audio.duration.toFixed(2)*($('#jin').width()/winW);
        $('#play').attr('src', 'images/pause.png');
        aud_play(w);
    },
    mousedown: function (ev) {
        aud_pause();
        var patch = parseInt($(this).css("height")) / 2;
        var left1 = parseInt($(this).parents('.myaudio-center').css("left"));
        $(this).mousemove(function (ev) {
            var oEvent = ev || event;
            var oX = oEvent.clientX;
            console.log(oX);
            var l = oX - patch-left1-115;
            console.log(l);
            var w = $(window).width() - $(this).width();
            console.log(w);
            if (l < 0) {
                l = 0;
            }
            if (l > w) {
                l = w;
            }
            $(this).css({left: l});
            $('#jin').width($(this).width()+l);
            var w = audio.duration.toFixed(2)*($('#jin').width()/winW);
             $('#play').attr('src', 'images/pause.png');
             aud_play(w);
        });
        $(this).mouseup(function () {
            $(this).unbind('mousemove');
        });
    }
});
</script>
<script type="text/javascript">var aud = document.getElementById("aud");aud.onended = function(){location.reload();};</script>
</body>
</html>
