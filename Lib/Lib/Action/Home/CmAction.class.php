<?php
class CmAction extends HomeAction{
	// 展示评论
    public function show(){
		$Url = ff_param_url();
		$where = array();
		$where['cm_cid'] = $Url['id'];
		$where['cm_sid'] = $Url['sid'];
		$limit = intval(C('user_cmnum'));
		if(C('user_check')){
			$where['cm_status'] = 1;
		}
		$rs = D('CmView');
		$count = $rs->where($where)->count('cm_id');
		$totalpages = ceil($count/$limit);
		$page = get_maxpage($Url['page'],$totalpages);
		$pageurl ='javascript:void(0)" onclick="SAN.Comment.Show(\''.U('cm/show',array('sid'=>$Url['sid'],'id'=>$Url['id'],'p'=>'{!page!}'),false,false).'\')';
		$pages = '共'.$count.'条评论&nbsp;当前:'.$page.'/'.$totalpages.'页&nbsp;';
		$pages .= getPageIndex($page,$totalpages,$limit,$pageurl,false);
		//
		$list = $rs->where($where)->order('cm_addtime desc')->limit($limit)->page($page)->select();
        if(!empty($list)) {
            foreach($list as $key=>$val){
                $list[$key]['cm_floor'] = $count-(($page-1) * $limit + $key);
            }
        }
		$this->assign('cm_sid',$Url['sid']);
		$this->assign('cm_id',$Url['id']);
		$this->assign('cm_pages',$pages);
		$this->assign('cm_count',$count);
		$this->assign('cm_list',$list);
		$this->display('pp_comment');
    }
	// 添加评论
    public function insert(){
		$cookie = 'comment_'.intval($_POST['cm_sid']).'_'.intval($_POST['cm_cid']);
		if(isset($_COOKIE[$cookie])){
			$this->ajaxReturn(0,'您已评论过，请先休息一会！',0);
		}
		$rs = D("Cm");
		C('TOKEN_ON',false);//关闭令牌验证
		if($rs->create()){
			if (false !== $rs->add()) {		
				setcookie($cookie, 'true', time()+intval(C('user_second')));
				if (C('user_check')) {
					$this->ajaxReturn(-1,"评论成功，我们会尽快审核你的评论！",2);
				}else{
					$this->ajaxReturn(-1,"评论成功，感谢你的参与！",1);
				}
			}
		}
		$this->ajaxReturn(0,'评论失败，请重试！',0);
    }
	// Ajax顶踩
    public function updown(){
		$id = !empty($_GET['id'])?intval($_GET['id']):0;
		if ($id < 1) {
			exit('-1');
		}
		$ajax = trim(getWDSrt($_GET['ajax']));
		$cookie = 'cmud-'.$id;
		if(isset($_COOKIE[$cookie])){
			exit('0');
		}
		$rs = M("Cm");
		if ('up' == $ajax){
			$rs->setInc('up','id = '.$id);
			setcookie($cookie, 'true', time()+intval(C('user_check_time')));
		}elseif( 'down' == $ajax){		
			$rs->setInc('down','id = '.$id);
			setcookie($cookie, 'true', time()+intval(C('user_check_time')));
		}
		$list = $rs->field('up,down')->find($id);
		if (empty($list)) {
			$list['up'] = 0;
			$list['down'] = 0;
		}
		echo($list['up'].':'.$list['down']);
    }	
}
?>