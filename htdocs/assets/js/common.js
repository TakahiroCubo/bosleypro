
$(function(){
    var ua = navigator.userAgent;
    if((ua.indexOf('iPhone') > 0) || ua.indexOf('iPod') > 0 || (ua.indexOf('Android') > 0 && ua.indexOf('Mobile') > 0)){
        $('head').prepend('<meta name="viewport" content="width=device-width,initial-scale=1">');
    } else {
        //$('head').prepend('<meta name="viewport" content="width=1200,initial-scale=1">');
    }


    if (ua.indexOf('iPhone') > 0 || ua.indexOf('Android') > 0 && ua.indexOf('Mobile') > 0) {
        //sp
    } else if (ua.indexOf('iPad') > 0 || ua.indexOf('Android') > 0) {
        //tb

        if( $(window).innerHeight() > $('#main').innerHeight()) {
            $('#main').css({
               'height':$(window).innerHeight() - $('footer').innerHeight() + 120
            });
        }
    } else {
        //pc
    }
});

$(function(){
	//bodyClass
	var bodyClassApp = bodyClassApp || {};
		bodyClassApp.name = [
			window.navigator.userAgent.toLowerCase(),
			window.navigator.appVersion.toLowerCase()
			];

		bodyClassApp.browser = function(){
			var oj;
			if(bodyClassApp.name[0].indexOf('msie') != -1){
				bodyClassApp.name[1].indexOf('msie 6.') != -1 || bodyClassApp.name[1].indexOf('msie 7.') != -1 || bodyClassApp.name[1].indexOf('msie 8.') != -1 || bodyClassApp.name[1].indexOf('msie 9.' != -1)? oj = false:oj = true;
			} else {
				bodyClassApp.name[0].indexOf('chrome') != -1 || bodyClassApp.name[0].indexOf('firefox')?oj = true:oj = false;
			}
			return oj;

		};
		bodyClassApp.set = function(){
			if(bodyClassApp.browser() == true)$('body').addClass('selector');

		}
		bodyClassApp.set();

	//ロールオーバー：同じ画像ファイル名+_onに差し替え処理
	$('.allbtn a img, a img.btn, .allbtn input:image, input.btn:image').on({
		'mouseover': function(){ $(this).attr('src', $(this).attr('src').replace(/(\.\w+$)/,'_on$1')); },
		'mouseout': function(){ $(this).attr('src', $(this).attr('src').replace(/_on(\.\w+$)/,'$1')); }
	});

	//ロールオーバー：透明度処理
	$('.allbtn_ro a img, a img.btn_ro, .allbtn_ro input:image, input.btn_ro:image').on({
		'mouseover': function(){ TweenMax.to($(this), 0.2, {opacity: 0.75}); },
		'mouseout': function(){ TweenMax.to($(this), 0.2, {opacity: 1.00}); }
	});

	//スムーススクロール：#だけなら処理しない
	$('a[href^=#]').on('click', function(){
		var href = $(this).attr('href');
		if(href != '#'){ $('html, body').stop().animate({scrollTop: $(href == '#top' ? 'html' : href).offset().top}, 500); return false; }
	});

});


var SP_MAX_WIDTH = 768;
var elmWin = $(window);

$(window).on('load resize', function(){
    //ヘッダー固定分の高さをとる
    if(elmWin.innerWidth() <= SP_MAX_WIDTH){
        //$('#g-navi').css({'top' : '100%' });
    }
});

/**
 * スマートフォン用ハンバーガーメニュー
 */
$(function(){
    $('.h-menu').click(function(e){
        var elmBtn = $(e.currentTarget);
        var elmMenu = $('#g-navi');

        elmMenu.stop();
        elmBtn.toggleClass('open');
        var bOpen = elmBtn.hasClass('open');

        if (bOpen) {
            elmMenu.slideDown(200);
        } else {
            elmMenu.slideUp(200);
        }

        //スマホからPCへリサイズしてもナビがでるように
        $(window).resize(function(){
            bOpen = elmBtn.hasClass('open');
            if(elmWin.innerWidth() > SP_MAX_WIDTH){
                elmBtn.removeClass('open');
                elmMenu.css({
                    'display' : 'block'
                });
            } else {
                if( bOpen ) {

                } else {
                    elmMenu.css({
                        'display' : 'none'
                    });
                }

            }
        });

    });
});

/**
 * pagetop
 */
$(function(){

    /*
    //フッターのページトップリンク
    var pageTop = $("#pagetop");

    pageTop.click(function () {
        $('body, html').animate({ scrollTop: 0 }, 500);
        return false;
    });
    */


	//フッターのページトップリンク
	var pageTop = $("#pagetop");
	//ページトップ
	//$(pageTop).hide();
	$(window).scroll(function(){
		scrollHeight = $(document).height();
		scrollPosition = $(window).height() + $(window).scrollTop();
		footHeight = $("footer").height();

		if ($(this).scrollTop() > 100) {
				$('#pagetop').fadeIn();
		} else {
			$('#pagetop').fadeOut();
		}


        if ( scrollHeight - scrollPosition  < footHeight ) {
            $("a#pagetop img").css({"position":"absolute","bottom":footHeight-220,"right":"0"});
        } else {
            if( $(window).width() <= 767 ){
                $("a#pagetop img").css({"position":"fixed","bottom":"55px","right": "12px" });
            } else {
                $("a#pagetop img").css({"position":"fixed","bottom":"55px","right": "30px" });
            }
        }

	});
	pageTop.click(function () {
		$('body, html').animate({ scrollTop: 0 }, 500);
		return false;
	});

});