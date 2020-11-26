<?php 
header("Content-type:text/html;charset=utf-8");
date_default_timezone_set("PRC");
if(!empty($_GET['id'])&&!empty($_GET['list'])) {
    $sum = 1;
    $fromList = array(5,6,7,8,9,11,12,13,20,14,15,4,3,22);
    $getList = array(5,6,7,8,9,10,11,12,13,14,15,16,17,18);

    $id = !empty($_GET['id'])?intval($_GET['id']):1;
    $cid = !empty($_GET['cid'])?intval($_GET['cid']):1;
    $list = !empty($_GET['list'])?intval($_GET['list']):1;

    if(intval($id)<($sum+1)) {
        mkdirs(__DIR__.DIRECTORY_SEPARATOR.'json'.DIRECTORY_SEPARATOR.md5($fromList[intval($list)-1].(($sum+1)-intval($id))));
        $jsonDir = __DIR__.DIRECTORY_SEPARATOR.'json'.DIRECTORY_SEPARATOR.md5($fromList[intval($list)-1].(($sum+1)-intval($id))).DIRECTORY_SEPARATOR;
        if(file_exists($jsonDir.$fromList[intval($list)-1].'.json')){
            $caiji = json_decode(file_get_contents($jsonDir.$fromList[intval($list)-1].'.json'),true);
        }else{
            $caiji = getVideo($fromList[intval($list)-1], (($sum+1)-intval($id)));
            if(is_array($caiji)&&$caiji['status']=='200') {
                file_put_contents($jsonDir.$fromList[intval($list)-1].'.json', json_encode($caiji,JSON_UNESCAPED_UNICODE), LOCK_EX);
            }
        }
    }

    if(!empty($fromList[$list])) {
        $toHref = '<script>setTimeout(function(){window.location.href="/caiji.php?id='.($id).'&list='.($list+1).'&cid='.($cid).'";}, 1000);</script>';
    } else if(!empty($caiji)&&is_array($caiji)&&!empty($caiji['data'][$cid])) {
        $toHref = '<script>setTimeout(function(){window.location.href="/caiji.php?id='.($id).'&list=1&cid='.($cid+1).'";}, 1000);</script>';
    } else if($id<$sum) {
        $toHref = '<script>setTimeout(function(){window.location.href="/caiji.php?id='.($id+1).'&list=1&cid=1";}, 1000);</script>';
    } else{
        deleteDir(__DIR__.DIRECTORY_SEPARATOR.'json'.DIRECTORY_SEPARATOR);
        exit('<h1>采集完成</h1>');
    }

    echo '<h1>正在采集 栏目 '.intval($list).' 第 '.intval($id).' 页 第 '.intval($cid).' 条数据</h1>';

    if(!empty($caiji)&&is_array($caiji)) {
        $vod_name = $vod_actor = $vod_director = $vod_content = $vod_pic = $vod_area = $vod_language = $vod_year = $vod_addtime = $vod_url = $vod_continu = $value = NULL;

        $title = '/([a-zA-Z0-9\/\_\～\-\.\:\·\,\，\、\（\）\s]+|[\x{4e00}-\x{9fff}]+|[\x{0800}-\x{4e00}]+|[\x{AC00}-\x{D7A3}]+|[\x{4e00}-\x{9fa5}]+)/u';

        $value = array_reverse($caiji['data'])[$cid-1];

        preg_match_all($title, $value['vod_name'], $vod_name);
        if(empty($vod_name[1])){exit($toHref);}
        $vod_name = implode($vod_name[1]);

        preg_match_all($title, preg_replace(array("/\&.*?\;/","/\,\,/"),"",$value['vod_actor']), $vod_actor);
        if(empty($vod_actor[1])){exit($toHref);}
        $vod_actor = implode($vod_actor[1]);

        preg_match_all($title, $value['vod_director'], $vod_director);
        if(empty($vod_director[1])){exit($toHref);}
        $vod_director = implode($vod_director[1]);

        preg_match_all($title, preg_replace(array("/\&.*?\;/","/\,\,/"),"",strip_tags($value['vod_content'])), $vod_content);
        if(empty($vod_content[1])){exit($toHref);}
        $vod_content = implode($vod_content[1]);

        preg_match_all('/([a-zA-Z0-9\:\_\/\-\.]+(png|jpg))/', $value['vod_pic'], $vod_pic);
        if(empty($vod_pic[2])){exit($toHref);}
        $vod_pic = implode($vod_pic[1]);

        preg_match_all('/([\x{4e00}-\x{9fff}]+)/u', $value['vod_area'], $vod_area);
        if(empty($vod_area[1])){exit($toHref);}
        $vod_area = implode($vod_area[1]);

        preg_match_all('/([\x{4e00}-\x{9fff}]+)/u', $value['vod_language'], $vod_language);
        if(empty($vod_language[1])){exit($toHref);}
        $vod_language = implode($vod_language[1]);

        preg_match_all($title, $value['vod_year'], $vod_year);
        if(empty($vod_year[1])){exit($toHref);}
        $vod_year = implode($vod_year[1]);

        if(empty($value['vod_addtime'])){exit($toHref);}
        $vod_addtime = strtotime($value['vod_addtime']);

        preg_match_all('/([a-zA-Z0-9\:\_\/\-\.]+m3u8)/', $value['vod_url'], $vod_url);
        if(empty($vod_url[1])){exit($toHref);}
        $vod_length = ceil(getDuration($vod_url[1][count($vod_url[1])-1])/60);
        if(empty($vod_length)){exit($toHref);}
        $vod_url = implode(PHP_EOL,$vod_url[1]);

        preg_match_all($title, $value['vod_continu'], $vod_continu);
        if(empty($vod_continu[1])){exit($toHref);}
        $vod_continu = implode($vod_continu[1]);
        $sunstrCount = substr_count(strtolower($vod_continu),"集");
        if(empty($sunstrCount)) {
            $vod_continu = trim(strtoupper(mb_substr($vod_continu,0,ChineseChar($vod_continu))));
        }

        preg_match_all('/([\x{4e00}-\x{9fff}]+)/u', trim($value['vod_name']), $getPY);
        $getPY = !empty($getPY[1])?getPinyin(implode('',$getPY[1])):'G';

        $SqlID = 'SELECT * from ff_vod WHERE vod_name_md5=\''.md5(htmlspecialchars(trim($vod_name),ENT_QUOTES)).'\'';
        // 配置数据库连接
        $SelectID = getSelect('127.0.0.1','root','root','root',$SqlID);

        if(!empty($SelectID)){
            $SqlID = 'UPDATE ff_vod SET vod_url=\''.trim($vod_url).'\', vod_continu=\''.trim($vod_continu).'\', vod_addtime=\''.trim($vod_addtime).'\' WHERE vod_id='.$SelectID;
            // 配置数据库连接
            getInsert('127.0.0.1','root','root','root',$SqlID);
        } else {
            $data = '('.($getList[intval($list)-1]).', \''.htmlspecialchars(trim($vod_name),ENT_QUOTES).'\', \''.md5(htmlspecialchars(trim($vod_name),ENT_QUOTES)).'\', \'\', \'\', \'\', \''.htmlspecialchars(trim($vod_actor),ENT_QUOTES).'\', \''.htmlspecialchars(trim($vod_director),ENT_QUOTES).'\', \''.htmlspecialchars(trim($vod_content),ENT_QUOTES).'\', \''.trim($vod_pic).'\', \''.trim($vod_area).'\', \''.trim($vod_language).'\', \''.trim($vod_year).'\', \''.trim($vod_continu).'\', \'\', 1, \''.trim($vod_addtime).'\', '.mt_rand(6050,9608).', '.mt_rand(508,809).', '.mt_rand(50,90).', '.mt_rand(5,9).', \''.trim($vod_addtime).'\', 1, 1, '.mt_rand(2080,4090).', '.mt_rand(108,809).', \'m3u8\', \'\', \''.trim($vod_url).'\', \'admin\', \'\', \'\', \''.trim($getPY).'\', \'\', \''.mt_rand(6,9).'.'.mt_rand(2,5).'\', '.mt_rand(3050,7608).', 1, 0, \''.$vod_length.'\', 0)';
            $data = 'INSERT INTO `ff_vod` (`vod_cid`, `vod_name`, `vod_name_md5`, `vod_title`, `vod_keywords`, `vod_color`, `vod_actor`, `vod_director`, `vod_content`, `vod_pic`, `vod_area`, `vod_language`, `vod_year`, `vod_continu`, `vod_total`, `vod_isend`, `vod_addtime`, `vod_hits`, `vod_hits_day`, `vod_hits_week`, `vod_hits_month`, `vod_hits_lasttime`, `vod_stars`, `vod_status`, `vod_up`, `vod_down`, `vod_play`, `vod_server`, `vod_url`, `vod_inputer`, `vod_reurl`, `vod_jumpurl`, `vod_letter`, `vod_skin`, `vod_gold`, `vod_golder`, `vod_isfilm`, `vod_filmtime`, `vod_length`, `vod_weekday`) VALUES '.$data.';';
            // 配置数据库连接
            getInsert('127.0.0.1','root','root','root',$data);
        }
    }
    echo $toHref;
}

function getVideo($cid = 1, $p = 1) {
    $url = '';
    $jsonVideo = getJson($url.'?a=json&cid='.intval($cid).'&g=plus&m=api&p='.intval($p),getRandIP());
    if(!empty($jsonVideo[1])&&$jsonVideo[1] == 200) {
        return json_decode($jsonVideo[0], true);
    }
    return '404';
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

function getSelect($host, $user, $passwd, $db, $sql){
    try {
        $conn = new PDO('mysql:host='.$host.';port=3306;dbname='.$db, $user, $passwd);
        $conn->query("set names utf8");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        foreach ($conn->query($sql) as $row) {
            $conn = null;
            return $row['vod_id'];
        }
    } catch(PDOException $e) {
        $conn = null;
        return $sql . "<br>" . $e->getMessage();exit();
    }
}

function code62($x) {
    $show = '';
    while($x > 0) {
        $s = $x % 62;
        if ($s > 35) {
            $s = chr($s+61);
        } elseif ($s > 9 && $s <=35) {
            $s = chr($s + 55);
        }
        $show .= $s;
        $x = floor($x/62);
    }
    return $show;
}

function shortUrl($url) {
    $url = md5($url);
    $url = crc32($url);
    $result = sprintf("%u", $url);
    return code62($result);
}

function mkdirs($path) {
    if(!is_dir($path)) {
        mkdirs(dirname($path));
        if(!mkdir($path, 0755)) {
            return false;
        }
    }
    return true;
}

function deleteDir($directory) {
    if (is_dir($directory) == false){
        exit("The Directory Is Not Exist!");
    }
    $handle = opendir($directory);
    while (($file = readdir($handle)) !== false){
        if ($file != "." && $file != ".."){
        is_dir("$directory/$file")?
            deleteDir("$directory/$file"):
            unlink("$directory/$file");
        }
    }
    if (readdir($handle) == false){
        closedir($handle);
        rmdir($directory);
    }
}

function getJson($url, $randIP) {
    $ch = curl_init();
    $user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:75.0) Gecko/20100101 Firefox/75.0';
    $options =  array(
        CURLOPT_URL => $url,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPGET => TRUE,
        CURLOPT_NOBODY => FALSE,
        CURLOPT_HEADER => FALSE,
        CURLOPT_REFERER => $url,
        CURLOPT_USERAGENT => $user_agent,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_FOLLOWLOCATION => TRUE,
        CURLOPT_SSL_VERIFYPEER => FALSE,
        CURLOPT_SSL_VERIFYHOST => FALSE,
        CURLOPT_COOKIEFILE => __DIR__.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'cookie.txt',
        CURLOPT_HTTPHEADER => array('Content-Type: text/plain', 'X-FORWARDED-FOR:' . $randIP, 'CLIENT-IP:' . $randIP),
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

function ChineseChar($str) {
    $index = -1;
    $len = strlen($str);
    for ($i = 0; $i < $len; $i++) {
        $char = substr($str, $i, 1);
        $isCh = preg_match("/^[" . chr(0xa1) . "-" . chr(0xff) . "]+$/", $char);
        if ($isCh) {
            $index = $i;
            break;
        }
    }
    return $index;
}

function getDuration($url) {
    $res = getJson($url, getRandIP());
    if($res[1] !== 200){return FALSE;}
    $rei = explode(chr(10),$res[0]);
    $srt = array_pop($rei);
    if(strstr($srt,'m3u8')) {
        $res = getJson(str_replace(substr($url,strrpos($url,'/')+1),$srt,$url), getRandIP());
        if($res[1] !== 200){return FALSE;}
    }
    preg_match_all('/\d+[.]\d+/',$res[0],$arr);
    $res = array_sum($arr[0]);
    return ceil($res);
}

function getPinyin($s0){
	$firstchar_ord=ord(strtoupper(mb_substr($s0,0,1))); 
	if (($firstchar_ord>=65 and $firstchar_ord<=91)or($firstchar_ord>=48 and $firstchar_ord<=57)) return mb_substr($s0,0,1); 
	$s=@iconv("UTF-8","GBK//IGNORE", $s0); 
	$asc=ord(mb_substr($s,0,1))*256+ord(mb_substr($s,1,1))-65536; 
	if($asc>=-20319 and $asc<=-20284)return "A";
	if($asc>=-20283 and $asc<=-19776)return "B";
	if($asc>=-19775 and $asc<=-19219)return "C";
	if($asc>=-19218 and $asc<=-18711)return "D";
	if($asc>=-18710 and $asc<=-18527)return "E";
	if($asc>=-18526 and $asc<=-18240)return "F";
	if($asc>=-18239 and $asc<=-17923)return "G";
	if($asc>=-17922 and $asc<=-17418)return "H";
	if($asc>=-17417 and $asc<=-16475)return "J";
	if($asc>=-16474 and $asc<=-16213)return "K";
	if($asc>=-16212 and $asc<=-15641)return "L";
	if($asc>=-15640 and $asc<=-15166)return "M";
	if($asc>=-15165 and $asc<=-14923)return "N";
	if($asc>=-14922 and $asc<=-14915)return "O";
	if($asc>=-14914 and $asc<=-14631)return "P";
	if($asc>=-14630 and $asc<=-14150)return "Q";
	if($asc>=-14149 and $asc<=-14091)return "R";
	if($asc>=-14090 and $asc<=-13319)return "S";
	if($asc>=-13318 and $asc<=-12839)return "T";
	if($asc>=-12838 and $asc<=-12557)return "W";
	if($asc>=-12556 and $asc<=-11848)return "X";
	if($asc>=-11847 and $asc<=-11056)return "Y";
	if($asc>=-11055 and $asc<=-10247)return "Z";
	return 0;
}
