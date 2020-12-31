 <?php
class Md5Action extends BaseAction{	
    public function showajax(){
		echo trim(md5($_GET['sid']));
    }

	public function Vodajax(){
		$rs = D("Vod");
		$where['vod_name_md5'] = trim(md5($_GET['sid']));
		$array = $rs->where($where)->select();
		if(!empty($array)){
			echo 'true';
		}else{
			echo 'false';
		}
    }
}
?>