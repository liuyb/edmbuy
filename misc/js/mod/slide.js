define(function(){
	$(function() {
        var $toTop = $('<div class="to-top"></div>').appendTo('body');
        $(window).scroll(function() {
            if ($(this).scrollTop() != 0) {
                $toTop.fadeIn();
            } else {
                $toTop.fadeOut();
            }
        });
        $toTop.click(function() {
            $('body,html').animate({ scrollTop: 0 }, 300);
        })
    });
    $(function(){
        $('.btn_close').click(function(){
            $('.top-download').slideUp();
        });
    })
});