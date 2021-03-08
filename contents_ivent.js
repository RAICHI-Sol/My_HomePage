/************************************************
gloval
*************************************************/
var now_num = 0;
var target_r;
var target_l;

/************************************************
*
image_ivent
*
*************************************************/
//カウントの調整
function Count_Adj(count)
{
    if(count_max >= 5){
        count_max = 5;
    }

    if (count < 0){
        return count_max - 1;
    }
    else if(count > count_max - 1){
        return 0;
    }
    else{
        return count;
    }
}

//画像の遷移(ボタン)
function Chenge_button(num)
{
    clearInterval(time);
    now_num = Count_Adj(num);
    $('.slide').animate({scrollLeft:300 * now_num},300,'linear');
    target_r = Count_Adj(now_num +1);
    target_l = Count_Adj(now_num -1);
    Button_light();
    time = setInterval(Change_image_Interval, 3000);
}

//画像の遷移(矢印ボタン)
function Chenge_scroll(num)
{
    clearInterval(time);
    target_r = Count_Adj(now_num +1);
    target_l =  Count_Adj(now_num -1);
    if(num == 1)
    {
        $('.slide').animate({scrollLeft:300 * target_r},300,'linear');
    }
    else
    {
        $('.slide').animate({scrollLeft:300 * target_l},300,'linear');
    }
    now_num = Count_Adj(now_num + num);
    Button_light();
    time = setInterval(Change_image_Interval, 3000);
}

//ボタンの遷移
function  Button_light() {
    for(i = 0;i < count_max;i++){
        var botton_num = $("#button" + i);

        if(i == now_num){
            botton_num.addClass('now');
        }
        else{
            botton_num.removeClass('now');
        }
    }
}

//タイマー処理
var Change_image_Interval = function(){
    now_num = Count_Adj(now_num + 1);
    $('.slide').animate({scrollLeft:300 * now_num},300,'linear');
    Button_light();
};

var time = setInterval(Change_image_Interval, 3000);
