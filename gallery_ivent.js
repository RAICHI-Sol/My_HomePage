/************************************************
gallery_ivent
*************************************************/
function  Checkbox(id) {

    var image_checbox = document.getElementById(id);
    var image_large = document.getElementById('large_'+id);

    if(image_checbox.checked)
    {
        image_large.classList.add('open');
    }
    else
    {
        image_large.classList.remove('open');
    }
}