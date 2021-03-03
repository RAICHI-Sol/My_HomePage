<?php
/************************************************
include
*************************************************/
include(DOCUMENT_PATH."/base_class.php");
include(DOCUMENT_PATH."/image_slide.php");

/************************************************
クラス名：Datafile_Form
機能：画像アップロード用フォーム作成
継承元：Form
*************************************************/
class Datafile_Form extends Form{

    //抽象メソッド(create_input)
    public function create_input()
    {
        echo '<p id = "upload">【 TITLE 】</p>';
        Input::Text(40,'タイトル');
        tag_p("【 COMMENT 】");
        Input::Textarea(4,40);
        tag_p("【 IMAGE 】");
        Input::File(".png, .jpg, .jpeg",'upload_data','req');
        echo '<label for = "upload_data">ファイルアップロード</label>';
        echo  '<span id = "image_name">選択されていません。</span><br>';
        Input::Submit("submit","送信");
        Input::Submit("reset","リセット");
    }

    //イメージのプレビュー
    public function prev_image()
    {
        echo '<div class = "prev"><img id = "preview"></div>';
    }
}

/************************************************
クラス名：Delete_Form
機能：画像アップロード用フォーム作成
継承元：Form
*************************************************/
class Delete_Form extends Form
{
    public $img;

    //イメージの取得
    public function get_img($img)
    {
        $this->img = $img;
    }

    //抽象メソッド(create_input)
    public function create_input()
    {
        global $icon;
        Input::Hidden("delete_img",$this->img);
        Input::Image($icon['delete_box']);
    }
}


/************************************************
関数名：create_gallery_server
機能：サーバー側のフォームの作成(gallery)
*************************************************/
function create_gallery_server(){
    $form   = new Datafile_Form(GALLERY_SERVER,'upload',ENCODE_FILE);
    $form->create();
    $form->prev_image();
}

?>