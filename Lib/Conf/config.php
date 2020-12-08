<?php 
if(is_file(RUNTIME_PATH.'Conf/config.php')){
	$config = require RUNTIME_PATH.'Conf/config.php';
}else{
	$config = array();
}
$array = array(
	'USER_AUTH_KEY'=>'ffvod',// 用户认证SESSION标记
	'NOT_AUTH_ACTION'=>'index,show,add,top,left,main',// 默认无需认证操作
	'REQUIRE_AUTH_MODULE'=>'Admin,List,Vod,News,User,Data,Upload,Link,Ads,Cache,Create,Tpl,Cm,Gb,Tag,Special,Nav,Side,Pic',// 默认需要认证模型
    'URL_PATHINFO_DEPR'=>'-',
	'APP_GROUP_LIST'=>'Admin,Home,Plus,User',//项目分组
	'TMPL_FILE_DEPR'=>'_',//模板文件MODULE_NAME与ACTION_NAME之间的分割符，只对项目分组部署有效
	'LANG_SWITCH_ON'=>true,// 多语言包功能
	'LANG_AUTO_DETECT'=>false,//是否自动侦测浏览器语言
	'URL_CASE_INSENSITIVE'=>true,//URL是否不区分大小写 默认区分大小写
    'DB_FIELDTYPE_CHECK'=>true, //是否进行字段类型检查
	'DATA_CACHE_SUBDIR'=>true,//哈希子目录动态缓存的方式
    'TMPL_ACTION_ERROR'     => './Public/jump/jumpurl.html', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   => './Public/jump/jumpurl.html', // 默认成功跳转对应的模板文件		
	'DATA_PATH_LEVEL'=>2,
  	'url_model' => '3',
	'play_player' =>array (
		'm3u8'=>    array('00','CKPlayer'),
	),
    //'APP_DEBUG'         => true,   // 是否开启调试模式
    //'SHOW_RUN_TIME'     => true,   // 运行时间显示
    //'SHOW_ADV_TIME'     => true,   // 显示详细的运行时间
    //'SHOW_DB_TIMES'     => true,   // 显示数据库查询和写入次数	
);
return array_merge($config,$array);
?>