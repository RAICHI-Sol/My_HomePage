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

$('#upload_data').on('change',function (event){
    var reader = new FileReader();
    var file = event.target.files[0];

    reader.onload = function (event)
    {
        $("#preview").attr('src', event.target.result);
        file_name = $('#upload_data').prop('files')[0].name;
        $("#image_name").text(file_name);
    }
    reader.readAsDataURL(file);
});