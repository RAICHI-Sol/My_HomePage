<?php
/************************************************
*
親クラス
*
*************************************************/
/****************************************************
ファイル処理クラス(抽象クラス)
****************************************************/
abstract class File_prosess{
    public $file_path;
    protected $fp;

    //コンストラクタ
    public function __construct($file_path)
    {
        $this->file_path = $file_path;
    }

    //ファイルの読み込み
    public function read()
    {
        $this->fp = fopen($this->file_path,"r");
        if($this->lock() == 0):
            while($line = fgets($this->fp)):
                $this->read_process($line);
            endwhile;
            $this->unlock();
        endif;
    }

    //ファイルのテキスト追加
    public function append($str)
    {
        $this->fp = fopen($this->file_path,"a");
        if($this->lock() == 0):
            fwrite($this->fp,$str);
            $this->unlock();
        endif;
    }

    //ファイルの書き込み
    public function write($str)
    {
        $this->fp = fopen($this->file_path,"r+");

        if($this->lock() == 0):
            $_contents = $this->write_process($str);
            fwrite($this->fp,$_contents);
            $this->unlock();
        endif;
    }

    //ファイルロック
    public function lock()
    {
        if(!flock($this->fp,LOCK_EX)):
            _alert("ファイルロックに失敗しました。");
            return 1;
        else:
            return 0;
        endif;
    }

    //ファイルのアップロードのチェック
    public function fileupload_check($tmp_file,$store_dir,$filename)
    {
        if($_FILES['datafile']['error'] != 0){
            _alert('アップロードが失敗しました。');
            return 1;
        }

        if(!is_uploaded_file($tmp_file)){
            _alert('ファイルが選択されていません。');
            return 1;
        }

        if(move_uploaded_file($tmp_file,$store_dir.$filename)):
            _alert('「'.$filename.'」をアップロードしました。');
            return 0;
        else:
            _alert('「'.$filename.'」をアップロードできませんでした。');
            return 1;
        endif;
    }

    //ファイルアンロック
    public function unlock(){
        flock($this->fp,LOCK_UN);
    }

    //クローズ
    public function close(){
        fclose($this->fp);
    }

    //抽象メソッド(読み込み処理)
    abstract public function read_process($line);

    //抽象メソッド(書き込み処理)
    abstract public function write_process($str);

}

/****************************************************
フォーム作成クラス(抽象クラス)
****************************************************/
abstract class Form{
    public $action;
    public $enctype;
    public $f_name;

    //コンストラクタ
    public function __construct($action,$f_name,$enctype = ENCODE_NOMAL)
    {
        $this->$action = $action;
        $this->enctype = $enctype;
        $this->f_name = $f_name;
    }

    //フォームの作成
    public function create(){
        echo <<< EOT
            <form action = "{$this->action}" method = "post"
            target = "contents" enctype = "{$this->enctype}">
        EOT;
        Input::Hidden($this->f_name,1);
        $this->create_input();
        echo '</form>';
    }

    //抽象メソッド
    abstract public function create_input();

}

/****************************************************
画像生成クラス
****************************************************/
class Image{
    public $dir_file;
    public $style;

    //コンストラクタ
    public function __construct($dir_file,$style = "none"){
        $this->dir_file = $dir_file;
        $this->$style = $style;
    }

    //imageの作成
    public function create($img_name,$comment,$id = "none")
    {
        echo <<< END_OF_TEXT
            <img src = "/php_test/image/{$this->dir_file}/$img_name" alt = "$comment"
        END_OF_TEXT;
        echo $this->add_attribute("class",$this->style);
        echo $this->add_attribute("id",$id);

        echo ' >';
    }

    //URL付きのimage
    function create_URL($url,$img_name,$comment,
    $style = "none",$id = "none",$target="_blank")
    {
        echo '<a target = '.$target.' href='.$url.'>';
        $this->create($img_name,$comment,$style,$id);
        echo '</a>';
    }

    //属性の追加
    public function add_attribute($atrb_name,$atrb){

        if($atrb != "none" && $atrb != ""){
            $element =  $atrb_name. ' = "'.$atrb.'"';
            print_r($element);
        }
    }
}

/****************************************************
入力要素クラス
****************************************************/
class Input{

    //テキスト(static)
    public static function Hidden($text,$value)
    {
        echo '<input type = "hidden" name = "'.$text.'" value = "'.$value.'">';
    }

    //テキストボックス(static)
    public static function Text($size,$holder,$temp = 'none')
    {
        echo '<input type = "text" name = "name" size = "'.$size.'"
        placeholder = "'.$holder.'" required ';
        echo ($temp != 'none') ?'value = "'.$temp.'">':'>';
    }
    //テキストエリア(static)
    public static function Textarea($rows,$cols,$temp = "テキスト")
    {
       echo <<< EOT
       <textarea name = "comment" rows = "$rows" cols = "$cols"
       placeholder = "テキストを入力">$temp</textarea>
       EOT;
    }
    //ファイル参照(static)
    public static function File($accrpt,$id,$required = "none")
    {
        echo <<<EOT
            <input type="file" id = "$id" name="datafile" accrpt = "$accrpt"
        EOT;
        echo ($required != 'none') ?'required>':'>';
    }

    //ボタン(static)
    public static function Submit($type,$text){
        echo '<input type = '.$type.' value = '.$text.'>';
    }

    //画像ボタン(static)
    public static function Image($img){
        echo '<input type = "image" src = '.$img.'>';
    }

    //チェックボックス(static)
    public static function Checkbox($id){
        echo <<< EOT
        <input type = "checkbox" onchange = "Checkbox('$id');" id = "$id">
        EOT;
    }
}
/************************************************
*
子クラス
*
*************************************************/
/************************************************
ニュース更新クラス
*************************************************/
class New_News extends File_prosess{
    public $News_list = array();
    public $News_count;

    //抽象メソッドの具現化(read_process)
    public function read_process($line)
    {
        list($_date,$_text) = explode(",",$line);
        $_content = $_date.' : '.$_text;
        array_push($this->News_list,$_content);
    }

    //抽象メソッドの具現化(write_process)
    public function write_process($str)
    {
        $_contents = null;
        while($line = fgets($this->fp)):
            $_contents .= $line;
        endwhile;
        rewind($this->fp);
        return date('Y/m/d').','.$str."\n".$_contents;
    }
    //表示
    public function print_news()
    {
        echo '<h1>News</h1>';
        echo '<div class = "Scrollarea">';

        foreach($this->News_list as $str):
            tag_p($str);
        endforeach;
        echo '</div>';
    }
}


/************************************************
画面更新クラス
*************************************************/
class Imag_Slide extends File_prosess{

    public $image_list;
    public $count_max = 0;
    public $img;

    //コンストラクタのオーバーライド
    public function __construct($file_path)
    {
        parent::__construct($file_path);
        $this->filerequest();
        $this->read();
        $this->close();

        echo <<<EOM
            <script type="text/javascript">
                var count_max = {$this->count_max};
            </script>
        EOM;

        $this->img = new Image('illust');
    }

    //抽象メソッドの具現化(read_process)
    public function read_process($line){
        list($date,$img_dr,$name,$comment) = explode(",",$line);

        $this->image_list[$this->count_max] =
        array(
            'date'=>$date,
            'img'=>$img_dr,
            'name'=>$name,
            'comment'=>$comment
        );
        $this->count_max++;
    }

    //抽象メソッドの具現化(write_process)
    public function write_process($str)
    {
        $file_str = null;
        $count = 1;
        $count_max = 0;
        for($count_max = 0;fgets($this->fp);$count_max++);
        rewind($this->fp);
        while($line = fgets($this->fp)):
            list($date,$img_dr,$name,$comment) = explode(",",$line);
            if($img_dr != $str):
                $line = ($count >= ($count_max - 1)) ? rtrim($line) : $line;
                $file_str .= $line;
            endif;
            $count++;
        endwhile;
        ftruncate($this->fp,0);
        rewind($this->fp);
        return $file_str;
    }

    //画像に対しての処理
    public function filerequest()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            if(isset($_POST['upload'])):
                $this->fileupload();
            elseif(isset($_POST['delete'])):
                $this->filedelete();
            endif;
        }
    }

    //イラストのアップロード
    public function fileupload()
    {
        $tmp_file = $_FILES['datafile']['tmp_name'];
        $filename = $_FILES['datafile']['name'];

        if($this->fileupload_check($tmp_file,STORE_ILLUST,$filename)==0):
            $title_name = $_POST['name'];
            $comment = $_POST['comment'];
            $_contents = "\n".date('Y/m/d').','.$filename.','.$title_name.','.$comment;
            $this->append($_contents);
            $this->close();
        endif;
    }

    //イラストのデリート
    public function filedelete()
    {
        $delete_img = $_POST['delete_img'];

        $file = STORE_ILLUST.$delete_img;

        if(file_exists($file)):
            unlink($file);
            $this->write($delete_img);
            $this->close();
            _alert('「'.$delete_img.'」を削除しました');
        else:
            _alert('「'.$delete_img.'」が存在しません');
        endif;
    }

    //イメージの表示(HOME)
    public function Show_Image()
    {
        $max = $this->count_max-1;

        echo '<div class = "frame">';
        echo '<div class = "slide">';
        for($i = $max,$j = 0;$i >= 0 && $j < 5;$i--,$j++):
            echo '<div class = "flame">';
                $this->list_reference($i,$img,$comment,$name,$date);
                $this->img->create($img,$comment,$j);
            echo '</div>';
        endfor;
        echo '</div>';
        create_submit('submit right','scroll_right',PLUS,">>");
        create_submit('submit left','scroll_left',MINUS,"<<");

        echo '<div class = "botton_frame">';
        for($i = $max,$j = 0;$i >= 0 && $j < 5;$i--,$j++):
            create_submit('button','button'.$j,$j,"");
        endfor;
        echo '</div></div>';
    }

    //イメージの表示(gallary)
    public function Show_Gallery($mode)
    {
        $box = new Delete_Form(GALLERY_SERVER,'delete');

        $max = $this->count_max - 1;

        echo '<div class = "gallary">';
        for($i = $max;$i >= 0;$i--):
            $this->list_reference($i,$img,$comment,$name,$date);
            echo <<< EOT
                <div class = "image_text">
                <label for = "img$i">
            EOT;
            $this->img->create($img,$comment);
            if($mode == 'Debug'):
                $box->get_img($img);
                $box->create();
            endif;
            tag_p($name);
            Input::Checkbox('img'.$i);
            echo '</div>';
            $this->image_large($i,'img'.$i);
        endfor;
        echo '</div>';
    }

    //クリック時の拡大画面
    public function image_large($i,$id){

        $this->list_reference($i,$img,$comment,$name,$date);
        echo <<<EOT
            <div class = "image_large" id = "large_$id">
            <label for = "$id">
        EOT;
        $this->img->create($img,$comment);
        echo <<<EOT
            <p><span class = "title">$name</span>
            <span>$date</span></p></div>
        EOT;
    }

    //配列の要素を取得
    public function list_reference($num,&$img,&$comment,&$name,&$date)
    {
        $img        = $this->image_list[$num]['img'];
        $comment    = $this->image_list[$num]['comment'];
        $name       = $this->image_list[$num]['name'];
        $date       = $this->image_list[$num]['date'];
    }
}

/************************************************
Headerクラス
*************************************************/
class Header extends File_prosess
{
    public $img;

    //コンストラクタ
    public function __construct($file_path)
    {
        parent::__construct($file_path);
    }

    //抽象メソッドの具現化(read_process)
    public function read_process($line)
    {
        list($url,$name) = explode(" ,",$line);
        $this->create_logo($url,$name);
    }

    //抽象メソッドの具現化(write_process)
    public function write_process($str)
    {
        return $str;
    }

    //タイトルの表示
    public function print_title($mode)
    {
        echo ($mode == "Debug")?'<h1>(開発者用)</h1>':'<h1>RAICHIのホームページ</h1>';
    }

    //ロゴの作成
    function create_logo($url,$name)
    {
        echo <<< END_OF_TEXT
            <a href = "$url" target="_blank" class = "fab $name"></a>
        END_OF_TEXT;
    }
}

/************************************************
Profileクラス
*************************************************/
class Profile extends File_prosess{

    public $profile = null;
    public $usrname = null;
    public $image;
    public $count = 1;

    //プロフィールの受け渡し
    public function put_profile()
    {
        return $this->profile;
    }

    //ユーザーネームの受け渡し
    public function put_usrname()
    {
        return $this->usrname;
    }

    //イメージの受け渡し
    public function put_image()
    {
        return $this->image;
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
画像アップロード用フォーム作成クラス
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
        echo <<< EOT
            <label for = "upload_data">
                ファイルアップロード
            </label>
            <span id = "image_name">選択されていません。</span><br>
        EOT;
        Input::Submit("submit","送信");
        Input::Submit("reset","リセット");
    }

    //イメージのプレビュー
    public function prev_image()
    {
        echo <<< EOT
            <div class = "prev">
                <img id = "preview">
            </div>
        EOT;
    }
}

/************************************************
画像アップロード用フォーム作成クラス
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
プロフィールアップデート用フォーム作成クラス
*************************************************/
class Update_Form extends Form
{
    public $contents;
    public $usrname;
    public $image;

    //プロフィールの取得
    public function get_profile($contents)
    {
        $this->contents = $contents;
    }

    //ユーザネームの取得
    public function get_usrname($usrname)
    {
        $this->usrname = $usrname;
    }

    //イメージの取得
    public function get_image($image)
    {
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
        echo <<<EOT
            <label for = "updata_data">
                ファイルアップロード
            </label>
            <span id = "image_name1">選択されていません。</span><br>
        EOT;
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
*
function
*
*************************************************/

/************************************************
　submitの作成
 *************************************************/
function create_submit($style,$id,$add,$text)
{
    if($style != "button"):
        echo <<< END_OF_TEXT
            <input type = "button" class = "$style" onclick = "Chenge_scroll($add);" value = "$text">
        END_OF_TEXT;
    else:
        echo <<< END_OF_TEXT
            <input type = "button" id = "$id" onclick = "Chenge_button($add);" value = "$text">
        END_OF_TEXT;
    endif;
}

/************************************************
アラート
 *************************************************/
function _alert($str){
    echo '<script>alert("'.$str.'");</script>';
}

/************************************************
タグp
*************************************************/
function tag_p($str){
    echo '<p>'.$str.'</p>';
}

/************************************************
メニュー項目の表示
 *************************************************/
function change_page($page,$text)
{
    echo <<< END_OF_TEXT
        <a href = "/php_test/contents/$page" target="contents">$text</a><br>
    END_OF_TEXT;
}


/************************************************
メニュー項目の全表示
 *************************************************/
function create_menu($mode)
{
    echo '<div class = "menu">';
    if($mode =="Debug"):
        $array = array(
            'PROFILE' => 'profile_server.php',
            'GALLERY' => 'gallery_server.php',
        );
    else:
        $array = array(
            'TOP' => 'contents.php',
            'PROFILE' => 'profile.php',
            'GALLERY' => 'gallery.php',
        );
    endif;

    foreach($array as $name => $page):
        change_page($page,$name);
    endforeach;
    echo '</div>';
}

/************************************************
　ヘッダーの作成
 *************************************************/
function create_header($mode)
{
    echo '<header class = "title">';

    $Head = new Header(LOGO_PATH);
    $Head->read();
    $Head->close();
    $Head->print_title($mode);
    echo '</header>';
}

/************************************************
　プロフィールの作成
 *************************************************/
function create_profile($mode)
{
    $PROF = new Profile(PROF_PATH);
    $PROF->request();
    $PROF->read();
    $PROF->close();
    $PROF->Show_Profile();
    $profile = $PROF->put_profile();
    $name = $PROF->put_usrname();
    $img  = $PROF->put_image();

    //サーバー専用のフォーム
    if($mode == "Debug"):
        $form = new Update_Form(PROFILE_SERVER,'update',ENCODE_FILE);
        echo '<div class = "update">';
        $form->get_profile($profile);
        $form->get_usrname($name);
        $form->get_image($img);
        $form->create();
        $form->prev_image();
        echo '</div>';
    endif;
}

/************************************************
　フッターの作成
 *************************************************/
function create_footer()
{
    echo '<footer>';
        tag_p('Copyright © 2021 RAICHI All Rights Reserved.');
    echo '</footer>';
}

/************************************************
　サーバー側のフォームの作成(gallery)
*************************************************/
function create_gallery_server(){
    $form   = new Datafile_Form(GALLERY_SERVER,'upload',ENCODE_FILE);
    echo '<div class = "upload">';
    $form->create();
    $form->prev_image();
    echo '</div>';
}

/************************************************
メインコンテンツの作成
*************************************************/
function main_contents($content_name,$mode)
{
    echo '<div class = "main_flame">';
    date_default_timezone_set('Asia/Tokyo');
    create_menu($mode);

    $Image = new Imag_Slide(IMG_PATH);

    switch($content_name)
    {
        case "HOME":
            $Image->Show_Image();
            echo '<main class = "contents">';
            $News = new New_News(NEWFILE_PATH);
            $News->read();
            $News->close();
            $News->print_news();
            break;
        case "PROFILE":
            echo '<main class = "contents">';
            echo '<h1>'.$content_name.'</h1>';
            create_profile($mode);
            break;
        case "GALLERY":
            echo '<main class = "contents">';
            echo '<h1>'.$content_name.'</h1>';
            $Image->Show_Gallery($mode);
            //サーバー専用のフォーム
            if($mode == "Debug"):
                create_gallery_server();
            endif;
            break;
        default:
            break;
    }
    echo "</main></div>";
}
?>