/************************************************
gloval
*************************************************/
var url = "/php_test/contents/contents.php#";
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
    window.open(url + now_num,'contents');
    target_r = url + Count_Adj(now_num +1);
    target_l = url + Count_Adj(now_num -1);
    Button_light();
    time = setInterval(Change_image_Interval, 3000);
}

//画像の遷移(矢印ボタン)
function Chenge_scroll(num)
{
    clearInterval(time);
    target_r = url + Count_Adj(now_num +1);
    target_l = url + Count_Adj(now_num -1);
    if(num == 1)
    {
        window.open(target_r,'contents');
    }
    else
    {
        window.open(target_l,'contents');
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
    $('.slide').scrollLeft(300 * now_num);
    Button_light();
};

var time = setInterval(Change_image_Interval, 3000);
