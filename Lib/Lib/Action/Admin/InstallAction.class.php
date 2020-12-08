<?php
class InstallAction extends Action{	
    public function _initialize() {
		if(is_file(RUNTIME_PATH.'Install/install.lock')){
			$this->assign("waitSecond",60);
			$this->error('Sorry，您已经安装了影视系统重新安装请<br />先删除 Runtime/install/install.lock 文件。');
		}
    }
    public function index(){
        $this->display('./Public/system/install.html');
    }
    public function second(){
        $this->display('./Public/system/install.html');
    }
    public function setup(){
        $this->display('./Public/system/install.html');
    }
    public function install(){
		$data = getWDSrt($_POST['data']);
		$rs = @mysqli_connect($data['db_host'].":".intval($data['db_port']),$data['db_user'],$data['db_pwd']);
		if(!$rs){
			$this->error(L('install_db_connect'));	
		}
		// 数据库不存在,尝试建立
		if(!@mysqli_select_db($rs,$data['db_name'])){
			$sql = "CREATE DATABASE `".$data["db_name"]."` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
			mysqli_query($rs,$sql);
		}
		// 建立不成功
		if(!@mysqli_select_db($rs,$data['db_name'])){
			$this->error(L('install_db_select'));
		}
		// 保存配置文件
		$config = array(
		    'site_path'=>$data['site_path'],
            'db_type' => 'mysqli',
			'db_host'=>$data['db_host'],
			'db_name'=>$data['db_name'],
			'db_user'=>$data['db_user'], 
			'db_pwd'=>$data['db_pwd'],
			'db_port'=>$data['db_port'],
			'db_prefix'=>$data['db_prefix'],
            'play_video_encrypt'=>md5(uniqid()),
		);
		$config_old = array (
			'db_type' => 'mysqli',
			'db_host' => '',
			'db_name' => '',
			'db_user' => '',
			'db_pwd' => '',
			'db_port' => '',
			'db_prefix' => '',
			'db_charset' => 'utf8',
			'default_theme' => 'defalut',
			'site_name' => '第三视频',
			'site_path' => '/',
			'site_url' => 'http://localhost/',
			'site_keywords' => '最新电影,好看的电影,在线观看,在线电影,高清电影',
			'site_description' => '专注于电影、电视剧、综艺节目在线观看的视频网站;第三视频提供豆瓣高分电影、热播电视剧、综艺节目大全等免费在线观看;最新好看的电影、热播电视剧、综艺节目等值得大家关注收藏!',
			'site_email' => '110119@qq.com',
			'site_copyright' => '免则声明:本站只提供WEB页面服务，本站不存储、不制作任何视频，不承担任何由于内容的合法性及健康性所引起的争议和法律责任。<br />若本站收录内容侵犯了您的权益，请附说明联系邮箱，本站将第一时间处理。',
			'site_tongji' => '',
			'site_icp' => 'ICP备20201111号',
			'url_html_suffix' => '.html',
			'url_num_admin' => '18',
			'admin_time_edit' => true,
			'admin_ads_file' => 'Runtime/Js',
			'admin_order_type' => 'addtime',
			'admin_time_limit' => '300',
			'home_pagenum' => '3',
			'home_pagego' => 'pagego',
			'rewrite_vodlist' => '/vod-show-id-$id-p-$page',
			'rewrite_voddetail' => '/vod-read-id-$id',
			'rewrite_vodplay' => '/vod-play-id-$id-sid-$sid-pid-$pid',
			'rewrite_vodsearch' => '/vod-search-wd-$wd$actor$director$area$language$year-p-$page',
			'rewrite_vodtype' => '/vod-type-id-$id-wd-$wd-letter-$letter-year-$year-area-$area-order-$order-p-$page',
			'rewrite_vodtag' => '/tag-vod-wd-$wd-p-$page',
			'rewrite_newslist' => '/news-show-id-$id-p-$page',
			'rewrite_newsdetail' => '/news-read-id-$id',
			'rewrite_newssearch' => '/news-search-wd-$wd-p-$page',
			'rewrite_newstag' => '/tag-news-wd-$wd-p-$page',
			'rewrite_speciallist' => '/special-show-p-$page',
			'rewrite_specialdetail' => '/special-read-id-$id',
			'rewrite_guestbook' => '/gb-show-p-$page',
			'rewrite_sitemap' => '/map-sitemap-id-$id',
			'rewrite_map' => '/map-show-id-$id-limit-$limit-p-$page',
			'rewrite_mytpl' => '/my-show-id-$id',
			'data_cache_type' => 'file',
			'data_cache_vod' => '0',
			'data_cache_news' => '0',
			'data_cache_special' => '0',
			'data_cache_foreach' => '5eae81ebeced5',
			'data_cache_vodforeach' => '0',
			'data_cache_newsforeach' => '0',
			'data_cache_specialforeach' => '0',
			'tmpl_cache_on' => false,
			'html_cache_on' => false,
			'html_cache_time' => 0,
			'html_read_type' => 0,
			'html_file_suffix' => '.html',
			'html_cache_index' => '1',
			'html_cache_list' => '1.5',
			'html_cache_content' => '12',
			'html_cache_play' => '12',
			'html_cache_iframe' => '12',
			'html_cache_ajax' => '12',
			'_htmls_' => 
			array (
			  'home:index:index' => 
			  array (
				0 => '{:action}',
				1 => 3600,
			  ),
			  'home:vod:show' => 
			  array (
				0 => '{:module}_{:action}/{$_SERVER.REQUEST_URI|md5}',
				1 => 5400,
			  ),
			  'home:news:show' => 
			  array (
				0 => '{:module}_{:action}/{$_SERVER.REQUEST_URI|md5}',
				1 => 5400,
			  ),
			  'home:vod:read' => 
			  array (
				0 => '{:module}_{:action}/{id|md5}',
				1 => 43200,
			  ),
			  'home:news:read' => 
			  array (
				0 => '{:module}_{:action}/{$_SERVER.REQUEST_URI|md5}',
				1 => 43200,
			  ),
			  'home:vod:play' => 
			  array (
				0 => '{:module}_{:action}/{$_SERVER.REQUEST_URI|md5}',
				1 => 43200,
			  ),
			  'home:my:show' => 
			  array (
				0 => '{:module}_{:action}/{$_SERVER.REQUEST_URI|md5}',
				1 => 43200,
			  ),
			),
			'upload_path' => 'Uploads',
			'upload_style' => 'Y-m-d',
			'upload_class' => 'jpg,gif,png,jpeg',
			'upload_thumb' => false,
			'upload_thumb_w' => '120',
			'upload_thumb_h' => '140',
			'upload_water' => false,
			'upload_water_img' => '',
			'upload_water_pct' => '75',
			'upload_water_pos' => '9',
			'upload_http' => false,
			'upload_http_down' => '5',
			'upload_http_prefix' => '',
			'upload_ftp' => false,
			'upload_ftp_host' => '',
			'upload_ftp_user' => '',
			'upload_ftp_pass' => '',
			'upload_ftp_port' => '',
			'upload_ftp_dir' => '',
			'play_show' => '1',
			'play_width' => '640',
			'play_height' => '480',
			'play_second' => 10,
			'play_language' => '国语,英语,粤语,闽南语,韩语,日语,国语/粤语,其它',
			'play_area' => '大陆,香港,台湾,美国,韩国,日本,其它',
			'play_year' => '2020,2019,2018,2017,2016,2015',
			'play_server' => 
			array (
			  'down_a' => 'http://downa.video.com/',
			  'down_b' => 'http://downb.video.com/',
			),
			'play_urllist' => '1',
			'play_collect_time' => '2',
			'play_collect_name' => '0',
			'play_collect' => true,
			'play_video_encrypt' => '2b85ce1f3b2a442a92b072bf43e36d7f',
			'url_html' => '0',
			'url_dir_a' => '2',
			'url_dir_b' => '5',
			'url_time' => '2',
			'url_number' => '20',
			'url_vodlist' => 'html/{listdir}/index.html',
			'url_voddata' => 'html/{listdir}/{id}/index.html',
			'url_vodplay' => 'vod/player/',
			'url_newslist' => 'html/{listdir}/index.html',
			'url_newsdata' => 'html/{listdir}/{id}/index.html',
			'url_play' => 'html/{listdir}/{id}/play.html',
			'url_html_list' => '1',
			'url_html_play' => '1',
			'url_rewrite' => '0',
			'url_map' => 'html/detail/',
			'url_mytpl' => 'html/detail/',
			'url_special' => 'html/detail/',
			'user_second' => '600',
			'user_replace' => '她妈|它妈|他妈|你妈|去死|贱人',
			'user_gbnum' => '10',
			'user_cmnum' => '10',
			'user_vcode' => '1',
			'user_register' => '0',
			'html_home_suffix' => '.html',
			'upload_ftp_del' => '0',
			'rand_hits' => '9',
			'rand_updown' => '9',
			'rand_gold' => '9',
			'rand_golder' => '9',
			'rand_tag' => '1',
			'user_post' => '0',
			'user_check' => '1',
		);
		$config_new = array_merge($config_old,$config);
		arr2file(RUNTIME_PATH.'Conf/config.php', $config_new);
		// 批量导入安装SQL
		$db_config = array(
			'dbms'=>'mysqli',
			'username'=>$data['db_user'],
			'password'=>$data['db_pwd'],
			'hostname'=>$data['db_host'],
			'hostport'=>$data['db_port'],
			'database'=>$data['db_name']
		);	
		$sql = read_file(RUNTIME_PATH.'Install/install.sql');
		$sql = str_replace('ff_',$data['db_prefix'],$sql);
		$this->batQuery($sql,$db_config);
		touch(RUNTIME_PATH.'Install/install.lock');
		if(is_file(RUNTIME_PATH.'~app.php')){@unlink(RUNTIME_PATH.'~app.php');}
		if(is_file(RUNTIME_PATH.'~runtime.php')){@unlink(RUNTIME_PATH.'~runtime.php');}
		$this->assign("jumpUrl",'./admin.php');
		$this->success(L('install_success'));
    }
	public function batQuery($sql,$db_config){
	    // 连接数据库
		require THINK_PATH.'/Lib/Think/Db/Driver/DbMysqli.class.php';
		$db = new Dbmysqli($db_config);
		$sql = str_replace("\r\n", "\n", $sql); 
		foreach(explode(";\n", trim($sql)) as $id => $query){
			$queries = explode("\n", trim($query)); 
            $ret[$id] = '';
			foreach($queries as $query) { 
				$ret[$id] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query; 
			} 
		} 
		unset($sql); 
		foreach($ret as $query) {  
			if(trim($query)) { 
			    $db->query($query); 
			} 
		} 
		return true; 
	}								
}
?>