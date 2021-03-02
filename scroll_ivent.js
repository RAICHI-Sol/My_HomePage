/************************************************
gloval
*************************************************/
var menu      = $('.menu');

/************************************************
scroll_ivent
*************************************************/
$(window).on('scroll',function(){
    if($(window).scrollTop() > 100)
    {
        menu.addClass('fixed');
    }
    else{
        menu.removeClass('fixed');
    }
});
$(window).trigger('scroll');