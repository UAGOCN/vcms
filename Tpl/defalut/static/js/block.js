var lazyswitch = "1";
//是否开启图片懒加载，0关闭1开启（关闭后会通过js直接加载图片，速度较慢）

var qrcode1 = "1";
//右侧悬浮二维码，1自动生成（当前页面二维码），填写路径将加载指定图片（建议尺寸150*150px）例：/statics/img/wxcode.png

var qrcode2 = "1";
//内容页二维码，1自动生成（当前页面二维码），填写路径将加载指定图片（建议尺寸160*160px）例：/statics/img/wxcode.png

var ui = {
	'browser': {//浏览器
		url: document.URL,
		domain: document.domain,
		title: document.title,
		language: (navigator.browserLanguage || navigator.language).toLowerCase(),
		canvas: function() {
			return !!document.createElement("canvas").getContext
		}(),
		useragent: function() {
			var a = navigator.userAgent;
			return {
				mobile: !! a.match(/AppleWebKit.*Mobile.*/),
				ios: !! a.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/),
				android: -1 < a.indexOf("Android") || -1 < a.indexOf("Linux"),
				iPhone: -1 < a.indexOf("iPhone") || -1 < a.indexOf("Mac"),
				iPad: -1 < a.indexOf("iPad"),
				trident: -1 < a.indexOf("Trident"),
				presto: -1 < a.indexOf("Presto"),
				webKit: -1 < a.indexOf("AppleWebKit"),
				gecko: -1 < a.indexOf("Gecko") && -1 == a.indexOf("KHTML"),
				weixin: -1 < a.indexOf("MicroMessenger")
			}
		}()
	},
	'mobile': {//移动端
		'popup': function() {
			$popblock = $(".popup");
			$(".open-popup").click(function() {
				$popblock.addClass("popup-visible");
				$("body").append('<div class="mask"></div>');
				$(".close-popup").click(function() {
					$popblock.removeClass("popup-visible");
					$(".mask").remove();
					$("body").removeClass("modal-open")
				});
				$(".mask").click(function() {
					$popblock.removeClass("popup-visible");
					$(this).remove();
					$("body").removeClass("modal-open")
				})
			})
		},
		'slide': function() {
			$(".type-slide").each(function(a) {
				$index = $(this).find('.active').index()*1;
				if($index > 3){
					$index = $index-3;
				}else{
					$index = 0;
				}
				$(this).flickity({
					cellAlign: 'left',
					freeScroll: true,
					contain: true,
					prevNextButtons: false,				
					pageDots: false,
					initialIndex: $index
				});
			})
		},
		'mshare': function() {
			$(".open-share").click(function() {
				ui.browser.useragent.weixin ? $("body").append('<div class="mobile-share share-weixin"></div>') : $("body").append('<div class="mobile-share share-other"></div>');
				$(".mobile-share").click(function() {
					$(".mobile-share").remove();
					$("body").removeClass("modal-open")
				})
			})
		}
	},
	'images': {//图片处理
		'lazyload': function() {
			if(lazyswitch==1){
				$(".lazyload").lazyload({
					effect: "fadeIn",
					threshold: 200,
					failurelimit: 15,
					skip_invisible: false
				})
			}else{				
				$(".lazyload").each(function(){
					var original = $(this).attr("data-original");
			        $(this).css("background-image","url("+original+")");
			        $(this).attr("data-original","");
			        if($(this).attr("src")!= undefined){
			        	$(this).attr("src",original)
			        }	        
			    });
			}			
		},
		'carousel': function() {
			$('.carousel_default').flickity({
			  	cellAlign: 'left',
			  	contain: true,
			  	wrapAround: true,
			  	autoPlay: true,
			  	prevNextButtons: false
			});					
			$('.carousel_wide').flickity({
			  	cellAlign: 'center',
			  	contain: true,
			  	wrapAround: true,
			  	autoPlay: true
			});
			$('.carousel_center').flickity({
			  	cellAlign: 'center',
			  	contain: true,
			  	wrapAround: true,
			  	autoPlay: true,
			  	prevNextButtons: false
			});			
			$('.carousel_right').flickity({
			  	cellAlign: 'left',
			  	wrapAround: true,
			  	contain: true,
			  	pageDots: false
			});
		},
		'qrcode': function() {
			if(qrcode1==1){
				if($("#qrcode").length){
					var qrcode = new QRCode('qrcode', {
					  	text: ui.browser.url,
					  	width: 150,
					  	height: 150,
					  	colorDark : '#000000',
					  	colorLight : '#ffffff',
					  	correctLevel : QRCode.CorrectLevel.H
					});
					$("#qrcode img").attr("class","img-responsive")
				}
			} else {
				if($("#qrcode").length){
					$("#qrcode").append("<img class='img-responsive' src='"+qrcode1+"' width='150' height='150' />")
				}
			}
			if(qrcode2==1){
				if($("#qrcode2").length){
					var qrcode = new QRCode('qrcode2', {
					  	text: ui.browser.url,
					  	width: 160,
					  	height: 160,
					  	colorDark : '#000000',
					  	colorLight : '#ffffff',
					  	correctLevel : QRCode.CorrectLevel.H
					});
					$("#qrcode2 img").attr("class","img-responsive").css("display","inline-block");
				}
			} else {
				if($("#qrcode2").length){
					$("#qrcode2").append("<img class='img-responsive' src='"+qrcode2+"' width='160' height='160' />")
				}
			}
		}
	},
	'common': {//公共基础
		'bootstrap': function() {
			$('a[data-toggle="tab"]').on("shown.bs.tab", function(a) {
				var b = $(a.target).text();
				$(a.relatedTarget).text();
				$("span.active-tab").html(b)
			})
		},
		'headroom': function() {
			if($("#header-top").length){
				var header = document.querySelector("#header-top");
	            var headroom = new Headroom(header, {
					tolerance: 5,
					offset: 205,
					classes: {
						initial: "top-fixed",
						pinned: "top-fixed-up",
						unpinned: "top-fixed-down"
					}
				});
				headroom.init();
			}
		},
		'collapse': function() {
			if($(".detail").length){
				$(".detail").find("a.detail-more").on("click",function(){
					$(this).parent().find(".detail-sketch").addClass("hide");
					$(this).parent().find(".detail-content").css("display","");
					$(this).remove();
				})
			}
		},
		'scrolltop': function() {
			var a = $(window);
			$scrollTopLink = $("a.backtop");
			a.scroll(function() {
				500 < $(this).scrollTop() ? $scrollTopLink.css("display", "") : $scrollTopLink.css("display", "none")
			});
			$scrollTopLink.on("click", function() {
				$("html, body").animate({
					scrollTop: 0
				}, 400);
				return !1
			})
		},
		'copylink': function(){
			if($(".copylink").length){
				var url_short = ui.browser.url;	
				var clipboard = new Clipboard('.copylink', {
					text: function() {									
						return url_short;
					}
				});
				clipboard.on('success', function(e) {
					alert("地址复制成功，赶快分享给小伙伴吧！");
				});
			}
		}	
	}	
};

$(document).ready(function() {	
	if(ui.browser.useragent.mobile){	
		ui.mobile.slide();
		ui.mobile.popup();
		ui.mobile.mshare();
	}
	ui.images.lazyload();
	ui.images.carousel();
	ui.images.qrcode();
	ui.common.bootstrap();
	ui.common.headroom();
	ui.common.collapse();
	ui.common.scrolltop();
	ui.common.copylink();
});
