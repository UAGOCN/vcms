﻿var ParentUrl = document.URL;
var video_URL = ParentUrl.match(/\d+.*/g)[0x0].match(/\d+/g);
var video_Id = video_URL[(video_URL.length - 0x3)] * 0x1;
var video_Sid = video_URL[(video_URL.length - 0x2)] * 0x1;
var video_Pid = video_URL[(video_URL.length - 0x1)] * 0x1;
var video_player = JSON.parse(player_urls);
var video = a.substring(0,16);
document.write('<link rel="stylesheet" href="'+san_root+'Public/CKPlayer/CKPlayer.min.css" /><div id="ckplayer" class="ckplayer"></div><script src="'+san_root+'Public/CKPlayer/hls.min.js" type="text/javascript"></script><script src="'+san_root+'Public/CKPlayer/CKPlayer.min.js" type="text/javascript"></script><script type="text/javascript">const dp = new CKPlayer({container: document.getElementById(\'ckplayer\'),screenshot: true,logo:\''+san_root+'Tpl/defalut/static/images/logo.png\',/*danmaku: {id: \''+Date.parse(new Date())+'\', token: \'token\', api: [\'/\'],maximum: 1000,user: \'Google\',bottom: \'15%\',unlimited: true,},*/video: {url: \''+decrypt(video_player['Data'][0]['playurls'][video_Pid-1][1],video,a.substring(16))+'\',type: \'customHls\',customType: {customHls: function(video, player) {const hls = new Hls();hls.loadSource(video.src);hls.attachMedia(video);},},},});maskPlayerIt($(\'#ckplayer\'));</script>');
document.write('<style>.player{position: absolute;left: 0px;right: 0px;top: 0px;bottom: 0px;width: 100%;height: 100%;}.ckplayer{width:100%;height:100%;}.ckplayer-logo{max-width:80px;}.divMaskPlayer img{width:300px;}.close_button{font-size: 14px;width:100%;line-height:1.6rem;background: #a43151;color: #ffffff;}</style>');
$(document).ready(function(){$("#on"+video_Pid).addClass("on");});
function maskPlayerIt(srt){var hoverdiv = '<div class="divMaskPlayer" style="position:absolute;width:100%;height:100%;margin: 0 auto;background:#999999;opacity:0.9;filter: alpha(opacity=10);z-index:8;"><div style="width:300px;height:100%;margin:0 auto;top:20%; position:relative;z-index:9;"><div class="pagePlay"></div><input class="close_button" type="submit" onclick="UnMaskPlayerIt($(\'#ckplayer\'));" value="关闭" /></div></div>';$(srt).wrap('<div class="position:relative;"></div>');$(srt).before(hoverdiv);$(srt).data("adPlayer",true);}function UnMaskPlayerIt(srt){if($(srt).data("adPlayer") == true){$(srt).parent().find(".divMaskPlayer").remove();$(srt).unwrap();$(srt).data("adPlayer",false);}$(srt).data("adPlayer",false);dp.play();/*dp.fullScreen.request('browser');*/}function getHTML(str) {var str=str.replace(/<script.*?>.*?<\/script>/ig, '');str=str.replace(/[|]*\n/, '');str=str.replace(/&nbsp;/ig, '');return str;}function decrypt(str, key, iv) {var str=str.replace(/-/g, '+');str=str.replace(/_/g, '/');str=str.replace(/~/g, '=');var key = CryptoJS.enc.Utf8.parse(key);var iv = CryptoJS.enc.Utf8.parse(iv);var decrypted = CryptoJS.AES.decrypt(str, key, {iv: iv,padding: CryptoJS.pad.Pkcs7});return decrypted.toString(CryptoJS.enc.Utf8);}
//console.log();