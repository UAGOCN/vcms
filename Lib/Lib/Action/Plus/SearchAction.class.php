<?php
class SearchAction extends HomeAction{
	public function vod(){
		$wd = trim(getWDSrt($_GET['q']));
		$where = array();
		$where['vod_name'] = array('like',$wd.'%');
		$rs = D('Vod');
		$list = $rs->field('vod_name')->where($where)->limit(10)->order('vod_hits_month desc')->select();
		if($list){
			$this->ajaxReturn($list,"ok",1);
		}else{
			$this->ajaxReturn($list,"ok",0);
		}
	}
}
?>