<?php
/*-------------------------------------------------文件夹与文件操作开始------------------------------------------------------------------*/
//读取文件
function read_file($l1){
	return @file_get_contents($l1);
}
//写入文件
function write_file($l1, $l2=''){
	$dir = dirname($l1);
	if(!is_dir($dir)){
		mkdirss($dir);
	}
	return @file_put_contents($l1, $l2);
}
//递归创建文件
function mkdirss($dirs,$mode=0755) {
	if(!is_dir($dirs)){
		mkdirss(dirname($dirs), $mode);
		return @mkdir($dirs, $mode);
	}
	return true;
}
// 数组保存到文件
function arr2file($filename, $arr=''){
	if(is_array($arr)){
		$con = var_export($arr,true);
	} else{
		$con = $arr;
	}
	$con = "<?php\nreturn $con;\n?>";//\n!defined('IN_MP') && die();\nreturn $con;\n
	write_file($filename, $con);
}
/*-------------------------------------------------系统路径相关函数开始------------------------------------------------------------------*/
//获取当前地址栏URL
function get_http_url(){
	return htmlspecialchars("http://".$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"]);
}
//获取根目录路径
function get_site_path($filename){
    $basepath = $_SERVER['PHP_SELF'];
    $basepath = substr($basepath,0,strpos($basepath,$filename));
	return $basepath;
}
//相对路径转绝对路径
function get_base_url($baseurl,$url){
	if("#" == $url){
		return "";
	}elseif(FALSE !== stristr($url,"http://")){
		return $url;
	}elseif( "/" == substr($url,0,1) ){
		$tmp = parse_url($baseurl);
		return $tmp["scheme"]."://".$tmp["host"].$url;
	}else{
		$tmp = pathinfo($baseurl);
		return $tmp["dirname"]."/".$url;
	}
}
//获取指定地址的域名
function get_domain($url){
	preg_match("|http://(.*)\/|isU", $url, $arr_domain);
	return $arr_domain[1];
}
/*-------------------------------------------------字符串处理开始------------------------------------------------------------------*/
// 过滤字符串
function getWD($str){
    preg_match_all('/([a-zA-Z0-9\_\～\！\：\。\，\·\《\》\?\=\-\,\.\:\|\/\s]+|\<br \/\>+|\<p\>+|\<\/p\>+|[\x{4e00}-\x{9fff}]+|[\x{0800}-\x{4e00}]+|[\x{AC00}-\x{D7A3}]+|[\x{4e00}-\x{9fa5}]+)/u', $str, $match);
    return !empty($match[1])?implode($match[1]):'';
}
// 过滤字符串
function getSrt($str){
    preg_match_all('/([a-zA-Z0-9\_\.]+)/u', $str, $match);
    return !empty($match[1])?implode($match[1]):'';
}
// 过滤字符串无限循环
function getWDSrt($data,$str=false) {
    $da = array();
    if($str) {
        if(is_array($data)) {
            foreach ($data as $key => $val) {
                if(is_array($val)){
                    $da[$key] = getWDSrt($val);
                } else {
                    $da[trim(getSrt($key))] = trim(getSrt($val));
                }
            }
        } else {
            $da = trim(getSrt($data));
        }
        return $da;
    } else {
        if(is_array($data)) {
            foreach ($data as $key => $val) {
                if(is_array($val)){
                    $da[$key] = getWDSrt($val);
                } else {
                    $da[trim(getWD($key))] = trim(getWD($val));
                }
            }
        } else {
            $da = trim(getWD($data));
        }
        return $da;
    }
}
// UT*转GBK
function u2g($str){
	return iconv("UTF-8","GBK//IGNORE",$str);
}
// GBK转UTF8
function g2u($str){
	return iconv("GBK","UTF-8//IGNORE",$str);
}
// 转换成JS
function t2js($l1, $l2=1){
    $I1 = str_replace(array("\r", "\n"), array('', '\n'), addslashes($l1));
    return $l2 ? "document.write(\"$I1\");" : $I1;
}
// 去掉换行
function nr($str){
	$str = str_replace(array("<nr/>","<rr/>"),array("\n","\r"),$str);
	return trim($str);
}
//去掉连续空白
function nb($str){
	$str = str_replace("　",' ',str_replace("&nbsp;",' ',$str));
	//$str = ereg_replace("[\r\n\t ]{1,}",' ',$str);
    $str = preg_replace("/[\r\n\t ]{1,}/",' ',$str);
	return trim($str);
}
//字符串截取(同时去掉HTML与空白)
function msubstr($str, $start=0, $length, $suffix=false){
    return ff_msubstr(preg_replace('/<[^>]+>/i','',preg_replace("/[\r\n\t ]{1,}/",' ',nb($str))),$start,$length,'utf-8',$suffix);
	//return ff_msubstr(eregi_replace('<[^>]+>','',ereg_replace("[\r\n\t ]{1,}",' ',nb($str))),$start,$length,'utf-8',$suffix);
}
function ff_msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true){
	$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
	$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
	$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
	$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
	preg_match_all($re[$charset], $str, $match);
	$length_new = $length;
    $length_chi = 0;
	for($i=$start; $i<$length; $i++){
		if (!empty($match[0][$i])&&ord($match[0][$i]) > 0xa0){
			//中文
		}else{
			$length_new++;
			$length_chi++;
		}
	}
	if($length_chi<$length){
		$length_new = $length+($length_chi/2);
	}
	$slice = join("",array_slice($match[0], $start, $length_new));
    if($suffix && $slice != $str){
		return $slice."…";
	}
    return $slice;
}
// 汉字转拼单
function ff_pinyin($str,$ishead=0,$isclose=1){
	$str = u2g($str);//转成GBK
	$restr = '';
	$str = trim($str);
	$slen = strlen($str);
	if($slen<2){
		return $str;
	}
	if(empty($pinyins)){
		$fp = fopen('./Lib/Common/pinyin.dat','r');
		while(!feof($fp)){
			$line = trim(fgets($fp));
			$pinyins[$line[0].$line[1]] = substr($line,3,strlen($line)-3);
		}
		fclose($fp);
	}
	for($i=0;$i<$slen;$i++){
		if(ord($str[$i])>0x80){
			$c = $str[$i].$str[$i+1];
			$i++;
			if(isset($pinyins[$c])){
				if($ishead==0){
					$restr .= $pinyins[$c];
				}
				else{
					$restr .= $pinyins[$c][0];
				}
			}else{
				//$restr .= "_";
			}
        // }else if( eregi("[a-z0-9]",$str[$i]) ){
        }else if( preg_match("/[a-z0-9]/i",$str[$i]) ){
			$restr .= $str[$i];
		}
		else{
			//$restr .= "_";
		}
	}
	if($isclose==0){
		unset($pinyins);
	}
	return $restr;
}
//生成字母前缀
function ff_letter_first($s0){
	$firstchar_ord=ord(strtoupper(mb_substr($s0,0,1))); 
	if (($firstchar_ord>=65 and $firstchar_ord<=91)or($firstchar_ord>=48 and $firstchar_ord<=57)) return mb_substr($s0,0,1); 
	$s=iconv("UTF-8","GBK//IGNORE", $s0); 
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
	return 0;//null
}
/*-------------------------------------------------采集函数开始------------------------------------------------------------------*/
// 采集内核
function ff_file_get_contents($url,$timeout=10,$referer=''){
	if(function_exists('curl_init')){
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_HEADER, 0);
		curl_setopt ($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,$timeout);
		if($referer){
			curl_setopt ($ch, CURLOPT_REFERER, $referer);
		}		
		$content = curl_exec($ch);
		curl_close($ch);
		if($content){
			return $content;
		}		
	}
	$ctx = stream_context_create(array('http'=>array('timeout'=>$timeout)));
	$content = @file_get_contents($url, 0, $ctx);
	if($content){
		return $content;
	}
	return false;
}
// 采集-匹配规则结果
function ff_preg_match($rule,$html){
	$arr = explode('$$$',$rule);
	if(count($arr) == 2){
	    preg_match('/'.$arr[1].'/', $html, $data);
		return $data[$arr[0]].'';
	}else{
	    preg_match('/'.$rule.'/', $html, $data);
		return $data[1].'';
	}
}
// 采集-匹配规则结果all
function ff_preg_match_all($rule,$html){
	$arr = explode('$$$',$rule);
	if(count($arr) == 2){
	    preg_match_all('/'.$arr[1].'/', $html, $data);
		return $data[$arr[0]];
	}else{
	    preg_match_all('/'.$rule.'/', $html, $data);
		return $data[1];
	}
}
// 采集-倒序采集
function ff_krsort_url($listurl){
   krsort($listurl);
   foreach($listurl as $val){
       $list[]=$val;
   }
   return $list;
}
// 采集-将所有替换规则保存在一个字段
function ff_implode_rule($arr){
    foreach($arr as $val){
	    $array[] = trim(stripslashes($val));
	}
	return implode('|||',$array);
}
//  采集-规则替换
function ff_replace_rule($str){
	//$str = str_replace(array("\n","\r"),array("<nr/>","<rr/>"),strtolower($str));
	$arr1 = array('?','"','(',')','[',']','.','/',':','*','||');
	$arr2 = array('\?','\"','\(','\)','\[','\]','\.','\/','\:','.*?','(.*?)');
	//$str = str_replace(array("\n","\r"),array("<nr/>","<rr/>"),strtolower($str));
	return str_replace('\[$ppvod\]','([\s\S]*?)',str_replace($arr1,$arr2,$str));
}
//TAG分词自动获取
function ff_tag_auto($title,$content){
	$data = ff_file_get_contents('http://keyword.discuz.com/related_kw.html?ics=utf-8&ocs=utf-8&title='.rawurlencode($title).'&content='.rawurlencode(msubstr($content,0,500)));
	if($data) {
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, $data, $values, $index);
		xml_parser_free($parser);
		$kws = array();
		foreach($values as $valuearray) {
			if($valuearray['tag'] == 'kw') {
				if(strlen($valuearray['value']) > 3){
					$kws[] = trim($valuearray['value']);
				}
			}elseif($valuearray['tag'] == 'ekw'){
				$kws[] = trim($valuearray['value']);
			}
		}
		return implode(',',$kws);
	}
	return false;
}
/*-------------------------------------------------栏目相关函数开始------------------------------------------------------------------*/
//通过栏目名名获取对应的栏目ID
function getlistid($str){
    $arr = list_search(F('_ppvod/list'),'list_name='.$str);
	if(empty($arr)){
		return 0;
	}else{
	    return $arr[0]['list_id'];
	}
}
//通过栏目ID获取对应的栏目名称/别名等
function getlistname($cid,$type='list_name'){
    $arr=list_search(F('_ppvod/list'),'list_id='.$cid);
	if(!empty($arr[0])){
		return $arr[0][$type];
	}else{
	    return '';
	}
}
// 检查当前栏目是否没有小类
function getlistson($pid){
	$tree=list_search(F('_ppvod/listtree'),'list_id='.$pid);
	if(!empty($tree[0]['son'])){
		return false;
	}else{
	    return true;
	}
}
//生成栏目sql查询语句范围
function getlistsqlin($cid){
	$tree = list_search(F('_ppvod/listtree'),'list_id='.$cid);
	if (!empty($tree[0]['son'])) {
		foreach($tree[0]['son'] as $val){
			$arr['vod_cid'][] = $val['list_id'];
		}
		$channel = implode(',', $arr['vod_cid']);
		return array('IN',''.$channel.'');	
	}
	return $cid;
}
//通过栏目ID返回其它值按数组方式
function getlistarr($cid,$type='list_id'){
    $tree = list_search(F('_ppvod/listtree'),'list_id='.$cid);
	if(!empty($tree[0]['son'])){
		foreach($tree[0]['son'] as $val){$param[]=$val[$type];}
		return $param;
	}else{
		return false;
	}
}
//去重后的模板栏目ID参数 $cids = array(1,2,3,...)
function getlistarr_tag($cids){
	$cid = '';
	foreach($cids as $key=>$value){
		if(getlistson($value)){
			$cid .= ','.$value;
		}else{
			$cidin = getlistsqlin($value);
			$cid .= ','.$cidin[1];
		}
	}
	$cidarr = explode(',',$cid);
	unset($cidarr[0]);
	$cidarr = array_unique($cidarr);
	return $cidarr;
}
// 获取栏目数据统计
function getcount($cid,$id=''){
    $where = array();
	if(999 == $cid){
		$rs = M("Vod");
		$where['vod_cid'] = getlistsqlin($id);//array('gt',0);
		$where['vod_addtime'] = array('gt',getxtime(1));//当天更新的影视
		$count = $rs->where($where)->count('vod_id');
	}elseif(888 == $cid){
		$rs = M("News");
		$where['news_cid'] = getlistsqlin($id);//array('gt',0);
		$where['news_addtime'] = array('gt',getxtime(1));//当天更新的小说
		$count = $rs->where($where)->count('news_id');
	}elseif(0 == $cid){
		$rs = M("Vod");
		$where['vod_cid'] = array('gt',0);
		$count = $rs->where($where)->count('vod_id');	
	}else{
		$sid = getlistname($cid,'list_sid');
		if ($sid == '2'){
			$where['news_cid'] = getlistsqlin($cid);
			$where['news_status'] = 1;
			$rs = M("News");
			$count = $rs->where($where)->count('news_id');
		}else{
			$where['vod_cid'] = getlistsqlin($cid);
			$where['vod_status'] = 1;
			$rs = M("Vod");
			$count = $rs->where($where)->count('vod_id');
		}
	}
	//dump($rs->getLastSql());
	return $count+0;
}
//获取模型名称
function getsidname($sid){
	if ($sid==1){
		return 'vod';
	}elseif ($sid==2){
		return 'news';
	}elseif ($sid==3){
		return 'special';
	}else{
		return 'url';
	}
}
//获取模型ID
function ff_sid($sidname){
	if ($sidname == 'vod'){
		return 1;
	}elseif ($sidname == 'news'){
		return 2;
	}elseif($sidname == 'special'){
		return 3;
	}else{
		return 9;
	}
}
/*-------------------------------------------------模板常用函数-----------------------------------------------------------------*/
//获得某天前的时间戳
function getxtime($day){
	$day = intval($day);
	return mktime(23,59,59,date("m"),date("d")-$day,date("y"));
}
// 获取标题颜色
function getcolor($str,$color){
	if(empty($color)){
	    return $str;
	}else{
	    return '<font color="'.$color.'">'.$str.'</font>';
	}
}
// 获取时间颜色
function getcolordate($type='Y-m-d H:i:s',$time,$color='red'){
	if((time()-$time)>86400){
	    return date($type,$time);
	}else{
	    return '<font color="'.$color.'">'.date($type,$time).'</font>';
	}
}
// 读出qvod地址的文件名
function ff_qvod_name($jiname){
	$jiname = ff_preg_match('3$$$(.*)\|(.*)\|(.*)\.(.*)\|',$jiname);
	return $jiname;
}
// 处理积分样式
function getjifen($fen){
	$array = explode('.',$fen);
	return '<strong>'.$array[0].'</strong>.'.$array[1];
}
//分页样式
function getPageIndex($currentPage,$totalPages,$halfPer=5,$url,$pagego){
    $linkPage = '';
    $linkPage .= ( $currentPage > 1 )
		? '<li><a href="'.str_replace('{!page!}',1,$url).'">首页</a></li><li><a href="'.str_replace('{!page!}',($currentPage-1),$url).'">上一页</a></li>' 
        : '<li><a href="javascript:;">首页</a></li><li><a href="javascript:;">上一页</a></li>';
    for($i=$currentPage-$halfPer,$i>1||$i=1,$j=$currentPage+$halfPer,$j<$totalPages||$j=$totalPages;$i<$j+1;$i++){
        $linkPage .= ($i==$currentPage)?'<li class="hidden-xs active"><a href="'.str_replace('{!page!}',$i,$url).'">'.$i.'</a></li>':'<li class="hidden-xs"><a href="'.str_replace('{!page!}',$i,$url).'">'.$i.'</a></li>'; 
    }
	$linkPage .= '<li class="active visible-xs"><span class="num">'.$currentPage.'/'.$totalPages.'</span></li>';
	$linkPage .= ( $currentPage < $totalPages )
        ? '<li><a href="'.str_replace('{!page!}',($currentPage+1),$url).'">下一页</a></li><li><a href="'.str_replace('{!page!}',$totalPages,$url).'">尾页</a></li>'
        : '<li><a href="javascript:;">下一页</a></li><li><a href="javascript:;">尾页</a></li>';
	//
	if(C('url_html') && C('url_html_list')){
    	return str_replace('-1'.C('html_file_suffix'),C('html_file_suffix'),str_replace('index1'.C('html_file_suffix'),'',$linkPage));
	}else{
		return $linkPage;
	}
}
//后台分页样式
function getpage($currentPage,$totalPages,$halfPer=5,$url,$pagego){
    $linkPage = '';
    $linkPage .= ( $currentPage > 1 )
        ? '<a href="'.str_replace('{!page!}',1,$url).'" class="pagegbk">首页</a>&nbsp;<a href="'.str_replace('{!page!}',($currentPage-1),$url).'" class="pagegbk">上一页</a>&nbsp;' 
        : '<em>首页</em>&nbsp;<em>上一页</em>&nbsp;';
    for($i=$currentPage-$halfPer,$i>1||$i=1,$j=$currentPage+$halfPer,$j<$totalPages||$j=$totalPages;$i<$j+1;$i++){
        $linkPage .= ($i==$currentPage)?'<span>'.$i.'</span>&nbsp;':'<a href="'.str_replace('{!page!}',$i,$url).'">'.$i.'</a>&nbsp;'; 
    }
    $linkPage .= ( $currentPage < $totalPages )
        ? '<a href="'.str_replace('{!page!}',($currentPage+1),$url).'" class="pagegbk">下一页</a>&nbsp;<a href="'.str_replace('{!page!}',$totalPages,$url).'" class="pagegbk">尾页</a>'
        : '<em>下一页</em>&nbsp;<em>尾页</em>';
	if(!empty($pagego)){
		$linkPage .='&nbsp;<input type="input" name="page" id="page" size=4 class="pagego"/><input type="button" value="跳 转" onclick="'.$pagego.'" class="pagebtn" />';
	}
	//
	if(C('url_html') && C('url_html_list')){
    	return str_replace('-1'.C('html_file_suffix'),C('html_file_suffix'),str_replace('index1'.C('html_file_suffix'),'',$linkPage));
	}else{
		return $linkPage;
	}
}
//处理最大分页参数
function get_maxpage($currentpage,$totalpages){
	if ($currentpage > $totalpages){
		$currentpage = $totalpages;
	}
	return $currentpage;
}
// 获取与处理人气值
function ff_get_hits($sidname,$type='hits',$array,$js=true){
	if((C('url_html') && $js) || $type=='insert'){
		return '<script type="text/javascript" src="'.C('site_path').'index.php?s=hits-show-id-'.$array[$sidname.'_id'].'-type-'.$type.'-sid-'.$sidname.'" charset="utf-8"></script>';
	}else{
		return $array[$type];
	}
}
// 递归多维数组转为一级数组
function ff_arrays2array($array){
	static $result_array=array();
	foreach($array as $value){
		if(is_array($value)){
			ff_arrays2array($value);
		}else{ 
			$result_array[]=$value;
		}
	}
	return $result_array;
}
// 返回下一篇或上一篇的内容的信息
function ff_detail_array($sid='vod', $type='next', $id, $cid, $field='vod_id,vod_cid,vod_status,vod_name,vod_jumpurl'){
	//优先读取缓存数据
	$cache_key = 'cache_'.$sid.'_'.$type.'_'.$cid.'_'.$id;
	if(C('data_cache_vod')){
		$array = S($cache_key);
		if($array){
			return $array;
		}
	}
	$where = array();
	$where[$sid.'_cid'] = $cid;
	$where[$sid.'_status'] = 1;
	if($type == 'next'){
		$where[$sid.'_id'] = array('gt', $id);
		$order = $sid.'_id asc';
	}else{
		$where[$sid.'_id'] = array('lt', $id);
		$order = $sid.'_id desc';
	}
	if($sid != 'vod'){
		$field = str_replace('vod_',$sid.'_',$field);
	}
	$rs = M(ucfirst($sid));
	$array = $rs->field($field)->where($where)->limit(1)->order($order)->find();
	// 是否写入缓存
	if( C('data_cache_vod') ){
		S($cache_key, $array, intval(C('data_cache_vod')));
	}
	return $array;
}
/*-------------------------------------------------访问路径函数开始------------------------------------------------------------------*/
// 重写动态路径
function UU($model,$params,$redirect=false,$suffix=false,$type='wd'){
	//rewrite重写
	if(C('url_rewrite')){
		if($model == 'Home-vod/show'){
			$reurl = str_replace(array('$id','$page'),array(!empty($params['id'])?$params['id']:1,!empty($params['p'])?$params['p']:1),C('rewrite_vodlist'));
		}elseif($model == 'Home-vod/read'){
			$reurl = str_replace('$id',!empty($params['id'])?$params['id']:1,C('rewrite_voddetail'));
		}elseif($model == 'Home-vod/play'){
			$reurl = str_replace(array('$id','$sid','$pid'),array(!empty($params['id'])?$params['id']:1,!empty($params['sid'])?$params['sid']:0,!empty($params['pid'])?$params['pid']:1),C('rewrite_vodplay'));
		}elseif($model == 'Home-vod/search'){
			$reurl = str_replace(array('$wd','$page','$actor','$director','$area','$language','$year','$order','wd'),array(!empty($params['wd'])?$params['wd']:'',!empty($params['p'])?$params['p']:1,!empty($params['actor'])?$params['actor']:'',!empty($params['director'])?$params['director']:'',!empty($params['area'])?$params['area']:'',!empty($params['language'])?$params['language']:'',!empty($params['year'])?$params['year']:'',!empty($params['order'])?$params['order']:'',$type),C('rewrite_vodsearch'));
		}elseif($model == 'Home-vod/type'){
			$reurl = str_replace(array('$id','$page','$wd','$area','$language','$actor','$director','$year','$letter','$order'),array(!empty($params['id'])?$params['id']:1,!empty($params['p'])?$params['p']:1,!empty($params['wd'])?$params['wd']:'',!empty($params['area'])?$params['area']:'',!empty($params['language'])?$params['language']:'',!empty($params['actor'])?$params['actor']:'',!empty($params['director'])?$params['director']:'',!empty($params['year'])?$params['year']:'',!empty($params['letter'])?$params['letter']:'',!empty($params['order'])?$params['order']:''),C('rewrite_vodtype'));
		}elseif($model == 'Home-tag/vod'){
			$reurl = str_replace(array('$wd','$page'),array(!empty($params['wd'])?$params['wd']:'',!empty($params['p'])?$params['p']:1),C('rewrite_vodtag'));
		}elseif($model == 'Home-news/show'){
			$reurl = str_replace(array('$id','$page'),array(!empty($params['id'])?$params['id']:1,!empty($params['p'])?$params['p']:1),C('rewrite_newslist'));
		}elseif($model == 'Home-news/read'){
			$reurl = str_replace('$id',$params['id'],C('rewrite_newsdetail'));
		}elseif($model == 'Home-news/search'){
			$reurl = str_replace(array('$wd','$page'),array(!empty($params['wd'])?$params['wd']:'',!empty($params['p'])?$params['p']:1),C('rewrite_newssearch'));
		}elseif($model == 'Home-tag/news'){
			$reurl = str_replace(array('$wd','$page'),array(!empty($params['wd'])?$params['wd']:'',!empty($params['p'])?$params['p']:1),C('rewrite_newstag'));
		}elseif($model == 'Home-special/show'){
			$reurl = str_replace('$page',!empty($params['p'])?$params['p']:1,C('rewrite_specialshow'));
		}elseif($model == 'Home-special/read'){
			$reurl = str_replace('$id',!empty($params['id'])?$params['id']:1,C('rewrite_specialdetail'));
		}elseif($model == 'Home-gb/show'){
			$reurl = str_replace(array('$id','$page'),array(!empty($params['id'])?$params['id']:1,!empty($params['p'])?$params['p']:1),C('rewrite_guestbook'));
		}elseif($model == 'Home-my/show'){
			$reurl = str_replace('$id',!empty($params['id'])?$params['id']:1,C('rewrite_mytpl'));
		}elseif($model == 'Home-map/show'){
			$reurl = str_replace(array('$id','$limit','$page'),array(!empty($params['id'])?$params['id']:1,!empty($params['limit'])?$params['limit']:100,!empty($params['p'])?$params['p']:1),C('rewrite_map'));
		}elseif($model == 'Home-map/sitemap'){
			$reurl = str_replace(array('$id'),array(!empty($params['id'])?$params['id']:''),C('rewrite_sitemap'));
		}
		//伪静态规则设置正确
		if(!empty($reurl)){
			if($model == 'Home-map/show'||$model == 'Home-map/sitemap'){
				return $reurl.'.xml';
			} else {
			    return $reurl.C('url_html_suffix');
			}
		}else{
			return str_replace('index.php?s=/Home-','',U($model,$params,$redirect,$suffix));
		}
	}
	//TP框架默认生成的路径
	$reurl = str_replace('Home-','',str_replace('index.php?s=/','index.php?s=',U($model,$params,$redirect,$suffix)));
	//不是静态模式则去掉index.php
	if(!C('url_html')){
		return str_replace('index.php','',$reurl);
	}
	return $reurl;
};
// 获取广告调用地址
function getadsurl($str,$charset="utf-8"){
	return '<script type="text/javascript" src="'.substr(trim(C('site_url')),0,strlen(trim(C('site_url')))-1).C('site_path').C('admin_ads_file').'/'.$str.'.js" charset="'.$charset.'"></script>';
}
/*******************替换自定义路径的变量****************************/
function str_replace_dir($urldir,$id,$cid,$name){
	$old = array('{listid}','{listdir}','{pinyin}','{id}','{md5}',C('html_file_suffix'));
	$new = array($cid,getlistname($cid,'list_dir'),ff_pinyin($name),$id,md5($id),'');
	return str_replace($old,$new,$urldir);
}
/*******************获取栏目页路径****************************
* $sid 模型名称'movie/article/special'
* $arrurl为数组参数传入参考U函数(方便动态模式直接生成)
* $page 分页数字,大于1时返回的URL带有分页跳转参数变量{!page!}
* 只有一个栏目ID参数时 $arrurl['id'] = $cid;
*/
function ff_list_url($sid,$arrurl,$page){
	//静态模式
	if(C('url_html') && C('url_html_list') && in_array($sid,array('vod','news'))){
		$showurl = C('site_path').str_replace('index'.C('html_file_suffix'),'',ff_list_url_dir($sid,$arrurl['id'],$page).C('html_file_suffix'));
		return $showurl;
	}else{
		if($page > 1){ $arrurl['p'] = '{!page!}'; }
		$showurl = UU('Home-'.$sid.'/show',$arrurl,false,true);
	}
	return $showurl;
}
/**********************静态生成的栏目结构******************************
* (返回的值为buildHtml的文件名,生成静态时需要将{!page!}换成对应的page值)
* $mid 模型名称'movie/article/special'
* $cid 当前分类的栏目ID值
* $page 分页数字,大于1时返回的URL带有分页跳转参数变量{!page!}
*/
function ff_list_url_dir($sid,$cid,$page){
	//影视或文章
	if('vod' == $sid){
		$listdir = str_replace_dir(C('url_vodlist'),$sid,$cid,$page);
	}else{
		$listdir = str_replace_dir(C('url_newslist'),$sid,$cid,$page);
	}
	if($page > 1){
		$listdir .= '-{!page!}';
	}
	return $listdir;
}
/*************************获取内容页路径****************************************
* $sid 模型名称'vod/news/special'
* $id 影片ID/文章ID/专题ID值
* $cid 当前影片/文章/专题/对应的栏目ID值
* $name 影片/文章/专题/的名称
* $page 分页数字,大于1时返回的URL带有分页跳转参数变量{!page!}
* $jumpurl 跳转地址
*/
function ff_data_url($sid,$id,$cid,$name,$page,$jumpurl){
	//有跳转地址
	if ($jumpurl) {
		return $jumpurl;
	}
	//静态
	if(C('url_html')){
		$readurl = C('site_path').str_replace('index'.C('html_file_suffix'),'',ff_data_url_dir($sid,$id,$cid,$name,$page).C('html_file_suffix'));
		return $readurl;
	}
	//动态模式
	$arrurl['id'] = $id;
	if($page > 1){
		$arrurl['p'] = '{!page!}';
	}
	return UU('Home-'.$sid.'/read',$arrurl,false,true);
}
/*************************获取详情页目录****************************************
* 返回的值为buildHtml的文件名
* $sid 模型名称'vod/news/special'
* $id 影片ID/文章ID/专题ID值
* $cid 当前影片/文章/专题/对应的栏目ID值
* $name 影片/文章/专题/的名称
* $page 分页数字,大于1时返回的URL带有分页跳转参数变量{!page!}
*/
function ff_data_url_dir($sid,$id,$cid,$name,$page){
	//专题直接返回
	if('special' == $sid){
		return trim(C('url_special').$id);
	}
	//影视或文章
	if('vod' == $sid){
		$datadir = str_replace_dir(C('url_voddata'),$id,$cid,$name);
	}else{
		$datadir = str_replace_dir(C('url_newsdata'),$id,$cid,$name);
	}
	if($page > 1){
		$datadir .= '-{!page!}';
	}
	return $datadir;
}
//获取播放页链接
function ff_play_url($id,$sid,$pid,$cid,$name){
	//静态模式
	if(C('url_html') && C('url_html_play')){
		$playurl = C('site_path').str_replace('index'.C('html_file_suffix'),'',ff_play_url_dir($id,$sid,$pid,$cid,$name).C('html_file_suffix'));
		if(C('url_html_play') == 1){
			$playurl .= '?'.$id.'-'.$sid.'-'.$pid;
		}
	}else{//动态
		$playurl = UU('Home-vod/play',array('id'=>$id,'sid'=>$sid,'pid'=>$pid),false,true);
	}
	return $playurl;
}
//播放页静态生成结构
function ff_play_url_dir($id,$sid,$pid,$cid,$name){
	$playdir = str_replace_dir(C('url_play'),$id,$cid,$name);
	if(C('url_html_play') == 2){
		$playdir .= '-'.$id.'-'.$sid.'-'.$pid;
	}	
	return $playdir;
}
//获取专题URL
function ff_special_url($page){
	if(C('url_html')){
		return C('site_path').str_replace('index'.C('html_file_suffix'),'',ff_special_url_dir($page).C('html_file_suffix'));
	}else{
		return UU('Home-special/show','',false,true);
	}
}
//获取专题静态模式目录结构URL
function ff_special_url_dir($page){
	$listdir = trim(C('url_special')).'index';
	if($page > 1){
		$listdir .= '-{!page!}';
	}
	return $listdir;
}
// 获取某图片的访问地址
function ff_img_url($file,$content='',$number=1){
	if(!$file){
		return ff_img_url_preg($file,$content,$number);
	}
	if(strpos($file,'http://') !== false||strpos($file,'https://') !== false){
		return $file;
	}
	$prefix = C('upload_http_prefix');
	if(!empty($prefix)){
		return $prefix.$file;
	}else{
		return C('site_path').C('upload_path').'/'.$file;
	}
}
// 获取某图片的缩略图地址
function ff_img_url_small($file,$content,$number=1){
	if(!$file){
		return ff_img_url_preg($file,$content,$number);
	}
	if(strpos($file,'http://') !== false||strpos($file,'https://') !== false){
		return $file;
	}	
	$prefix = C('upload_http_prefix');
	if(!empty($prefix)){
		return $prefix.$file;
	}else{
		return C('site_path').C('upload_path').'-s/'.$file;
	}	
}
//正则提取正文里指定的第几张图片地址
function ff_img_url_preg($file,$content,$number=1){
	preg_match_all('/<img(.*?)src="(.*?)(?=")/si',$content,$imgarr);///(?<=img.src=").*?(?=")/si
	preg_match_all('/(?<=src=").*?(?=")/si',implode('" ',$imgarr[0]).'" ',$imgarr);
	$countimg = count($imgarr);
	if($number > $countimg){
		$number = $countimg;
	}
	return $imgarr[0][($number-1)];
}
// Tag链接
function ff_tag_url($str,$sid=1){
	if($sid==2){
		return UU('Home-tag/news',array('wd'=>rawurlencode($str)),false,true);
	}else{
		return UU('Home-tag/vod',array('wd'=>rawurlencode($str)),false,true);
	}
}
// 内容页Tag链接
function ff_content_url($content,$array_tag=array(),$sid=''){
	if(!empty($array_tag)){
		foreach($array_tag as $key=>$value){
			$content = str_replace($value['tag_name'],'<a href="'.ff_tag_url($value['tag_name'],$sid).'">'.$value['tag_name'].'</a>',$content);
		}
	}
	return preg_replace('/<\/p>/','</p>'.PHP_EOL,$content);
}
// 自定义模板链接
function ff_mytpl_url($tplname){
	$tplname = str_replace(array('my_','.html'),'',$tplname);
	if(C('url_html')){
		return C('site_path').C('url_mytpl').$tplname.C('html_file_suffix');
	}
	return UU('Home-my/show',array('id'=>trim($tplname)),false,true);
}
// 地图索引链接
function ff_map_index($tplname){
	if(C('url_html')){
		return C('site_path').$tplname.'.xml';
	}
	return UU('Home-map/sitemap',array('id'=>$tplname),false,true);
}
// 地图页链接
function ff_map_url($tplname){
	if(C('url_html')){
		return C('site_path').C('url_map').$tplname.'.xml';
	}
	$limit = 30;
	if($tplname != 'rss'){
		$limit = 100;
	}
	return UU('Home-map/show',array('id'=>$tplname,'limit'=>$limit),false,true);
}
// 获取26个字母链接
function ff_letter_url($file='vod',$str=''){
	if(C('url_html')){
		$index='index.html';
	}else{
		$index='index.php';
	}
    for($i=1;$i<=26;$i++){
	   $url = UU('Home-'.$file.'/search',array('id'=>chr($i+64),'x'=>'letter'),false,true);
	   $str.='<a href="'.$url.'" class="letter_on">'.chr($i+64).'</a>';
	}
	return $str;
}
// 获取搜索带链接
function ff_search_url($str,$type="actor",$sidname='vod',$action='search'){
	$array = array();
    $str = str_replace(array('/','|',',','，','、'),'|',$str);
	$arr = explode('|',$str);
	foreach($arr as $key=>$val){
		$array[$key] = '<a href="'.UU('Home-'.$sidname.'/'.$action,array($type=>rawurlencode($val)),false,true,$type).'" target="_blank">'.$val.'</a>';
	}
	return implode(' ',$array);
}
// 获取影片最后一集array(sid,pid,jiname,jipath)
function ff_play_url_end($vod_url){
	$array = array();
	$arr_server = explode('$$$',trim($vod_url));
	foreach($arr_server as $key=>$value){
		$array[$key] = array(count(explode(chr(13),str_replace(array("\r\n", "\n", "\r"),chr(13),$value))),$key);
	}
	$max_key = max($array);
	$array = explode(chr(13),str_replace(array("\r\n", "\n", "\r"),chr(13),$arr_server[$max_key[1]]));
	$arr_url = explode('$',end($array));
	if($arr_url[1]){
		return array($max_key[1],$max_key[0],$arr_url[0],$arr_url[1]);
	}else{
		return array($max_key[1],$max_key[0],'第'.((strlen($max_key[0])=='1')?'00'.($max_key[0]):((strlen($max_key[0])=='2')?'0'.($max_key[0]):$max_key[0])).'集',$arr_url[0]);
	}
}
/*---------------------------------------标签解析函数开始------------------------------------------------------------------*/
//路径参数处理函数
function ff_param_url(){
	$where = array();
	$where['sid'] = !empty($_REQUEST['sid']) ? intval($_REQUEST['sid']) : 0;
	$where['id'] = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$where['year'] = !empty($_REQUEST['year']) ? intval($_REQUEST['year']) : 0;
	$where['language'] = !empty($_REQUEST['language']) ? htmlspecialchars(trim(getWDSrt(rawurldecode($_REQUEST['language'])))) : '';
	$where['area'] = !empty($_REQUEST['area']) ? htmlspecialchars(trim(getWDSrt(rawurldecode($_REQUEST['area'])))) : '';
	$where['letter'] = !empty($_REQUEST['letter']) ? htmlspecialchars(getWDSrt($_REQUEST['letter'])) : '';
	$where['actor'] = !empty($_REQUEST['actor']) ? htmlspecialchars(trim(getWDSrt(rawurldecode($_REQUEST['actor'])))) : '';
	$where['director'] = !empty($_REQUEST['director']) ? htmlspecialchars(trim(getWDSrt(rawurldecode($_REQUEST['director'])))) : '';
	$where['wd'] = !empty($_REQUEST['wd']) ? htmlspecialchars(trim(getWDSrt(rawurldecode($_REQUEST['wd'])))) : '';
	//
	$where['limit'] = !empty($_GET['limit']) ? intval($_GET['limit']) : 10;
	$where['page'] = !empty($_GET['p']) ? intval($_GET['p']) : 1;
	$where['order'] = !empty($_GET['order']) ? ff_order_by(getWDSrt($_GET['order'])) : 'addtime';
	return $where;
}
//分页跳转参数处理
function ff_param_jump($where){
	if(!empty($where['sid'])){
		$jumpurl['sid'] = $where['sid'];
	}
	if(!empty($where['id'])){
		$jumpurl['id'] = $where['id'];
	}
	if(!empty($where['year'])){
		$jumpurl['year'] = $where['year'];
	}		
	if(!empty($where['language'])){
		$jumpurl['language'] = rawurlencode($where['language']);
	}
	if(!empty($where['area'])){
		$jumpurl['area'] = rawurlencode($where['area']);
	}
	if(!empty($where['letter'])){
		$jumpurl['letter'] = $where['letter'];
	}	
	if(!empty($where['actor'])){
		$jumpurl['actor'] = rawurlencode($where['actor']);
	}
	if(!empty($where['director'])){
		$jumpurl['director'] = rawurlencode($where['director']);
	}
	if(!empty($where['wd'])){
		$jumpurl['wd'] = rawurlencode($where['wd']);
	}		
	if(!empty($where['order'] != 'addtime' && $where['order'])){
		$jumpurl['order'] = $where['order'];
	}
	$jumpurl['p'] = '';
	return $jumpurl;
}
//返回安全的orderby
function ff_order_by($order = 'addtime'){
	if(empty($order)){
		return 'addtime';
	}
	$array = array();
	$array['addtime'] = 'addtime';
	$array['id'] = 'id';
	$array['hits'] = 'hits';
	$array['hits_month'] = 'hits_month';
	$array['hits_week'] = 'hits_week';
	$array['stars'] = 'stars';
	$array['up'] = 'up';
	$array['down'] = 'down';
	$array['gold'] = 'gold';
	$array['golder'] = 'golder';
	$array['year'] = 'year';
	$array['letter'] = 'letter';
	$array['filmtime'] = 'filmtime';
	return $array[trim($order)];
}
//生成参数列表,以数组形式返回
function ff_param_lable($tag = ''){
	$param = array();
	$array = explode(';',str_replace('num:','limit:',$tag));
	foreach ($array as $v){
		list($key,$val) = explode(':',trim($v));
		$param[trim(getWDSrt($key))] = trim(getWDSrt($val));
	}
	return $param;
}
/******************************************
* @处理影视标签函数
* @以字符串方式传入,通过ff_param_lable函数解析为以下变量
* name:vod 必须(vod/news/special/guestbook/common/user)
* ids:调用指定ID的一个或多个数据,如 1,2,3
* cid:数据所在分类,可调出一个或多个分类数据,如 1,2,3 默认值为全部,在当前分类为:'.$cid.'
* field:调用影视类的指定字段,如(id,title,actor) 默认全部
* limit:数据条数,默认值为10,可以指定从第几条开始,如3,8(表示共调用8条,从第3条开始)
* order:推荐方式(id/addtime/hits/year/up/down) (desc/asc/rand())
* wd:'关键字' 用于调用自定义关键字(搜索/标签)结果
* serial:调用连载信息(all/数字) 全部连载值为all 其它数字为大于该数字的连载影片
* time: 指定上传时间内,如(1/7/30) 分别表示(当天/本周/本月)------未做好
* stars:推荐星级数,可调出一个或多个星级数据,如 1,2,3 默认值为全部
* hits:大于指定人气值的数据(如:888)或某段之间的(如:888,999)
* up:大于指定支持值的数据(如:888)或某段之间的(如:888,999)
* down:大于指定反对值的数据(如:888)或某段之间的(如:888,999)
* gold:大于指定评分平均值的数据(如:6)或某段之间的(如:1,8)/范围:0-10
* golder:大于指定评分人的数据(如:888)或某段之间的(如:888,999)
*/
function ff_mysql_vod($tag){
	$search = array();$where = array();
	$tag = ff_param_lable($tag);
	$field = !empty($tag['field']) ? $tag['field'] : '*';
	$limit = !empty($tag['limit']) ? $tag['limit'] : '10';
	$order = !empty($tag['order']) ? $tag['order'] : 'vod_addtime';
	//优先从缓存调用
	if(C('data_cache_vodforeach') && C('currentpage') < 2 ){
		$data_cache_name = md5(C('data_cache_foreach').implode(',',$tag));
		$data_cache_content = S($data_cache_name);
		if($data_cache_content){
			return $data_cache_content;
		}
	}
	//根据参数生成查询条件
	$where['vod_status'] = array('eq',1);	
	if (!empty($tag['ids'])) {
		$where['vod_id'] = array('in',$tag['ids']);
	}	
	if (!empty($tag['cid'])) {
		$cids = explode(',',trim($tag['cid']));
		if (count($cids)>1) {
			$where['vod_cid'] = array('in',getlistarr_tag($cids));
		}else{
			$where['vod_cid'] = getlistsqlin($tag['cid']);
		}
	}
	if (!empty($tag['day'])) {
		$where['vod_addtime'] = array('gt',getxtime($tag['day']));
	}
	if (!empty($tag['stars'])) {
		$where['vod_stars'] = array('in',$tag['stars']);
	}
	if (!empty($tag['letter'])) {
		$where['vod_letter'] = array('in',$tag['letter']);
	}
	if (!empty($tag['isfiml'])) {
		$where['vod_isfiml'] = array('eq',$tag['isfiml']);
	}	
	if (!empty($tag['area'])) {
		$where['vod_area'] = array('eq',''.$tag['area'].'');
	}
	if (!empty($tag['language'])) {
		$where['vod_language'] = array('eq',''.$tag['language'].'');
	}
	if (!empty($tag['lz']) && $tag['lz'] == 1) {
		$where['vod_continu'] = array('neq','0');
	} elseif (!empty($tag['lz']) && $tag['lz'] == 2){
		$where['vod_continu'] = 0;
	}	
	if (!empty($tag['year'])) {
		$year = explode(',',$tag['year']);
		if (count($year) > 1) {
			$where['vod_year'] = array('between',$year[0].','.$year[1]);
		}else{
			$where['vod_year'] = array('eq',$tag['year']);
		}
	}
	if (!empty($tag['hits'])) {
		$hits = explode(',',$tag['hits']);
		if (count($hits) > 1) {
			$where['vod_hits'] = array('between',$hits[0].','.$hits[1]);
		}else{
			$where['vod_hits'] = array('gt',$hits[0]);
		}
	}
	if (!empty($tag['up'])) {
		$up = explode(',',$tag['up']);
		if (count($up)>1) {
			$where['vod_up'] = array('between',$up[0].','.$up[1]);
		}else{
			$where['vod_up'] = array('gt',$up[0]);
		}
	}
	if (!empty($tag['down'])) {
		$down = explode(',',$tag['down']);
		if (count($down)>1) {
			$where['vod_down'] = array('between',$down[0].','.$down[1]);
		}else{
			$where['vod_down'] = array('gt',$down[0]);
		}
	}
	if (!empty($tag['gold'])) {
		$gold = explode(',',$tag['gold']);
		if (count($gold)>1) {
			$where['vod_gold'] = array('between',$gold[0].','.$gold[1]);
		}else{
			$where['vod_gold'] = array('gt',$gold[0]);
		}
	}
	if (!empty($tag['golder'])) {
		$golder = explode(',',$tag['golder']);
		if (count($golder)>1) {
			$where['vod_golder'] = array('between',$golder[0].','.$golder[1]);
		}else{
			$where['vod_golder'] = array('gt',$golder[0]);
		}
	}
	if (!empty($tag['name'])) {
		$where['vod_name'] = array('like','%'.$tag['name'].'%');
	}
	if (!empty($tag['title'])) {
		$where['vod_title'] = array('like','%'.$tag['title'].'%');
	}
	if (!empty($tag['actor'])) {
		$where['vod_actor'] = array('like','%'.$tag['actor'].'%');
	}
	if (!empty($tag['director'])) {
		$where['vod_director'] = array('like','%'.$tag['director'].'%');
	}
	if (!empty($tag['play'])) {
		$where['vod_play'] = array('eq',$tag['play']);
	}
	if (!empty($tag['inputer'])) {
		$where['vod_inputer'] = array('eq',$tag['inputer']);
	}	
	if (!empty($tag['wd'])) {
		$search['vod_name'] = array('like','%'.$tag['wd'].'%');
		$search['vod_title'] = array('like','%'.$tag['wd'].'%');
		$search['vod_actor'] = array('like','%'.$tag['wd'].'%');
		$search['vod_director'] = array('like','%'.$tag['wd'].'%');
		$search['_logic'] = 'or';
		$where['_complex'] = $search;		
	}
	//查询数据开始
	if(!empty($tag['tag'])){//视图模型查询
		$where['tag_sid'] = 1;
		$where['tag_name'] = $tag['tag'];
		$rs = D('TagView');
	}else{
		$rs = M('Vod');
	}
	if(!empty($tag['page'])){
		//组合分页信息
		$count = $rs->where($where)->count('vod_id');if(!$count){return false;}
		$totalpages = ceil($count/$limit);
		$currentpage = get_maxpage(C('currentpage'),$totalpages);
		//生成分页列表
		//$pageurl = ff_list_url('vod',C('jumpurl'),9999);
		$pageurl = C('jumpurl');
		$pages = getPageIndex($currentpage,$totalpages,C('home_pagenum'),$pageurl,'pagego(\''.$pageurl.'\','.$totalpages.')');
		//数据列表
		$list = $rs->field($field)->where($where)->order($order)->limit($limit)->page($currentpage)->select();
		$list[0]['count'] = count($list);
		$list[0]['page'] = $pages;						
	}else{
		$list = $rs->field($field)->where($where)->order($order)->limit($limit)->select();
	}
	//dump($rs->getLastSql());
	//循环赋值
    if(!empty($list)) {
        foreach($list as $key=>$val){
            $list[$key]['list_id'] = $list[$key]['vod_cid'];
            $list[$key]['list_name'] = getlistname($list[$key]['list_id'],'list_name');
            $list[$key]['list_url'] = getlistname($list[$key]['list_id'],'list_url');
            $list[$key]['vod_readurl'] = ff_data_url('vod',$list[$key]['vod_id'],$list[$key]['vod_cid'],$list[$key]['vod_name'],1,$list[$key]['vod_jumpurl']);
            $list[$key]['vod_playurl'] = ff_play_url($list[$key]['vod_id'],0,1,$list[$key]['vod_cid'],$list[$key]['vod_name']);
            $list[$key]['vod_picurl'] = ff_img_url($list[$key]['vod_pic'],$list[$key]['vod_content']);
            $list[$key]['vod_picurl_small'] = ff_img_url_small($list[$key]['vod_pic'],$list[$key]['vod_content']);		
        }
    }
	//是否写入数据缓存
	if(C('data_cache_vodforeach') && C('currentpage') < 2 ){
		S($data_cache_name,$list,intval(C('data_cache_vodforeach')));
	}
	return $list;
}
/*数据调用-小说资讯标签*/
function ff_mysql_news($tag){
	$search = array();$where = array();
	$tag = ff_param_lable($tag);
	$field = !empty($tag['field']) ? $tag['field'] : '*';
	$limit = !empty($tag['limit']) ? $tag['limit'] : '10';
	$order = !empty($tag['order']) ? $tag['order'] : 'news_addtime';
	//优先从缓存调用
	if(C('data_cache_newsforeach') && C('currentpage') < 2 ){
		$data_cache_name = md5(C('data_cache_foreach').implode(',',$tag));
		$data_cache_content = S($data_cache_name);
		if($data_cache_content){
			return $data_cache_content;
		}
	}	
	//根据参数生成查询条件
	$where['news_status'] = array('eq',1);	
	if (!empty($tag['ids'])) {
		$where['news_id'] = array('in',$tag['ids']);
	}
	if (!empty($tag['day'])) {
		$where['news_addtime'] = array('gt',getxtime($tag['day']));
	}	
	if (!empty($tag['hits'])) {
		$hits = explode(',',$tag['hits']);
		if (count($hits) > 1) {
			$where['news_hits'] = array('between',$hits[0].','.$hits[1]);
		}else{
			$where['news_hits'] = array('gt',$hits[0]);
		}
	}		
	if (!empty($tag['cid'])) {
		$cids = explode(',',trim($tag['cid']));
		if (count($cids)>1) {
			$where['news_cid'] = array('in',getlistarr_tag($cids));
		}else{
			$where['news_cid'] = getlistsqlin($tag['cid']);
		}
	}
	if (!empty($tag['stars'])) {
		$where['news_stars'] = array('in',$tag['stars']);
	}
	if (!empty($tag['letter'])) {
		$where['news_letter'] = array('in',$tag['letter']);
	}
	if (!empty($tag['name'])) {
		$where['news_name'] = array('like','%'.$tag['name'].'%');
	}	
	if (!empty($tag['title'])) {
		$where['news_title'] = array('like','%'.$tag['title'].'%');
	}			
	if (!empty($tag['wd'])) {
		$search['news_name'] = array('like','%'.$tag['wd'].'%');
		$search['news_inputer'] = array('like','%'.$tag['wd'].'%');
		$search['_logic'] = 'or';
		$where['_complex'] = $search;
	}
	if (!empty($tag['up'])) {
		$up = explode(',',$tag['up']);
		if (count($up)>1) {
			$where['news_up'] = array('between',$up[0].','.$up[1]);
		}else{
			$where['news_up'] = array('gt',$up[0]);
		}
	}
	if (!empty($tag['down'])) {
		$down = explode(',',$tag['down']);
		if (count($down)>1) {
			$where['news_down'] = array('between',$down[0].','.$down[1]);
		}else{
			$where['news_down'] = array('gt',$down[0]);
		}
	}
	if (!empty($tag['gold'])) {
		$gold = explode(',',$tag['gold']);
		if (count($gold)>1) {
			$where['news_gold'] = array('between',$gold[0].','.$gold[1]);
		}else{
			$where['news_gold'] = array('gt',$gold[0]);
		}
	}
	if (!empty($tag['golder'])) {
		$golder = explode(',',$tag['golder']);
		if (count($golder)>1) {
			$where['news_golder'] = array('between',$golder[0].','.$golder[1]);
		}else{
			$where['news_golder'] = array('gt',$golder[0]);
		}
	}
	//查询数据开始
	if(!empty($tag['tag'])){//视图模型查询
		$where['tag_sid'] = 2;
		$where['tag_name'] = $tag['tag'];
		$rs = D('TagnewsView');
	}else{
		$rs = M('News');
	}
	if(!empty($tag['page'])){
		//组合分页信息
		$count = $rs->where($where)->count('news_id');if(!$count){return false;}
		$totalpages = ceil($count/$limit);
		$currentpage = get_maxpage(C('currentpage'),$totalpages);
		//生成分页列表
		$pageurl = C('jumpurl');
		$pages = '共'.$count.'篇资讯&nbsp;当前:'.$currentpage.'/'.$totalpages.'页&nbsp;'.getPageIndex($currentpage,$totalpages,C('home_pagenum'),$pageurl,'pagego(\''.$pageurl.'\','.$totalpages.')');
		//数据列表
		$list = $rs->field($field)->where($where)->order($order)->limit($limit)->page($currentpage)->select();
		$list[0]['count'] = count($list);
		$list[0]['page'] = $pages;						
	}else{
		$list = $rs->field($field)->where($where)->order($order)->limit($limit)->select();
	}
	//循环赋值
    if(!empty($list)){
	    foreach($list as $key=>$val){
		    $list[$key]['list_id'] = $list[$key]['news_cid'];
		    $list[$key]['list_name'] = getlistname($list[$key]['list_id'],'list_name');
		    $list[$key]['list_url'] = getlistname($list[$key]['list_id'],'list_url');
		    $list[$key]['news_readurl'] = ff_data_url('news',$list[$key]['news_id'],$list[$key]['news_cid'],$list[$key]['news_name'],1,$list[$key]['news_jumpurl']);
	    	$list[$key]['news_picurl'] = ff_img_url($list[$key]['news_pic'],$list[$key]['news_content']);
		    $list[$key]['news_picurl_small'] = ff_img_url_small($list[$key]['news_pic'],$list[$key]['news_content']);		
	    }
    }
	//是否写入数据缓存
	if(C('data_cache_newsforeach') && C('currentpage') < 2 ){
		S($data_cache_name,$list,intval(C('data_cache_newsforeach')));
	}	
	return $list;
}
/*数据调用-专题循环标签*/
function ff_mysql_special($tag){
	$search = array();$where = array();
	$tag = ff_param_lable($tag);
	$field = !empty($tag['field']) ? $tag['field'] : '*';
	$limit = !empty($tag['limit']) ? $tag['limit'] : '10';
	$order = !empty($tag['order']) ? $tag['order'] : 'special_addtime';
	//优先从缓存调用
	if(C('data_cache_specialforeach') && C('currentpage') < 2 ){
		$data_cache_name = md5(C('data_cache_foreach').implode(',',$tag));
		$data_cache_content = S($data_cache_name);
		if($data_cache_content){
			return $data_cache_content;
		}
	}	
	//根据参数生成查询条件
	$where['special_status'] = array('eq',1);	
	if (!empty($tag['ids'])) {
		$where['special_id'] = array('in',$tag['ids']);
	}
	if (!empty($tag['hits'])) {
		$hits = explode(',',$tag['hits']);
		if (count($hits) > 1) {
			$where['special_hits'] = array('between',$hits[0].','.$hits[1]);
		}else{
			$where['special_hits'] = array('gt',$hits[0]);
		}
	}
	if (!empty($tag['name'])) {
		$where['special_name'] = array('like','%'.$tag['name'].'%');
	}
	//查询数据开始
	$rs = M('Special');
	if(!empty($tag['page'])){
		//组合分页信息
		$count = $rs->where($where)->count('special_id');if(!$count){return false;}
		$totalpages = ceil($count/$limit);
		$currentpage = get_maxpage(C('currentpage'),$totalpages);
		//生成分页列表
		$pageurl = C('jumpurl');
		$pages = getPageIndex($currentpage,$totalpages,C('home_pagenum'),$pageurl,'pagego(\''.$pageurl.'\','.$totalpages.')');
		//数据列表
		$list = $rs->field($field)->where($where)->order($order)->limit($limit)->page($currentpage)->select();
		$list[0]['count'] = count($list);
		$list[0]['page'] = $pages;						
	}else{
		$list = $rs->field($field)->where($where)->order($order)->limit($limit)->select();
	}
	//dump($rs->getLastSql());
	//循环赋值
	foreach($list as $key=>$val){
		$list[$key]['special_readurl'] = ff_data_url('special',$list[$key]['special_id'],0,$list[$key]['special_name'],1,'');
		$list[$key]['special_logo'] = ff_img_url($list[$key]['special_logo'],$list[$key]['special_content']);
		$list[$key]['special_banner'] = ff_img_url_small($list[$key]['special_banner'],$list[$key]['special_content']);		
	}
	//是否写入数据缓存
	if(C('data_cache_specialforeach') && C('currentpage') < 2 ){
		S($data_cache_name,$list,intval(C('data_cache_specialforeach')));
	}	
	return $list;
}
/*---------------------------------------ThinkPhp扩展函数库开始------------------------------------------------------------------
 * @category   Think
 * @package  Common
 * @author   liu21st <liu21st@gmail.com>*/
// 获取客户端IP地址
function get_client_ip(){
   if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
       $ip = getenv("HTTP_CLIENT_IP");
   else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
       $ip = getenv("HTTP_X_FORWARDED_FOR");
   else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
       $ip = getenv("REMOTE_ADDR");
   else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
       $ip = $_SERVER['REMOTE_ADDR'];
   else
       $ip = "unknown";
   return($ip);
}
//输出安全的html
function h($text, $tags = null){
	$text	=	trim($text);
	//完全过滤注释
	$text	=	preg_replace('/<!--?.*-->/','',$text);
	//完全过滤动态代码
	$text	=	preg_replace('/<\?|\?'.'>/','',$text);
	//完全过滤js
	$text	=	preg_replace('/<script?.*\/script>/','',$text);

	$text	=	str_replace('[','&#091;',$text);
	$text	=	str_replace(']','&#093;',$text);
	$text	=	str_replace('|','&#124;',$text);
	//过滤换行符
	$text	=	preg_replace('/\r?\n/','',$text);
	//br
	$text	=	preg_replace('/<br(\s\/)?'.'>/i','[br]',$text);
	$text	=	preg_replace('/(\[br\]\s*){10,}/i','[br]',$text);
	//过滤危险的属性，如：过滤on事件lang js
	while(preg_match('/(<[^><]+)( lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i',$text,$mat)){
		$text=str_replace($mat[0],$mat[1],$text);
	}
	while(preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i',$text,$mat)){
		$text=str_replace($mat[0],$mat[1].$mat[3],$text);
	}
	if(empty($tags)) {
		$tags = 'table|td|th|tr|i|b|u|strong|img|p|br|div|strong|em|ul|ol|li|dl|dd|dt|a';
	}
	//允许的HTML标签
	$text	=	preg_replace('/<('.$tags.')( [^><\[\]]*)>/i','[\1\2]',$text);
	//过滤多余html
	$text	=	preg_replace('/<\/?(html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|script|style|xml)[^><]*>/i','',$text);
	//过滤合法的html标签
	while(preg_match('/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i',$text,$mat)){
		$text=str_replace($mat[0],str_replace('>',']',str_replace('<','[',$mat[0])),$text);
	}
	//转换引号
	while(preg_match('/(\[[^\[\]]*=\s*)(\"|\')([^\2=\[\]]+)\2([^\[\]]*\])/i',$text,$mat)){
		$text=str_replace($mat[0],$mat[1].'|'.$mat[3].'|'.$mat[4],$text);
	}
	//过滤错误的单个引号
	while(preg_match('/\[[^\[\]]*(\"|\')[^\[\]]*\]/i',$text,$mat)){
		$text=str_replace($mat[0],str_replace($mat[1],'',$mat[0]),$text);
	}
	//转换其它所有不合法的 < >
	$text	=	str_replace('<','&lt;',$text);
	$text	=	str_replace('>','&gt;',$text);
	$text	=	str_replace('"','&quot;',$text);
	 //反转换
	$text	=	str_replace('[','<',$text);
	$text	=	str_replace(']','>',$text);
	$text	=	str_replace('|','"',$text);
	//过滤多余空格
	$text	=	str_replace('  ',' ',$text);
	return $text;
}
// 随机生成一组字符串
function build_count_rand ($number,$length=4,$mode=1) {
    if($mode==1 && $length<strlen($number) ) {
        //不足以生成一定数量的不重复数字
        return false;
    }
    $rand   =  array();
    for($i=0; $i<$number; $i++) {
        $rand[] = rand_string($length,$mode);
    }
    $unqiue = array_unique($rand);
    if(count($unqiue)==count($rand)) {
        return $rand;
    }
    $count   = count($rand)-count($unqiue);
    for($i=0; $i<$count*3; $i++) {
        $rand[] = rand_string($length,$mode);
    }
    $rand = array_slice(array_unique ($rand),0,$number);
    return $rand;
}
//产生随机字串，可用来自动生成密码  默认长度6位 字母和数字混合 支持中文
function rand_string($len=6,$type='',$addChars='') {
	$str ='';
	switch($type) {
		case 0:
			$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.$addChars;
			break;
		case 1:
			$chars= str_repeat('0123456789',3);
			break;
		case 2:
			$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ'.$addChars;
			break;
		case 3:
			$chars='abcdefghijklmnopqrstuvwxyz'.$addChars;
			break;
		case 4:
			$chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借".$addChars;
			break;
		default :
			// 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
			$chars='ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'.$addChars;
			break;
	}
	if($len>10 ) {//位数过长重复字符串一定次数
		$chars= $type==1? str_repeat($chars,$len) : str_repeat($chars,5);
	}
	if($type!=4) {
		$chars   =   str_shuffle($chars);
		$str     =   substr($chars,0,$len);
	}else{
		// 中文随机字
		for($i=0;$i<$len;$i++){
		  $str.= msubstr($chars, floor(mt_rand(0,mb_strlen($chars,'utf-8')-1)),1);
		}
	}
	return $str;
}
//XSS漏洞过滤
function remove_xss($val) {
   $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
   $search = 'abcdefghijklmnopqrstuvwxyz';
   $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
   $search .= '1234567890!@#$%^&*()';
   $search .= '~`";:?+/={}[]-_|\'\\';
   for ($i = 0; $i < strlen($search); $i++) {
      $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
      $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
   }
   $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
   $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
   $ra = array_merge($ra1, $ra2);
   $found = true; // keep replacing as long as the previous round replaced something
   while ($found == true) {
      $val_before = $val;
      for ($i = 0; $i < sizeof($ra); $i++) {
         $pattern = '/';
         for ($j = 0; $j < strlen($ra[$i]); $j++) {
            if ($j > 0) {
               $pattern .= '(';
               $pattern .= '(&#[xX]0{0,8}([9ab]);)';
               $pattern .= '|';
               $pattern .= '|(&#0{0,8}([9|10|13]);)';
               $pattern .= ')*';
            }
            $pattern .= $ra[$i][$j];
         }
         $pattern .= '/i';
         $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
         $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
         if ($val_before == $val) {
            // no replacements were made, so exit the loop
            $found = false;
         }
      }
   }
   return $val;
}
/*** 把返回的数据集转换成Tree
 +----------------------------------------------------------
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array 
 */
function list_to_tree($list, $pk='id',$pid = 'pid',$child = '_child',$root=0)
{
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            }else{
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}
/**----------------------------------------------------------
 * 在数据列表中搜索
 +----------------------------------------------------------
 * @param array $list 数据列表
 * @param mixed $condition 查询条件
 * 支持 array('name'=>$value) 或者 name=$value
 * @return array
 */
function list_search($list,$condition) {
    if(is_string($condition))
        parse_str($condition,$condition);
    // 返回的结果集合
    $resultSet = array();
    if(!empty($list)) {
        foreach ($list as $key=>$data){
            $find   =   false;
            foreach ($condition as $field=>$value){
                if(isset($data[$field])) {
                    if(0 === strpos($value,'/')) {
                        $find   =   preg_match($value,$data[$field]);
                    }elseif($data[$field]==$value){
                        $find = true;
                    }
                }
            }
        if($find) { $resultSet[]     =   &$list[$key]; }
        }
    }
    return $resultSet;
}
/**
 +----------------------------------------------------------
 * 字节格式化 把字节数格式为 B K M G T 描述的大小
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function byte_format($size, $dec=2)
{
	$a = array("B", "KB", "MB", "GB", "TB", "PB");
	$pos = 0;
	while ($size >= 1024) {
		 $size /= 1024;
		   $pos++;
	}
	return round($size,$dec)." ".$a[$pos];
}
/**
 +----------------------------------------------------------
 * 对查询结果集进行排序
 +----------------------------------------------------------
 * @access public
 +----------------------------------------------------------
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param array $sortby 排序类型
 * asc正向排序 desc逆向排序 nat自然排序
 +----------------------------------------------------------
 * @return array
 +----------------------------------------------------------
 */
function list_sort_by($list,$field, $sortby='asc') {
   if(is_array($list)){
       $refer = $resultSet = array();
       foreach ($list as $i => $data)
           $refer[$i] = &$data[$field];
       switch ($sortby) {
           case 'asc': // 正向排序
                asort($refer);
                break;
           case 'desc':// 逆向排序
                arsort($refer);
                break;
           case 'nat': // 自然排序
                natcasesort($refer);
                break;
       }
       foreach ( $refer as $key=> $val)
           $resultSet[] = &$list[$key];
       return $resultSet;
   }
   return false;
}
?>
