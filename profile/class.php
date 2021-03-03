<?php
/************************************************
*
画面更新クラスの宣言
*
*************************************************/


/************************************************
include
*************************************************/
include(DOCUMENT_PATH."/base_class.php");

/************************************************
クラス名：Profile
機能：アイコン画像及びプロフィールに関する処理
継承元：File_prosess
*************************************************/
class Profile extends File_prosess{

    public $profile = null;
    public $usrname = null;
    public $image;
    public $count = 1;

    //プロフィールの受け渡し
    public function put_profile()
    {
        return [$this->profile,$this->usrname,$this->image];
    }

    //抽象メソッドの具現化(read_process)
    public function read_process($line)
    {
        if($this->count == 1):
            list($label,$name) = explode(":",rtrim($line));
            $this->usrname = $name;
        elseif($this->count == 2):
            list($label,$img) = explode(":",rtrim($line));
            $this->image = $img;
        else:
            $this->profile .= $line;
        endif;
        $this->count++;
    }

    //抽象メソッドの具現化(write_process)
    public function write_process($str)
    {
        $_contents = null;
        $_text =null;
        $count = 0;
        while($line = fgets($this->fp)):
            switch($count):
                case 0:
                    list($label,$name) = explode(":",rtrim($line));
                    $this->usrname = ($this->usrname == "") ? $name:$this->usrname;
                    $_contents .= 'name:'.$this->usrname.PHP_EOL;
                    break;
                case 1:
                    list($label,$img) = explode(":",rtrim($line));
                    $this->image = ($this->image == "") ? $img:$str;
                    $_contents .= 'image:'.$this->image;
                    break;
                default:
                    $_text .= $line;
                    break;
            endswitch;
            $count++;
        endwhile;

        if($this->profile != ""):
            $_contents .= PHP_EOL.$this->profile;
            $this->profile = "";
        elseif($_text != ""):
            $_contents .= PHP_EOL.$_text;
        endif;

        ftruncate($this->fp,0);
        rewind($this->fp);
        return $_contents;
    }

    //サーバーからの受信
    public function request()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST'):
            if(isset($_POST['update'])):
                $this->fileupdate();
            endif;
            $this->write($this->image);
            $this->close();
        endif;
    }

    //画像のアップロード
    public function fileupdate(){
        if($_FILES['datafile']['name'] != ''):
            $tmp_file = $_FILES['datafile']['tmp_name'];
            $filename = $_FILES['datafile']['name'];

            if($this->fileupload_check($tmp_file,STORE_ICON,$filename) == 0):
                $this->image = $filename;
            endif;
        endif;
        $this->usrname = $_POST['name'];
        $this->profile = $_POST['comment'];
    }

    //プロフィールの表示
    public function Show_Profile(){
        echo '<div class = "profile"><ul>';
        echo '<li>'.$this->usrname.'</li>';
        tag_p($this->profile);
        echo '</ul>';
        $IMG = new Image('icon');
        $IMG->create($this->image,'アイコン');
        echo '</div>';
    }
}



/************************************************
クラス名：Update_Form
機能：プロフィールアップデート用フォーム作成
継承元：Formクラス
*************************************************/
class Update_Form extends Form
{
    public $contents;
    public $usrname;
    public $image;

    //プロフィールの取得
    public function get_profile($contents,$usrname,$image)
    {
        $this->contents = $contents;
        $this->usrname = $usrname;
        $this->image = $image;
    }

    //抽象メソッド(create_input)
    public function create_input()
    {
        tag_p("【 USR_NAME 】");
        Input::Text(40,'ユーザーネーム',$this->usrname);
        tag_p("【 PROFILE 】");
        Input::Textarea(8,40,$this->contents);
        echo '<br>';
        tag_p("【 IMAGE 】");
        Input::File(".png, .jpg, .jpeg",'updata_data');
        echo '<label for = "updata_data">ファイルアップロード</label>';
        echo '<span id = "image_name1">選択されていません。</span><br>';
        Input::Submit("submit","送信");
        Input::Submit("reset","リセット");
    }

    //イメージのプレビュー
    public function prev_image()
    {
        echo <<< EOT
            <div class = "prev">
                <img id = "preview_prof" src = "/php_test/image/icon/{$this->image}">
            </div>
        EOT;
    }
}

/************************************************
関数名：create_profile
機能：プロフィールの作成
*************************************************/
function create_profile($mode)
{
    $PROF = new Profile(PROF_PATH);
    $PROF->request();
    $PROF->read();
    $PROF->close();
    $PROF->Show_Profile();
    list($profile,$name,$img) = $PROF->put_profile();

    //サーバー専用のフォーム
    if($mode == "Debug"):
        $form = new Update_Form(PROFILE_SERVER,'update',ENCODE_FILE);
        echo '<div class = "update">';
        $form->get_profile($profile,$name,$img);
        $form->create();
        $form->prev_image();
        echo '</div>';
    endif;
}
?>