<?php
header("Content-Type: text/html; charset=utf-8");

function getData() {
    $data = "{id:'2884035','limit':30,'offset':0}";
    $detail = getJson('https://music.163.com/weapi/playlist/detail?csrf_token=',$data);
    if(empty(json_decode($detail[0],true)['result'])||strpos($detail[0],'"code":406')!==false) {
        exit(header("Refresh:0"));
    }
    $detail = json_decode($detail[0],true)['result']['tracks'];
    $detail = $detail[array_rand($detail,1)];
    $comments = getJson('https://music.163.com/weapi/v1/resource/comments/R_SO_4_'.$detail['id'],$data);
    $comments = json_decode($comments[0],true)['hotComments'];
    $comments = $comments[array_rand($comments,1)];
    foreach($detail['artists'] as $value){
        $singer[] = $value['name'];
    }
    return array('id' => ceil($detail['id']),
                 'name' => strip_tags($detail['name']),
                 'singer' => strip_tags(implode(',',$singer)),
                 'content' => strip_tags($comments['content']),
                 'nickname' => strip_tags($comments['user']['nickname']),
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
<meta charset="utf-8">
<title>网易云热评墙 - 记录伤心生活</title>
<style>
.container {width: 60%;margin: 10% auto 0;background-color: #f0f0f0;padding: 2% 5%;border-radius: 10px}
ul {padding-left: 20px;}
ul li {line-height: 2.3}
a {text-decoration-line: none;color: #20a53a}
</style>
</head>
<body>
    <div class="container">
        <h2><a href="https://music.163.com/song?id=<?php echo $name['id'];?>"><?php echo $name['name'];?> - <?php echo $name['singer'];?></a></h2>
        <p><?php echo $name['content'];?></p>
        <p style="text-align:right;">来自:<?php echo $name['nickname'];?></p>
        <p style="text-align:center;"><a href="https://beian.miit.gov.cn/" target="_blank">豫ICP备17035756号</a></p>
    </div>
</body>
</html>
