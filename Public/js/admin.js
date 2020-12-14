var $feifeicms = {};
$feifeicms.version = '2.0';
//所有class为table表格的隔行换色效果
$feifeicms.show = ({	  
	table : function(){
		var tabLen = $("table.table").length;
		if (tabLen>0){
			$("tbody:even").attr("class","bgEven");  
			$("tbody:odd").attr("class","bgOdd");
			//鼠标移至tbody区域时换色
			$("table.table tbody").hover(
				function(){
					$(this).addClass("bgOver");
				},
				function(){
					$(this).removeClass("bgOver");
				}
			);
		};
	}
});
//表单提交验证
$feifeicms.form = ({	  
	//$("#"+$id).val().match(/([w]){2,12}$/) 2-12位数字字母组合	  
	empty : function($formname,$id){
		if(!$("#"+$id).val()){
			$("#"+$id).focus();
			$("#"+$id).css("border-color","#FF0000");
			$("#"+$id+"_error").html($('#'+$id).attr('error'));//html>><input ... error='not empty'></input><span id="$id_error">error</span>
			return false;
		}else{
			return true;
		}
	},
	repwd : function($formname,$id,$reid){
		if($("#"+$id).val()){
			if($("#"+$id).val() != $("#"+$reid).val()){
				$("#"+$id).focus();
				$("#"+$id).css("border-color","#FF0000");
				$("#"+$reid+"_error").html($('#'+$reid).attr('error'));
				return false;
			}else{
				return true;
			}
		}
	}	
});
//表单提交
function post($url){
	$('#myform').attr('action',$url);
	$('#myform').submit();
}
//全选与反选
function checkall($all){
	if($all){
		$("input[name='ids[]']").each(function(){
				this.checked = true;
		});		
	}else{
		$("input[name='ids[]']").each(function(){
			if(this.checked == false)
				this.checked = true;
			else
			   this.checked = false;
		});		
	}
}
//tab切换
function showtab(mid,no,n){
	for(var i=0;i<=n;i++){
		$('#'+mid+i).hide();
	}
	$('#'+mid+no).show();
}
//分页跳转
function pagego($url,$total){
	$page=document.getElementById('page').value;
	if($page>0&&($page<=$total)){
		$url=$url.replace('{!page!}',$page);
		location.href=$url;
	}
	return false;
}
// 图片预览
function showpic(event,imgsrc,path){	
	var left = event.clientX+document.body.scrollLeft+20;
	var top = event.clientY+document.body.scrollTop+20;
	$("#showpic").css({left:left,top:top,display:""});
	if(imgsrc.indexOf('://')<0){
		imgsrc = path+imgsrc;
	}
	$("#showpic_img").attr("src",imgsrc);
}
// 取消图片预览
function hiddenpic(){
	$("#showpic").css({display:"none"});
}
//设置星级
function setstars(mid, id, stars){
	$.get('?s=Admin-'+mid+'-Ajaxstars-id-'+id+'-stars-'+stars, function(obj){
		if(obj == 'ok'){
			for(i=1; i<=5; i++){
				$('#star_'+id+'_'+i).attr("src","./Public/images/admin/star0.png");
				//$('#star_'+id+'_'+i).removeClass('star1');
				//$('#star_'+id+'_'+i).addClass('star0');
			}
			for(i=1; i<=stars; i++){
				$('#star_'+id+'_'+i).attr("src","./Public/images/admin/star1.png");
			}	
		}
	});
}
//设置星级(添加与编辑)
function addstars(sid,stars){
	for(i=1; i<=5; i++){
		$('#star_'+i).attr("src","./Public/images/admin/star0.png");
	}
	for(i=1; i<=stars; i++){
		$('#star_'+i).attr("src","./Public/images/admin/star1.png");
	}
	$('#'+sid+'_stars').val(stars);
}
//设置连载
function setcontinu(id,string){
	//var width = document.body.scrollWidth;
	//var height = document.body.scrollHeight;
	$('#showbg').css({width:$(window).width(),height:$(window).height()});	
	$('#ct_'+id).after('<span class="continu" id="htmlcontinu">连载至<input type="text" size="8" maxlength="15" value="'+string+'" name="continuajax" id="continuajax" onMouseOver="this.select();">集 <input type="button" value="确定" onclick="ajaxcontinu('+id+',continuajax.value);" class="navpoint"/><input type="button" value="取消" onclick="hidecontinu()" class="navpoint"/></span>');
	var offset = $('#ct_'+id).offset();
	$('#htmlcontinu').css({left:offset.left,top:offset.top});
}
//取消连载
function hidecontinu(){
	$('#showbg').css({width:0,height:0});
	$('#htmlcontinu').remove();
}
//AJAX连载
function ajaxcontinu(id,value){
	if(value==0){
		$('#ct_'+id).html('<img src="./Public/images/admin/ct.png" style="margin-top:10px" class="navpoint" onClick="setcontinu('+id+',0);">');
	}else{
		$('#ct_'+id).html('<sup onClick=setcontinu('+id+',"'+value+'") class="navpoint">'+value+'</sup>');
	}
	$.get('?s=Admin-Vod-Ajaxcontinu-id-'+id+'-continu-'+value);
	hidecontinu();
}
/*滚动
$(window).scroll(function() { 		
	$("#div0").css({left:'50%',top:$(this).scrollTop()+100});
});
*/
//pop弹出层：引用网页
function divwindow(strPath,Msg){
	var htm = '<iframe src="'+strPath+'" width="100%" height="95%" frameborder="0" scrolling="auto" style="overflow-x:hidden;"></iframe>';
	$("#dialog>#dia_title>span").html(Msg);
	$("#dialog>#dia_content").html(htm);
	$("#dialog").jqmShow();
}