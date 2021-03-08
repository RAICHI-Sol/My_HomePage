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
    public function read(){
        $this->fp = fopen($this->file_path,"r");
        if($this->lock() == 0)
        {
            while($line = fgets($this->fp)){
                $this->read_process($line);
            }
            $this->unlock();
        }
    }

    //ファイルのテキスト追加
    public function append($str){
        $this->fp = fopen($this->file_path,"a");
        if($this->lock() == 0){
            fwrite($this->fp,$str);
            $this->unlock();
        }
    }

    //ファイルの書き込み
    public function write($str){
        $this->fp = fopen($this->file_path,"r+");

        if($this->lock() == 0){
            $_contents = $this->write_process($str);
            fwrite($this->fp,$_contents);
            $this->unlock();
        }
    }

    //ファイルロック
    public function lock()
    {
        if(!flock($this->fp,LOCK_EX)){
            _alert("ファイルロックに失敗しました。");
            return 1;
        }
        else{
            return 0;
        }
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
            <div class = "{$this->f_name}">
            <form action = "{$this->action}" method = "post"
            target = "contents" enctype = "{$this->enctype}">
        EOT;
        Input::Hidden($this->f_name,1);
        $this->create_input();
        echo '</form></div>';
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
            <img src = "/php_test/image/$this->dir_file/$img_name" alt = "$comment"
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
        echo '<input type = "text" name = "name" size = "'.$size.'"';
        if($temp != 'none')
        {
            echo 'value = "'.$temp.'"';
        }
        echo 'placeholder = "'.$holder.'" required>';
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
    public static function File($accrpt)
    {
        echo '<input type="file" name="datafile" accept = "'.$accrpt.'" required>';
    }

    //ボタン(static)
    public static function Submit($type,$text){
        echo '<p><input type = '.$type.' value = '.$text.'></p>';
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
        while($line = fgets($this->fp))
        {
            $_contents .= $line;
        }
        rewind($this->fp);
        return date('Y/m/d').','.$str."\n".$_contents;
    }
    //表示
    public function print_news()
    {
        echo '<h1>News</h1>';
        echo '<div class = "Scrollarea">';

        foreach($this->News_list as $str)
        {
            echo  '<p>'.$str.'</p>';
        }
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
        while($line = fgets($this->fp))
        {
            list($date,$img_dr,$name,$comment) = explode(",",$line);
            if($img_dr != $str)
            {
                if($count >= ($count_max - 1))
                {
                    $line = rtrim($line);
                }
                $file_str .= $line;
            }
            $count++;
        }
        ftruncate($this->fp,0);
        rewind($this->fp);
        return $file_str;
    }

    //画像に対しての処理
    public function filerequest()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            if(isset($_POST['upload']))
            {
                $this->fileupload();
            }
            else if(isset($_POST['delete']))
            {
                $this->filedelete();
            }
        }
    }

    //イラストのアップロード
    public function fileupload()
    {
        $tmp_file = $_FILES['datafile']['tmp_name'];
        $filename = $_FILES['datafile']['name'];
        $title_name = $_POST['name'];
        $comment = $_POST['comment'];

        if($_FILES['datafile']['error'] != 0)
        {
            _alert('アップロードが失敗しました。');
            return;
        }

        if(!is_uploaded_file($tmp_file))
        {
            _alert('ファイルが選択されていません。');
            return;
        }

        if(move_uploaded_file($tmp_file,STORE_ILLUST.$filename))
        {
            _alert('「'.$filename.'」をアップロードしました。');
            $_contents = "\n".date('Y/m/d').','.$filename.','.$title_name.','.$comment;
            $this->append($_contents);
            $this->close();
        }
        else
        {
            _alert('「'.$filename.'」をアップロードできませんでした。');
        }
    }

    //イラストのデリート
    public function filedelete()
    {
        $delete_img = $_POST['delete_img'];

        $file = STORE_ILLUST.$delete_img;

        if(file_exists($file))
        {
            unlink($file);
            $this->write($delete_img);
            $this->close();
            _alert('「'.$delete_img.'」を削除しました');
        }

        else{
            _alert('「'.$delete_img.'」が存在しません');
        }

    }

    //イメージの表示(HOME)
    public function Show_Image()
    {
        $max = $this->count_max-1;

        echo '<div class = "frame"><div class = "slide">';
        for($i = $max,$j = 0;$i >= 0 && $j < 5;$i--,$j++)
        {
            echo '<div class = "flame">';
            $this->list_reference($i,$img,$comment,$name,$date);
            $this->img->create($img,$comment,$j);
            echo '</div>';
        }
        echo '</div>';
        create_submit('submit right','scroll_right',PLUS,">>");
        create_submit('submit left','scroll_left',MINUS,"<<");

        echo '<div class = "botton_frame">';
        for($i = $max,$j = 0;$i >= 0 && $j < 5;$i--,$j++)
        {
            create_submit('button','button'.$j,$j,"");
        }
        echo '</div></div>';
    }

    //イメージの表示(gallary)
    public function Show_Gallery($mode)
    {
        $box = new Delete_Form(GALLERY_SERVER,'delete');

        $max = $this->count_max - 1;

        echo '<div class = "gallary">';
        for($i = $max;$i >= 0;$i--)
        {
            $id = 'img'.$i;
            $this->list_reference($i,$img,$comment,$name,$date);
            echo '<div class = "image_text"><label for = "'.$id.'">';
            $this->img->create($img,$comment);
            if($mode == 'Debug')
            {
                $box->get_img($img);
                $box->create();
            }
            echo '<p>'.$name.'</p>';
            Input::Checkbox('img'.$i);
            echo '</div>';
            $this->image_large($i,$id);
        }
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
            <span>$date</span></p>
            </div>
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
        $this->img = new Image('logo');
    }

    //抽象メソッドの具現化(read_process)
    public function read_process($line)
    {
        list($url,$img_dr,$name) = explode(" ,",$line);
        $this->img->create_URL($url,$img_dr,$name);
    }

    //抽象メソッドの具現化(write_process)
    public function write_process($str)
    {
        return $str;
    }

    //タイトルの表示
    public function print_title($mode)
    {
        if($mode == "Debug")
        {
            echo '<h1>(開発者用)</h1>';
        }
        else
        {
            echo '<h1>RAICHIのホームページ</h1>';
        }
    }
}

/************************************************
Profileクラス
*************************************************/
class Profile extends File_prosess{

    public $profile = null;
    public $usrname = null;
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

    //抽象メソッドの具現化(read_process)
    public function read_process($line)
    {
        if($this->count == 1){
            list($name_label,$name) = explode(":",rtrim($line));
            $this->usrname = $name;
            echo '<li>'.$line.'</li>';
        }
        else{
            $this->profile .= $line;
            echo '<p>'.$line.'</p>';
        }
        $this->count++;
    }

    //抽象メソッドの具現化(write_process)
    public function write_process($str)
    {
        ftruncate($this->fp,0);
        rewind($this->fp);
        return $str;
    }

    //サーバーからの受信
    public function request()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            if(isset($_POST['update']))
            {
                $this->fileupdate();
            }
        }
    }

    //プロフィールの更新
    public function fileupdate(){
        $usrname = $_POST['name'];
        $profile = $_POST['comment'];
        $_contents = null;

        $_contents .= 'name:'.$usrname.PHP_EOL;
        $_contents .= $profile;
        $this->write($_contents);
        $this->close();
        _alert('プロフィールを更新しました。');
    }
}

/************************************************
画像アップロード用フォーム作成クラス
*************************************************/
class Datafile_Form extends Form{

    //抽象メソッド(create_input)
    public function create_input()
    {
        echo '<p id = "upload">タイトル：※<br>';
        Input::Text(40,'タイトル').'</p>';
        echo '<p>説明：<br>';
        Input::Textarea(4,40).'</p>';
        echo '<p>ファイル：※<br>';
        Input::File(".png, .jpg, .jpeg").'</p>';
        Input::Submit("submit","送信");
        Input::Submit("reset","リセット");
    }
}

/************************************************
画像アップロード用フォーム作成クラス
*************************************************/
class Delete_Form extends Form
{
    public $img;

    //イメージの取得
    public function get_img($img){
        $this->img = $img;
    }

    //抽象メソッド(create_input)
    public function create_input()
    {
        Input::Hidden("delete_img",$this->img);
        $icon = "/php_test/image/icon/delete_box.jpeg";
        Input::Image($icon);
    }
}

/************************************************
プロフィールアップデート用フォーム作成クラス
*************************************************/
class Update_Form extends Form
{
    public $contents;
    public $usrname;

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

    //抽象メソッド(create_input)
    public function create_input()
    {
        echo '<p>ユーザーネーム：※<br>';
        Input::Text(40,'ユーザーネーム',$this->usrname).'</p>';
        echo '<p>プロフィール：※<br>';
        Input::Textarea(8,40,$this->contents).'</p>';
        Input::Submit("submit","送信");
        Input::Submit("reset","リセット");
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
    if($style != "button"){
        echo <<< END_OF_TEXT
            <input type = "button" class = "$style" onclick = "Chenge_scroll($add);" value = "$text">
        END_OF_TEXT;
    }
    else{
        echo <<< END_OF_TEXT
            <input type = "button" id = "$id" onclick = "Chenge_button($add);" value = "$text">
        END_OF_TEXT;
    }
}

/************************************************
アラート
 *************************************************/
function _alert($str){
    echo '<script>alert("'.$str.'");</script>';
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
    if($mode =="Debug"){
        $array = array(
            'PROFILE' => 'profile_server.php',
            'GALLERY' => 'gallery_server.php',
        );
    }
    else{
        $array = array(
            'TOP' => 'contents.php',
            'PROFILE' => 'profile.php',
            'GALLERY' => 'gallery.php',
        );
    }
    foreach($array as $name => $page)
    {
        change_page($page,$name);
    }
    echo '</div>';
}

/************************************************
　ヘッダーの作成
 *************************************************/
function create_header($mode)
{
    echo '<header class = "title">';
    echo '<div class = "logo">';

    $Head = new Header(LOGO_PATH);
    $Head->read();
    $Head->close();
    echo '</div>';
    $Head->print_title($mode);
    echo '</header>';
}

/************************************************
　プロフィールの作成
 *************************************************/
function create_profile($mode)
{
    echo '<ul>';
    $prof = new Profile(PROF_PATH);
    $prof->request();
    $prof->read();
    $prof->close();
    $profile = $prof->put_profile();
    $name = $prof->put_usrname();
    echo '</ul>';

    //サーバー専用のフォーム
    if($mode == "Debug")
    {
        $form = new Update_Form(PROFILE_SERVER,'update');
        $form->get_profile($profile);
        $form->get_usrname($name);
        $form->create();
    }
}

/************************************************
　フッターの作成
 *************************************************/
function create_footer()
{
    echo <<< END_OF_TEXT
    <footer>
        <p>Copyright © 2021 RAICHI All Rights Reserved.</p>
    </footer>
    END_OF_TEXT;
}

/************************************************
メインコンテンツの作成
*************************************************/
function main_contents($content_name,$mode)
{
    date_default_timezone_set('Asia/Tokyo');
    create_header($mode);
    create_menu($mode);

    echo '<main class = "contents">';
    echo '<h1>'.$content_name.'</h1>';

    $Image = new Imag_Slide(IMG_PATH);

    switch($content_name)
    {
        case "HOME":
            $Image->Show_Image();
            $News = new New_News(NEWFILE_PATH);
            $News->read();
            $News->close();
            $News->print_news();
            break;
        case "PROFILE":
            create_profile($mode);
            break;
        case "GALLERY":
            $Image->Show_Gallery($mode);
            //サーバー専用のフォーム
            if($mode == "Debug")
            {
                $form   = new Datafile_Form(GALLERY_SERVER,'upload',ENCODE_FILE);
                $form->create();
            }
            break;
        default:
            break;
    }
    echo "</main>";
    create_footer();
}
?>