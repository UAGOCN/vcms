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
		$config_old = require RUNTIME_PATH.'Conf/config.php';
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