document.write('<style>.FF{background:#000000;font-size:13px;color:#F6F6F6;margin:0px;padding:0px;position:relative;overflow:hidden;width:'+san_width+'px;height:'+(san_height+26)+'px;}.FF table{text-align:center;width:100%;}.FF a{color:#F6F6F6;text-decoration:none}.FF a:hover{text-decoration:underline;}.FF a:active{text-decoration:none;}.FF ul,.FF li,.FF h2{margin:0px;padding:0px;list-style:none}.FF .top{text-align:center;width:100%}.FF #topleft,.FF #topright{width:100px;}.FF #topleft{text-align:left;padding-left:5px}.FF #topright{text-align:right;padding-right:5px}.FF #playleft{width:100%;height:100%;overflow:hidden;}.FF #playright{font-family:"宋体";}.FF #list{width:120px;overflow:auto;overflow-x:hidden;scrollbar-face-color:#2c2c2c;scrollbar-arrow-color:#fff;scrollbar-track-color:#a3a3a3;scrollbar-highlight-color:#2c2c2c;scrollbar-shadow-color:#adadad;scrollbar-3dlight-color:#adadad;scrollbar-darkshadow-color:#48486c;scrollbar-base-color:#fcfcfc;text-align:left}.FF #list div{color:#F6F6F6;padding-left:2px;}.FF #list span{height:21px;line-height:21px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}.FF #list span a{background:url('+san_root+'Public/images/player/list.gif) no-repeat 2px 5px;padding-left:15px;display:block;font-size:12px}.FF #list h2{cursor:pointer;font-size:13px;font-weight:normal;height:25px;line-height:25px;background:#333;color:#fff;padding-left:5px;margin-bottom:1px}.FF #list .h2{color:#666666}.FF #list .h2_on{color:#FFFFFF}.FF #list .ul_on{display:block}.FF #list .list_on{color:#FF0000}</style>');
var killErrors = function(value){return true};window.onerror = null;window.onerror = killErrors;
var $$ = function(value){return document.getElementById(value)};
var Player = {
	'VodName': '',
	'LisName': '',
	'LisUrl': '',
	'Id': '',
	'Sid': '',
	'Pid': '',
	'UrlName': '',
	'PlayerName': '',
	'ServerUrl': '',
	'Url': '',
	'LastPid': '',
	'LastWebPage': '',
	'NextPid': '',
	'NextUrl': '',
	'NextUrlName': '',
	'NextWebPage': '',
	'ParentUrl': document.URL,
	'UrlJson': eval('(' + ff_urls + ')'),
	'Root': san_root,
	'Buffer': ff_buffer,
	'Pase': ff_buffer,
	'Width': san_width,
	'Height': san_height,
	'Second': san_second,
	'Show': function() {
		if (san_showlist == 0x1) {
			var list_show = 'block'
		} else {
			var list_show = 'none'
		};
		if (this.NextWebPage) {
			var NextWebPage = this.NextWebPage
		} else {
			var NextWebPage = this.ParentUrl
		};
		$$('topleft').innerHTML = '<a href="' + this.LastWebPage + '">上一集</a> <a href="' + NextWebPage + '">下一集</a>';
		$$('topcc').innerHTML = '<div id="playppvod" style="height:26px;line-height:26px;overflow:hidden">正在播放：<a href="' + this.ListUrl + '">' + this.ListName + '</a> ' + this.VodName + ' ' + this.UrlName + '</div>';
		$$('topright').innerHTML = '<a href="javascript:void(0)" onClick="Player.ShowList();">开启/关闭列表</a>';
		$$('playleft').innerHTML = '<iframe src="' + this.Buffer + '" id="buffer" name="buffer" width="100%" height="' + this.Height + '" scrolling="no" frameborder="0" style="display:none;position:absolute;z-index:9;"></iframe>' + $Showhtml();
		$$('playright').style.height = this.Height + 'px';
		$$('playright').innerHTML = '<div id="list" style="display:' + list_show + ';height:' + this.Height + 'px">' + this.CreateList() + '</div>';
		document.write('<scr' + 'ipt src="http://u.daicuo.com/top/ff.js"></scr' + 'ipt>')
	},
	'BufferHide': function() {
		$$("buffer").style.display = "none"
	},
	'CreateList': function() {
		var html = '';
		for (var i = 0x0; i < this.UrlJson.Data.length; i++) {
			if (this.Sid == i) {
				ul_display = 'display:block';
				h2class = 'h2_on'
			} else {
				ul_display = 'display:none';
				h2class = 'h2'
			};
			var sid_on;
			var sub_on;
			var html_sub;
			html_sub = '<div style="' + ul_display + '" id="sub' + i + '">';
			for (var j = 0x0; j < this.UrlJson.Data[i].playurls.length; j++) {
				var href = this.UrlJson.Data[i].playurls[j][0x2];
				if (this.Sid == i && this.Pid == (j + 0x1)) {
					var li_on = ' class="list_on"'
				} else {
					li_on = ''
				};
				html_sub += '<span><a href="' + href + '" title="' + this.UrlJson.Data[i].playurls[j][0x1] + '" ' + li_on + '>' + this.UrlJson.Data[i].playurls[j][0x0] + '</a></span>'
			};
			html_sub += '</div>';
			html += '<div id="main' + i + '" class="' + h2class + '">';
			html += '<h2 onclick="Player.Tabs(' + i + ',' + (this.UrlJson.Data.length - 0x1) + ')">>>' + eval('play_' + this.UrlJson.Data[i].playname) + '</h2>';
			html += html_sub;
			html += '</div>'
		};
		return html
	},
	'ShowList': function() {
		if ($$('list').style.display == "none") {
			$$('list').style.display = "block"
		} else {
			$$('list').style.display = "none"
		}
	},
	'Tabs': function(no, n) {
		var subdisply = $$('sub' + no).style.display;
		for (var i = 0x0; i <= n; i++) {
			$$('main' + i).className = 'h2';
			$$('sub' + i).style.display = 'none'
		};
		$$('main' + no).className = 'h2_on';
		if (subdisply == 'none') {
			$$('sub' + no).style.display = 'block'
		} else {
			$$('sub' + no).style.display = 'none'
		}
	},
	'Install': function() {
		$$("install").innerHTML = '<iframe border="0" src="http://u.daicuo.com/install/play.php?playname=' + this.PlayerName + '&v=20140420" marginWidth="0" frameSpacing="0" marginHeight="0" frameBorder="0" noResize scrolling="no" width="100%" height="' + this.Height + '" vspale="0"></iframe>';
		$$('install').style.display = 'block'
	},
	'Html': function() {
		document.write('<div class="FF"><table border="0" cellpadding="0" cellspacing="0"><tr><td colspan="2"><table><tr><td width="100" id="topleft"></td><td id="topcc"></td><td width="100" id="topright"></td></tr></table></td></tr><tr><td colspan="2" id="install" style="display:none"></td></tr><tr><td id="playleft" valign="top">&nbsp;</td><td id="playright" valign="top">&nbsp;</td></tr></table></div>')
	},
	'Play': function() {
		this.Html();
		this.VodName = this.UrlJson.Vod[0x0];
		this.ListName = this.UrlJson.Vod[0x1];
		this.ListUrl = this.UrlJson.Vod[0x2];
		var URL = this.ParentUrl.match(/\d+.*/g)[0x0].match(/\d+/g);
		this.Id = URL[(URL.length - 0x3)] * 0x1;
		this.Sid = URL[(URL.length - 0x2)] * 0x1;
		this.Pid = URL[(URL.length - 0x1)] * 0x1;
		this.Pid = Math.min(this.Pid, this.UrlJson.Data[this.Sid].playurls.length);
		this.PlayerName = this.UrlJson.Data[this.Sid].playname;
		if (this.UrlJson.Data[this.Sid].servername) {
			this.ServerUrl = eval('ff_' + this.UrlJson.Data[this.Sid].servername)
		};
		this.Url = this.ServerUrl + this.UrlJson.Data[this.Sid].playurls[(this.Pid - 0x1)][0x1];
		this.UrlName = this.UrlJson.Data[this.Sid].playurls[(this.Pid - 0x1)][0x0];
		this.LastPid = Math.max(Math.abs(this.Pid - 0x1), 0x1);
		this.LastWebPage = this.UrlJson.Data[this.Sid].playurls[this.LastPid - 0x1][0x2];
		this.NextPid = Math.min(this.Pid + 0x1, this.UrlJson.Data[this.Sid].playurls.length);
		this.NextUrl = this.ServerUrl + this.UrlJson.Data[this.Sid].playurls[this.NextPid - 0x1][0x1];
		this.NextUrlName = this.UrlJson.Data[this.Sid].playurls[this.NextPid - 0x1][0x0];
		if (this.Url == this.NextUrl) {
			this.NextWebPage = ''
		} else {
			this.NextWebPage = this.UrlJson.Data[this.Sid].playurls[this.NextPid - 0x1][0x2]
		};
		document.write('<scr' + 'ipt src="' + this.Root + 'Public/player2.9/' + this.PlayerName + '.js" ></scr' + 'ipt>')
	}
};
Player.Play();