<?php
class MyAction extends HomeAction{
    public function show(){
		$id = !empty($_GET['id'])?getWDSrt($_GET['id']):'new';
		$this->display('my_'.trim($id));
	}					
}
?>