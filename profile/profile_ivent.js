/************************************************
gallery_ivent
*************************************************/
$('#updata_data').on('change',function (event){
    var reader = new FileReader();
    var file = event.target.files[0];

    reader.onload = function (event)
    {
        $("#preview_prof").attr('src', event.target.result);
        file_name = $('#updata_data').prop('files')[0].name;
        $("#image_name1").text(file_name);
    }
    reader.readAsDataURL(file);
});