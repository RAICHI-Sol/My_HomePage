/************************************************
gloval
*************************************************/
var url = "/php_test/contents/contents.php#";
var now_num = 0;

/************************************************
image_ivent
*************************************************/
function Count_Adj(count)
{
    if (count < 0){
        return count_max-1;
    }
    else if(count > count_max-1){
        return 0;
    }
    else{
        return count;
    }
}

function Chenge_num(num,id)
{
    var taget_r = document.getElementById("scroll_right");
    var taget_l = document.getElementById("scroll_left");

    if(id == "botton"){
        now_num = Count_Adj(num);
        taget_r.href = url + Count_Adj(now_num +1);
        taget_l.href = url + Count_Adj(now_num -1);
    }
    else{
        taget_r.href = url + Count_Adj(now_num +1);
        taget_l.href = url + Count_Adj(now_num -1);
        now_num = Count_Adj(now_num + num);
    }

    for(i = 0;i < count_max;i++){
        var botton_num = $("#botton" + i);

        if(i == now_num){
            botton_num.addClass('now');
        }
        else{
            botton_num.removeClass('now');
        }
    }
}


var Change_image_Interval = function(){
    var new_num = now_num + 1;
    window.open(url + Count_Adj(new_num),"contents");
    Chenge_num(new_num,"timer");
};
