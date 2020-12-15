<?php
class CreateAction extends BaseAction{
	//构造函数
    public function _initialize(){
	    parent::_initialize();
		$this->assign($this->Lable_Style());
		$this->assign("waitSecond",C('url_time'));
    }
	//组合需要生成的资讯列表并缓存
    public function newslist(){
		$this->list_cache_array(2);
	}
	//生成资讯列表从缓存任务列表
    public function newslist_create(){
		$this->list_create_array(2);
	}
	//组合需要生成的视频列表并缓存
    public function vodlist(){
		$this->list_cache_array(1);
	}
	//生成视频列表从缓存任务列表
    public function vodlist_create(){
		$this->list_create_array(1);
	}
	//缓存视频/资讯列表列表路径为数组
	public function list_cache_array($sid=1){
		$jump = !empty($_REQUEST['jump'])?intval($_REQUEST['jump']):0;
		$this->check(C('url_html_list'),getsidname($sid).'列表页','?s=Admin-Create-Index-jump-'.$jump);
		$array_list = F('_ppvod/list');
		$array_listids = explode(',',getWDSrt($_REQUEST['ids_list_'.$sid]));
		$k = 0;
		foreach($array_listids as $key=>$value){
			$list = list_search($array_list,'list_id='.$value);
			if(!empty($list[0]["list_limit"])){
				$totalpages = ceil(getcount($value)/$list[0]["list_limit"]);
			}else{
				$totalpages = 1;
			}
			for($page = 1; $page <= $totalpages; $page++){
				$array[$k]['id'] = $value;
				$array[$k]['page'] = $page;
				$k++;
			}
		}
		F('_create/list',!empty($array)?$array:'');
		if($sid == 1){
			$this->vodlist_create();
		}else{
			$this->newslist_create();
		}
	}
	//生成视频/资讯列表列表从缓存数组(分页生成)
    public function list_create_array($sid){
		$List = F('_ppvod/list');
		$Url = F('_create/list');
		$key = !empty($_REQUEST['key'])?intval($_REQUEST['key']):0;
		$jump = !empty($_REQUEST['jump'])?intval($_REQUEST['jump']):0;
		if($sid==1){
			$nextcreate = '?s=Admin-Create-Vodlist_create-jump-'.$jump.'-key-'.$key;
			$sidname = '视频';
		}else{
			$nextcreate = '?s=Admin-Create-Newslist_create-jump-'.$jump.'-key-'.$key;
			$sidname = '资讯';
		}
		//断点生成(写入缓存)
		F('_create/nextcreate',$nextcreate);
		echo'<ul id="show" style="font-size:12px;list-style:none;margin:0px;padding:0px;font-family:宋体">';
		echo'<li>总共需要生成<font color=blue>'.(!empty($Url)?count($Url):0).'</font>个'.$sidname.'列表页，每页生成'.C('url_number').'个。</li>';
		for($i=1;$i<=C('url_number');$i++){
			if(empty($Url[$key])){
				break;
			}
			//变量赋值
			C('jumpurl',ff_list_url(getsidname($sid),array('id'=>$Url[$key]['id'],'p'=>'{!page!}'),9999));
			C('currentpage',$Url[$key]['page']);
			$channel = list_search($List,'list_id='.$Url[$key]['id']);
			if($sid == 1){
				$channel = $this->Lable_Vod_List(array('id'=>$Url[$key]['id'],'page'=>$Url[$key]['page'],'order'=>'addtime'),(!empty($channel[0])?$channel[0]:''));
			}else{
				$channel = $this->Lable_News_List(array('id'=>$Url[$key]['id'],'page'=>$Url[$key]['page'],'order'=>'addtime'),(!empty($channel[0])?$channel[0]:''));
			}
			$this->assign($channel);
			//目录路径并生成
			$listdir = str_replace('{!page!}',$Url[$key]['page'],ff_list_url_dir(getsidname($sid),$Url[$key]['id'],$Url[$key]['page']));
			$this->buildHtml($listdir,'./','Home:'.$channel['list_skin']);	
			//预览路径
			$showurl = C('sitepath').$listdir.C('html_file_suffix');
			echo'<li>第<font color=red>'.($key+1).'</font>个生成完毕　<a href="'.$showurl.'" target="_blank">'.$showurl.'</a></li>';
			$this->echo_ob_flush();
			$key++;
		}
		echo'</ul>';
		if(!empty($Url)&&$key < count($Url)){
			if($sid==1){
				$nextcreate = '?s=Admin-Create-Vodlist_create-jump-'.$jump.'-key-'.$key;
			}else{
				$nextcreate = '?s=Admin-Create-Newslist_create-jump-'.$jump.'-key-'.$key;
			}		
			$this->jump($nextcreate,'让服务器休息一会，生成任务等待中...');
		}
		F('_create/list',NULL);
		if($jump){
			$this->jump('?s=Admin-Create-Index-jump-'.$jump,'列表页已经全部生成，下次将生成网站首页。');
		}
		F('_create/nextcreate',NULL);
		$this->jump('?s=Admin-Create-Show','恭喜您，列表页已经全部生成！');
	}
	//读取资讯内容按分类
	public function newsclass(){
		$jump = !empty($_REQUEST['jump'])?intval($_REQUEST['jump']):0;
		$ids = trim(getWDSrt($_REQUEST['ids_2']));
		$page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
		//检测是否需要生成
		$this->check(C('url_html'),'资讯内容页','?s=Admin-Create-Newslist-ids_2-'.$ids.'-jump-'.$jump);
		//断点生成(写入缓存)
		F('_create/nextcreate','?s=Admin-Create-Vodclass-ids_2-'.$ids.'-jump-'.$jump.'-page-'.$page);
		//查询数据开始	
		$rs = D("News");
		$where['news_status'] = array('eq',1);
		$where['news_cid'] = array('in',$ids);
		$count = $rs->where($where)->count('news_id');
		$totalpages = ceil($count/C('url_number'));
		$array = $rs->where($where)->order('news_addtime desc')->limit(C('url_number'))->page($page)->relation('Tag')->select();
		if (empty($array)) {
			$this->assign("jumpUrl",'?s=Admin-Create-Show');
			$this->error('当前分类没有数据，暂不需要生成！');
		}
		echo'<ul id="show" style="font-size:12px;list-style:none;margin:0px;padding:0px;font-family:宋体">';
		echo'<li>总共需要生成<font color=blue>'.$count.'</font>个资讯内容页，需要分<font color=blue>'.$totalpages.'</font>次来执行，正在执行第<font color=red>'.$page.'</font>次</li>';
		foreach($array as $key=>$value){
			$this->news_read_create($value);
		}
		echo'</ul>';
		//组合回跳URL路径与执行跳转
		$jumpurl = '?s=Admin-Create-Show';
		if($page < $totalpages){
			$jumpurl = '?s=Admin-Create-Newsclass-ids_2-'.$ids.'-jump-'.$jump.'-page-'.($page+1);
		}else{
			if($jump){
				$jumpurl = '?s=Admin-Create-Newslist-ids_list_2-'.$ids.'-jump-'.$jump;
			}
		}
		if($page < $totalpages){
			$this->jump($jumpurl,'稍等一会，准备生成下一次资讯内容页...');
		}	
		F('_create/nextcreate',NULL);
		$this->jump($jumpurl,'恭喜您，资讯内容页全部生成完毕。');
	}
	//读取资讯内容按时间
	public function newsday(){
		$jump = !empty($_REQUEST['jump'])?intval($_REQUEST['jump']):0;
		$this->check(C('url_html'),'资讯内容页');
		//查询数据开始
		$rs = D("News");
		$mday = !empty($_REQUEST['mday_2'])?intval($_REQUEST['mday_2']):0;
		$min = !empty($_REQUEST['min'])?intval($_REQUEST['min']):0;
		$where['news_status'] = array('eq',1);
		$where['news_cid'] = array('gt',0);
		if($min){//生成多少分钟内的影片
			$where['news_addtime'] = array('gt',time()-$min*60);
		}else{
			$where['news_addtime'] = array('gt',getxtime($mday));
		}
		//
		$count = $rs->where($where)->count('news_id');
		$totalpages = ceil($count/C('url_number'));
		$page = !empty($_GET['page'])?intval($_GET['page']):1;
		$array = $rs->where($where)->order('news_addtime desc')->limit(C('url_number'))->page($page)->relation('Tag')->select();
		if (empty($array )) {
			$this->assign("jumpUrl",'?s=Admin-Create-Show');
			$this->error('今日没有数据更新,暂不需要生成！');
		}
		echo'<ul id="show" style="font-size:12px;list-style:none;margin:0px;padding:0px;font-family:宋体">';
		echo'<li>总共需要生成<font color=blue>'.$count.'</font>个资讯内容页，需要分<font color=blue>'.$totalpages.'</font>次来执行，正在执行第<font color=red>'.$page.'</font>次</li>';
		foreach($array as $key=>$value){
			$this->news_read_create($value);
		}
		echo'</ul>';
		//组合回跳URL路径与执行跳转
		$jumpurl = '?s=Admin-Create-Show';
		if ($page < $totalpages) {
			$jumpurl = '?s=Admin-Create-Newsday-jump-'.$jump.'-mday_2-'.$mday.'-min-'.$min.'-page-'.($page+1);
		}else{
			if($jump){
				$listarr = F('_ppvod/list');
				foreach($listarr as $key=>$value){
					$keynew = $value['list_sid'];
					$list[$keynew][$key] = $value['list_id'];
				}
				$ids = implode(',',$list[1]);
				$jumpurl = '?s=Admin-Create-Newslist-jump-1-ids_list_2-'.$ids;			
			}
		}
		if ($page < $totalpages) {
			$this->jump($jumpurl,'第('.$page.')页已经生成完毕，正在准备下一个。');
		}
		F('_create/nextcreate',NULL);
		$this->jump($jumpurl,'恭喜您，生成完毕。');
	}
	//读取资讯内容按ID
	public function newsid(){
		$where = array();
		$rs = D("News");
		$where['news_id'] = array('in',getWDSrt($_REQUEST["id"]));
		$where['news_status'] = 1;
		$where['news_cid'] = array('gt',0);
		$array = $rs->where($where)->relation('Tag')->select();
		foreach($array as $value){
			$this->news_read_create($value);
		}
	}		
	//生成资讯内容页
    public function news_read_create($array){
		$arrays = $this->Lable_News_Read($array);
		$this->assign($arrays['show']);
		$this->assign($arrays['read']);
		$newsdir = ff_data_url_dir('news',$arrays['read']['news_id'],$arrays['read']['news_cid'],$arrays['read']['news_name'],1);
		$this->buildHtml($newsdir,'./',$arrays['read']['news_skin_detail']);
		$newsurl = C('site_path').$newsdir.C('html_file_suffix');
		echo '<li>'.$arrays['read']['news_id'].' <a href="'.$newsurl.'" target="_blank">'.$newsurl.'</a> 生成完毕</li>';
		$this->echo_ob_flush();
    }
	//读取视频内容按分类
	public function vodclass(){
		$jump = !empty($_REQUEST['jump'])?intval($_REQUEST['jump']):0;
		$ids = !empty($_REQUEST['ids_1'])?trim(getWDSrt($_REQUEST['ids_1'])):'';
		$page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
		$this->check(C('url_html'),'视频内容页','?s=Admin-Create-Vodlist-ids_1-'.$ids.'-jump-'.$jump);
		//断点生成(写入缓存)
		F('_create/nextcreate','?s=Admin-Create-Vodclass-ids_1-'.$ids.'-jump-'.$jump.'-page-'.$page);	
		$rs = D("Vod");
		$where['vod_status'] = array('eq',1);
		$where['vod_cid'] = array('in',$ids);
		$count = $rs->where($where)->count('vod_id');
		$totalpages = ceil($count/C('url_number'));
		$array = $rs->where($where)->order('vod_addtime desc')->limit(C('url_number'))->page($page)->relation('Tag')->select();
		if (empty($array)) {
			$this->assign("jumpUrl",'?s=Admin-Create-Show');
			$this->error('当前分类没有数据，暂不需要生成！');
		}	
		echo'<ul id="show" style="font-size:12px;list-style:none;margin:0px;padding:0px;font-family:宋体"><li>总共需要生成<font color=blue>'.$count.'</font>个视频内容页，需要分<font color=blue>'.$totalpages.'</font>次来执行，正在执行第<font color=blue>'.$page.'</font>次</li>';
		foreach($array as $key=>$value){
			$this->vod_read_create($value);
		}
		echo'</ul>';
		//组合回跳URL路径与执行跳转
		$jumpurl = '?s=Admin-Create-Show';
		if($page < $totalpages){
			$jumpurl = '?s=Admin-Create-Vodclass-ids_1-'.$ids.'-jump-'.$jump.'-page-'.($page+1);
		}else{
			if($jump){
				$jumpurl = '?s=Admin-Create-Vodlist-ids_list_1-'.$ids.'-jump-'.$jump;
			}
		}
		if($page < $totalpages){
			$this->jump($jumpurl,'稍等一会，准备生成下一次视频内容页...');
		}
		F('_create/nextcreate',NULL);
		$this->jump($jumpurl,'恭喜您，视频内容页全部生成完毕。');
	}
	//读取视频内容按时间
	public function vodday(){
		$jump = !empty($_REQUEST['jump'])?intval($_REQUEST['jump']):0;
		$this->check(C('url_html'),'视频内容页');
		$rs = D("Vod");
		$mday = !empty($_REQUEST['mday_1'])?intval($_REQUEST['mday_1']):0;
		$min = !empty($_REQUEST['min'])?intval($_REQUEST['min']):0;
		$where['vod_status'] = array('eq',1);
		$where['vod_cid'] = array('gt',0);
		//
		if($min){//生成多少分钟内的影片
			$where['vod_addtime'] = array('gt',time()-$min*60);
		}else{
			$where['vod_addtime'] = array('gt',getxtime($mday));
		}
		//
		$count = $rs->where($where)->count('vod_id');
		$totalpages = ceil($count/C('url_number'));
		$page = !empty($_GET['page'])?intval($_GET['page']):1;
		$array = $rs->where($where)->order('vod_addtime desc')->limit(C('url_number'))->page($page)->relation('Tag')->select();
		if (empty($array )) {
			$this->assign("jumpUrl",'?s=Admin-Create-Show');
			$this->error('今日没有数据更新,暂不需要生成！');
		}
		echo'<ul id="show" style="font-size:12px;list-style:none;margin:0px;padding:0px;font-family:宋体"><li>总共需要生成<font color=blue>'.$count.'</font>个视频内容页，需要分<font color=blue>'.$totalpages.'</font>次来执行，正在执行第<font color=blue>'.$page.'</font>次</li>';
		foreach($array as $key=>$value){
			$this->vod_read_create($value);
		}
		echo'</ul>';
		if ($page < $totalpages) {
			$jumpurl = '?s=Admin-Create-Vodday-jump-'.$jump.'-mday_1-'.$mday.'-min-'.$min.'-page-'.($page+1);
		}else{
			if($jump){
				$listarr = F('_ppvod/list');
				foreach($listarr as $key=>$value){
					$keynew = $value['list_sid'];
					$list[$keynew][$key] = $value['list_id'];
				}
				$ids = implode(',',$list[1]);
				$jumpurl = '?s=Admin-Create-Vodlist-jump-1-ids_list_1-'.$ids;			
			}else{
				$jumpurl = '?s=Admin-Create-Show';
			}
		}
		if ($page < $totalpages) {
			$this->jump($jumpurl,'第('.$page.')页已经生成完毕，正在准备下一个。');
		}
		F('_create/nextcreate',NULL);
		$this->jump($jumpurl,'恭喜您，生成完毕。');
	}
	//读取影片内容按ID
	public function vodid(){
		$where = array();
		$rs = D("Vod");
		$where['vod_id'] = array('in',getWDSrt($_REQUEST["id"]));
		$where['vod_cid'] = array('gt',0);
		$where['vod_status'] = 1;
		$array = $rs->where($where)->relation('Tag')->select();
		foreach($array as $value){
			$this->vod_read_create($value);
		}
	}
	//生成影视内容页
    public function vod_read_create($array){
		$arrays = $this->Lable_Vod_Read($array);
		$this->assign($arrays['show']);
		$this->assign($arrays['read']);
		//生成内容页
		$videodir = ff_data_url_dir('vod',$arrays['read']['vod_id'],$arrays['read']['vod_cid'],$arrays['read']['vod_name'],1);
		$this->buildHtml($videodir,'./',$arrays['read']['vod_skin_detail']);
		echo('<li><a href="'.C('site_path').$videodir.C('html_file_suffix').'" target="_blank">'.$arrays['read']['vod_id'].'</a> detail ok</li>');
		//生成播放页
		if(C('url_html')){
			$this->vod_play_create($arrays);
		}
		$this->echo_ob_flush();
    }
	//生成播放页
	public function vod_play_create($arrays){
		//合集在一个页面
		if(C('url_html_play')==1){
			$this->assign($arrays['show']);
			$arrays['read'] = $this->Lable_Vod_Play($arrays['read'],array('id'=>$arrays['read']['vod_id'],'sid'=>0,'pid'=>1));
			$this->assign($arrays['read']);
			$playdir = ff_play_url_dir($arrays['read']['vod_id'],0,1,$arrays['read']['vod_cid'],$arrays['read']['vod_name']);
			$this->buildHtml($playdir,'./',$arrays['read']['vod_skin_play']);
			echo('<li>'.$arrays['read']['vod_id'].' play ok</li>');
			$this->echo_ob_flush();
		}elseif(C('url_html_play')==2){
			echo('<li>'.$arrays['read']['vod_id'].' play ');
			//生成播放地址js(只需要一次)
			$player_dir = ff_play_url_dir($arrays['read']['vod_id'],0,1,$arrays['read']['vod_cid'],$arrays['read']['vod_name']);
			write_file('./'.$player_dir.'.js',$arrays['read']['vod_player']);
			//播放页模板通用变量解析
			$this->assign($arrays['show']);
			//第一个分集生成完后
			foreach($arrays['read']['vod_playlist'] as $sid=>$son){
				$arr_sid = explode('-',$sid);
				$arr_play = array();
				foreach($son["son"] as $pid=>$value){
					//路径替换与生成
					$player_dir_ji = preg_replace('/play-([0-9]+)-([0-9]+)-([0-9]+)/i','play-$1-'.$arr_sid[1].'-'.($pid+1).'',$player_dir);
					$this->assign($this->Lable_Vod_Play($arrays['read'],array('id'=>$arrays['read']['vod_id'],'sid'=>$arr_sid[1],'pid'=>$pid+1),true));
					$this->buildHtml($player_dir_ji,'./',$arrays['read']['vod_skin_play']);
					$this->echo_ob_flush();
				}
				echo('ok </li>');
			}
		}
	}
	//一键生成全站地图
    public function maps(){
		$this->check(C('url_html'),'网站地图');
		$this->google(true, true);
		$this->assign("jumpUrl",'?s=Admin-Create-Show');
		$this->success('恭喜您，所有地图生成完毕！');	
	}
	//生成Baidu地图
    public function baidu($id = '', $pgid = ''){
		$googleall = !empty($_REQUEST['googleall'])?intval($_REQUEST['googleall']):5000;
		$google = !empty($_REQUEST['google'])?intval($_REQUEST['google']):1000;
		$baiduall = !empty($_REQUEST['baiduall'])?intval($_REQUEST['baiduall']):10000;
		$baidu = !empty($_REQUEST['baidu'])?intval($_REQUEST['baidu']):2000;
		$pgid = (!empty($_GET['page'])&&empty($pgid)?intval($_GET['page']):1);
		$page = ceil(intval($baiduall)/intval($baidu));
		$id = (!empty($_REQUEST['id'])||!empty($id)?'-id-1':'-id-0');
		if($pgid <= $page){
			$this->map_create('baidu',$baidu,$pgid);
			$this->jump('?s=Admin-Create-Baidu-google-'.$google.'-googleall-'.$googleall.'-baidu-'.$baidu.'-baiduall-'.$baiduall.'-page-'.($pgid+1).$id,'正在执行第('.$pgid.')页 Baidu Sitemap 地图生成成功！');
		}
		if (empty(substr($id, -1))) {
			$this->siteMap('baidu',$baidu);
			$this->assign("waitSecond",3);
			$this->assign("jumpUrl",'?s=Admin-Create-Show');
        	$this->success('Baidu Sitemap地图生成成功！<br />请通过<a href="https://ziyuan.baidu.com" target="_blank">百度站长平台</a>提交！');			
		} else {
			$this->rss(true);
		}
    }
	//生成Google地图
    public function google($id = '', $pgid = ''){
		$googleall = !empty($_REQUEST['googleall'])?intval($_REQUEST['googleall']):5000;
		$google = !empty($_REQUEST['google'])?intval($_REQUEST['google']):1000;
		$baiduall = !empty($_REQUEST['baiduall'])?intval($_REQUEST['baiduall']):10000;
		$baidu = !empty($_REQUEST['baidu'])?intval($_REQUEST['baidu']):2000;
		$pgid = (!empty($_GET['page'])&&empty($pgid)?intval($_GET['page']):1);
		$page = ceil(intval($googleall)/intval($google));
		$id = (!empty($_REQUEST['id'])||!empty($id)?'-id-1':'-id-0');
		if($pgid <= $page){
			$this->map_create('google',$google,$pgid);
			$this->jump('?s=Admin-Create-Google-google-'.$google.'-googleall-'.$googleall.'-baidu-'.$baidu.'-baiduall-'.$baiduall.'-page-'.($pgid+1).$id,'正在执行第('.$pgid.')页 Google Sitemap 地图生成成功！');
		}
		if (empty(substr($id, -1))) {
			$this->siteMap('google',$google);
			$this->assign("waitSecond",3);
			$this->assign("jumpUrl",'?s=Admin-Create-Show');
        	$this->success('Google Sitemap地图生成成功！<br />请通过<a href="https://www.google.com/webmasters/tools" target="_blank">谷歌站长工具</a>提交！');
		} else {
			$this->baidu(true, true);
		}
    }
	//生成索引地图
    public function siteMap($mapname,$limit){
		$suffix = C('html_file_suffix');
		$listmap = $this->Lable_siteMap($mapname,$limit);
		if($listmap){
			$this->assign('list_map',$listmap);
			C('html_file_suffix','.xml');
			$this->buildHtml($mapname,'./','./Public/maps/sitemap.html');
			$this->echo_ob_flush();
			C('html_file_suffix',$suffix);
		}
    }
	//生成Rss订阅
    public function rss($id = ''){
		$rss = !empty($_REQUEST['rss'])?intval($_REQUEST['rss']):50;
		$this->map_create('rss',$rss,1);
		if (empty($id)) {
			$this->assign("jumpUrl",'?s=Admin-Create-Show');
        	$this->success('Rss订阅文件生成成功！');
		} else {
			$this->assign("jumpUrl",'?s=Admin-Create-Show');
			$this->success('恭喜您，所有地图生成完毕！');	
		}
    }
	//生成地图共用模块
    public function map_create($mapname,$limit,$page){
		$suffix = C('html_file_suffix');
		$listmap = $this->Lable_Maps($limit,$page);
		if($listmap){
			$this->assign('list_map',$listmap);
			C('html_file_suffix','.xml');
			$this->buildHtml($mapname.'-'.$page,'./'.C('url_map'),'./Public/maps/'.$mapname.'.html');
			$this->echo_ob_flush();
			C('html_file_suffix',$suffix);
		}
    }
    //生成网站首页
    public function index(){
		$jump = !empty($_GET['jump'])?intval($_GET['jump']):0;
		$this->check(C('url_html'),'网站首页');
		F('_create/nextcreate',NULL);
	    $this->assign($this->Lable_Index());
		$this->buildHtml("index",'./','Home:pp_index');
		$this->echo_ob_flush();
		if ($jump) {
			$this->assign("jumpUrl",'?s=Admin-Create-Mytpl-jump-'.$jump);
			$this->success('首页生成完毕，准备生成自定义模板！');
		}else{
			$this->assign("jumpUrl",'?s=Admin-Create-Show');
			$this->success('首页生成完毕。');		
		}
    }
    //生成自定义模板
    public function mytpl(){
		$suffix = C('html_file_suffix');
		$jump = !empty($_GET['jump'])?intval($_GET['jump']):0;
		$this->check(C('url_html'),'自定义模板');
		import("ORG.Io.Dir");
		$dir = new Dir(TEMPLATE_PATH.'/Home');
		$list_dir = $dir->toArray();
		$array_tpl = array();
		foreach($list_dir as $key=>$value){
			if(preg_match("/my_(.*)\.html/",$value['filename'])){
				C('html_file_suffix',$suffix);
				$this->buildHtml(str_replace(array('my_','.html'),'',$value['filename']),'./'.C('url_mytpl'),'Home:'.str_replace('.html','',$value['filename']));
				$this->echo_ob_flush();
			}
		}
		if ($jump) {
			$this->assign("jumpUrl",'?s=Admin-Create-Maps-jump-'.$jump);
			$this->success('自定义模板生成完毕，准备生成网站地图！');
		}else{
			$this->assign("jumpUrl",'?s=Admin-Create-Show');
			$this->success('恭喜您，自定义模板生成成功！');			
		}		
    }
	//生成专题列表
    public function speciallist(){
		//检测是否需要生成
		$this->check(C('url_html'),'专题列表页','?s=Admin-Create-Show');
		//查询数据开始	
		$page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
		$limit = gettplnum('ff_mysql_special\(\'(.*)\'\)','pp_speciallist');
		$rs = D("Special");
		$where['special_status'] = array('eq',1);
		$count = $rs->where($where)->count('special_id');
		$totalpages = ceil((!empty($count)?$count:1)/(!empty($limit)?$limit:1));
		echo'<ul id="show" style="font-size:12px;list-style:none;margin:0px;padding:0px;font-family:宋体">';
		echo'<li>共有专题<font color=red>'.$count.'</font>篇，专题列表需要生成<font color=blue>'.$totalpages.'</font>页。</li>';
		for($i=1;$i<=$totalpages;$i++){
			//变量赋值
			C('jumpurl',ff_special_url(9999));
			C('currentpage',$i);
			$channel = $this->Lable_Special_List(array('page'=>$i));
			//生成文件		
			$htmldir = str_replace('{!page!}',$i,ff_special_url_dir($i));
			$htmlurl = C('sitepath').$htmldir.C('html_file_suffix');
			$this->buildHtml($htmldir,'./','Home:'.$channel['special_skin']);
			echo'<li>第<font color=blue>'.$i.'</font>页生成完毕　<a href="'.$htmlurl.'" target="_blank">'.$htmlurl.'</a></li>';
			$this->echo_ob_flush();
		}
		echo'</ul>';
		$this->jump('?s=Admin-Create-Show','恭喜您，专题列表页已经全部生成！');
	}
	//生成专题内容分页处理
    public function specialclass(){
		//检测是否需要生成
		$this->check(C('url_html'),'专题内容页','?s=Admin-Create-Show');
		//查询数据开始	
		$page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
		$rs = D("Special");
		$where['special_status'] = array('eq',1);
		$count = $rs->where($where)->count('special_id');
		$totalpages = ceil($count/C('url_number'));
		$array = $rs->field('special_id')->where($where)->order('special_addtime desc')->limit(C('url_number'))->page($page)->select();
		if (empty($array)) {
			$this->assign("jumpUrl",'?s=Admin-Create-Show');$this->error('没有数据，不需要生成！');
		}
		echo'<ul id="show" style="font-size:12px;list-style:none;margin:0px;padding:0px;font-family:宋体">';
		echo'<li>总共需要生成<font color=blue>'.$count.'</font>个专题内容页，需要分<font color=blue>'.$totalpages.'</font>次来执行，正在执行第<font color=red>'.$page.'</font>次</li>';
		foreach($array as $key=>$value){
			$this->create_red_special($value['special_id']);
			$this->echo_ob_flush();
		}
		echo'</ul>';
		//组合回跳URL路径与执行跳转
		$jumpurl = '?s=Admin-Create-Show';
		if($page < $totalpages){
			$jumpurl = '?s=Admin-Create-Specialclass-page-'.($page+1);
			$this->jump($jumpurl,'稍等一会，准备生成下一次专题内容页...');
		}
		$this->jump($jumpurl,'恭喜您，专题内容页全部生成完毕。');
	}
	//生成专题内容页
    public function create_red_special($specialid){
		$where = array();
		$where['special_id'] = $specialid;
		$where['special_status'] = array('eq',1);
		$rs = D("Special");
		$array_special = $rs->where($where)->find();
		if($array_special){
			$arrays = $this->Lable_Special_Read($array_special);
			$this->assign($arrays['read']);
			$this->assign('list_vod',$arrays['list_vod']);
			$this->assign('list_news',$arrays['list_news']);
			//
			$htmldir = ff_data_url_dir('special',$arrays['read']['special_id'],0,$arrays['read']['special_name'],1);
			$htmlurl = C('site_path').$htmldir.C('html_file_suffix');
			$this->buildHtml($htmldir,'./',$arrays['read']['special_skin']);
			echo '<li>'.$arrays['read']['special_id'].' <a href="'.$htmlurl.'" target="_blank">'.$htmlurl.'</a> 生成完毕</li>';
		}
	}	
	//显示生成
    public function show(){
		$array['url_html'] = C('url_html');
		if(C('url_html_list')){
			$array['url_html_list'] = C('url_html_list');
		}else{
			$array['url_html_list'] = 0;
		}
		$listarr = F('_ppvod/list');
		foreach($listarr as $key=>$value){
			$keynew = $value['list_sid'];
			$list[$keynew][$key] = $value['list_id'];
		}
		$this->assign($array);
		$this->assign('jumpurl',F('_create/nextcreate'));
		$this->assign('list_vod_all',!empty($list[1])?getWDSrt(implode(',',$list[1])):'');
		$this->assign('list_news_all',!empty($list[2])?getWDSrt(implode(',',$list[2])):'');
	    $this->assign('list_vod',F('_ppvod/listvod'));
		$this->assign('list_news',F('_ppvod/listnews'));
        $this->display('./Public/system/html_show.html');
    }
	//判断生成条件
    public function check($html_status,$html_err,$jumpurl='?s=Admin-Create-Show'){	
	    if (!$html_status) {
		    $this->assign("jumpUrl",$jumpurl);
		    $this->error('"'.$html_err.'"模块动态运行，不需要生成静态网页！');
		}
	}
	//生成跳转
	public function jump($jumpurl,$html){
		echo '</div><script>if(document.getElementById("show")){document.getElementById("show").style.display="none";}</script>';
		$this->assign("waitSecond",C('url_time'));
		$this->assign("jumpUrl",$jumpurl);
		$this->success($html);
	}
    //清空缓存
	private function echo_ob_flush(){
		header("Connection: close");
		header("HTTP/1.1 200 OK");
		echo str_repeat(" ", 1024*128*8);
		//echo('<p style="font-size:13px;color:red;">任务已经开始，后台执行中</p>');
		ob_flush();flush();
		ob_end_clean();//销毁缓冲区，后面的不会显示在浏览器上了
		//ignore_user_abort(true);//后台运行
	}
}
?>