<?php
class SpecialAction extends HomeAction{
    //专题列表
    public function show(){
		//通过地址栏参数支持筛选条件,$JumpUrl传递分页及跳转参数
		$Url = ff_param_url();
		$JumpUrl = ff_param_jump($Url);
		$JumpUrl['p'] = '{!page!}';	
		C('jumpurl',UU('Home-special/show',$JumpUrl,false,true));	
		C('currentpage',$Url['page']);
		//变量赋值
		$channel = $this->Lable_Special_List($Url);
		$this->assign($channel);
		$this->display($channel['special_skin']);
    }
	//专题内容页
    public function read(){
		$array_detail = $this->get_cache_detail( intval($_GET['id']) );
		if($array_detail){
			$this->assign($array_detail['read']);
			$this->assign('list_vod',$array_detail['list_vod']);
			$this->assign('list_special',$array_detail['list_special']);
			$this->display($array_detail['read']['special_skin']);
		}else{
			$this->assign("jumpUrl",C('site_path'));
			$this->error('此专题已经删除！');
		}
    }
	// 从数据库获取数据
	private function get_cache_detail($special_id){
		if(!$special_id){ return false; }
		//优先读取缓存数据
		if(C('data_cache_special')){
			$array_detail = S('data_cache_special_'.$special_id);
			if($array_detail){
				return $array_detail;
			}
		}
		//未中缓存则从数据库读取
		$where['special_id'] = $special_id;
		$where['special_status'] = array('eq',1);
		$rs = D("Special");
		$array = $rs->where($where)->find();
		if($array){
			//解析标签
			$array_detail = $this->Lable_Special_Read($array);
			if( C('data_cache_special') ){
				S('data_cache_special_'.$special_id, $array_detail, intval(C('data_cache_special')));
			}
			return $array_detail;
		}
		return false;
	}
}
?>