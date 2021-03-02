<?php
/************************************************
*
親クラス
*
*************************************************/
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
        $this->fp = fopen(NEWFILE_PATH,"r+");

        if($this->lock() == 0){
            $_contents = $this->write_process($this->fp,$str);
            fwrite($this->fp,$_contents);
            $this->unlock();
        }
    }

    //ファイルに書き込むテキストを一行ずつ検討
    public function write_process($str)
    {
        $file_str = null;
        while($line = fgets($this->fp)){
            $file_str .= $line;
        }
        return $file_str;
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

    //抽象メソッド
    abstract public function read_process($line);

}

/****************************************************
画像読み込みクラス
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

    //読み込み時の処理
    public function read_process($line)
    {
        list($_date,$_text) = explode(",",$line);
        $_content = $_date.' : '.$_text;
        array_push($this->News_list,$_content);
    }

    //書き込み処理
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
            echo  '<p class = "news_line">'.$str.'</p>';
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
        $this->fileupload();
        $this->read();

        echo <<<EOM
            <script type="text/javascript">
                var count_max = {$this->count_max};
            </script>
        EOM;

        $this->img = new Image('illust');
    }

    //読み込み時の処理
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

    //アップロード
    public function fileupload()
    {
        $storedir = DOCUMENT_PATH."/image/illust/";
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $tmp_file = $_FILES['datafile']['tmp_name'];
            $filename = $_FILES['datafile']['name'];
            $title_name = $_POST['name'];
            $comment = $_POST['comment'];

            if($_FILES['datafile']['error'] ==0)
            {
                if(is_uploaded_file($tmp_file))
                {
                    if(move_uploaded_file($tmp_file,$storedir.$filename))
                    {
                       _alert('「'.$filename.'」をアップロードしました。');
                       $_contents = "\n".date('Y/m/d').','.$filename.','.$title_name.','.$comment;
                       $this->append($_contents);
                    }
                    else
                    {
                        _alert('「'.$filename.'」をアップロードできませんでした。');
                    }
                }
                else
                {
                    _alert('ファイルが選択されていません。');
                }
            }
            else
            {
                _alert('アップロードが失敗しました。');
            }
        }
    }

    //イメージの表示(HOME)
    public function Show_Image()
    {
        $max = $this->count_max-1;

        echo '<div class = "frame"><div class = "slide">';
        for($i = $max,$j = 0;$i >= 0;$i--,$j++)
        {
            $this->list_reference($i,$img,$comment,$name);
            $this->img->create($img,$comment,$j);
            if($j > 3)
            {
                break;
            }
        }
        echo '</div>';
        create_submit('submit right','scroll_right',1,PLUS,">>");
        create_submit('submit left','scroll_left',$max,MINUS,"<<");

        echo '<div class = "botton_frame">';
        for($i = $max,$j = 0;$i >= 0;$i--,$j++)
        {
            create_submit('botton','botton'.$j,$j,$j,"");
            if($j > 3)
            {
                break;
            }
        }
        echo '</div></div>';
    }

    //イメージの表示(gallary)
    public function Show_Gallery()
    {
        $max = $this->count_max - 1;

        echo '<div class = "gallary">';
        for($i = $max;$i >= 0;$i--)
        {
            $this->list_reference($i,$img,$comment,$name);
            echo '<div class = "image_text">';
                $this->img->create($img,$comment);
                echo "<p>{$name}</p>";
            echo '</div>';
        }
        echo '</div>';
    }

    public function list_reference($num,&$img,&$comment,&$name)
    {
        $img        = $this->image_list[$num]['img'];
        $comment    = $this->image_list[$num]['comment'];
        $name       = $this->image_list[$num]['name'];
    }
}

/************************************************
Headerクラス
*************************************************/
class Header extends File_prosess
{
    public $img;
    public function __construct($file_path)
    {
        parent::__construct($file_path);
        $this->img = new Image('logo');
    }
    public function read_process($line)
    {
        list($url,$img_dr,$name) = explode(" ,",$line);
        $this->img->create_URL($url,$img_dr,$name);
    }
    public function print_title($mode)
    {
        if($mode == "Debug")
        {
            echo '<h1>(開発者用)</h1>';
        }
        else
        {
            echo '<h1>RAICHI_Official</h1>';
        }
    }
}

/************************************************
　submitの作成
 *************************************************/
function create_submit($style,$id,$num,$add,$text)
{
    $url = "/php_test/contents/contents.php#";
    if($style != "botton"){
        echo <<< END_OF_TEXT
            <a id = "$id" href = "$url$num" target="contents">
            <input type = "submit" class = "$style" onclick = "Chenge_num($add,'scroll');" value = "$text">
            </a>
        END_OF_TEXT;
    }
    else{
        echo <<< END_OF_TEXT
            <a href = "$url$num" target = "contents">
            <input type = "submit" id = "$id" class = "$style" onclick = "Chenge_num($add,'$style');" value = "$text">
            </a>
        END_OF_TEXT;
    }
}

/************************************************
Profileクラス
*************************************************/
class Profile extends File_prosess{

    public function read_process($line)
    {
        echo '<li>'.$line.'</li>';
    }
}

/************************************************
*
function
*
*************************************************/

/************************************************
画像アップロード用フォームの作成
*************************************************/
function datafile_upload(){
    echo <<< EOT
    <div class = "upload">
        <form action = "/php_test/contents/gallery_server.php" method = "post"
        target = "contents" enctype="multipart/form-data"　name = "upload">
            <p>タイトル：<br>
            <input type="text" name="name" size="40"></p>
            <p>説明：<br>
            <textarea name="comment" rows="4" cols="30">テキストを入力</textarea></p>
            <p>ファイル<br>
            <input type="file" name="datafile"　accept=".png, .jpg, .jpeg" required></p>
            <p><input type="submit" value="送信"></p>
        <form>
    </div>
    EOT;
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
            'TOP' => 'contents_server.php',
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
function create_profile()
{
    echo '<ul>';
    $prof = new Profile(PROF_PATH);
    $prof->read();
    $prof->close();
    echo '</ul>';
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
            create_profile();
            break;
        case "GALLERY":
            $Image->Show_Gallery();
            if($mode == "Debug")
            {
                datafile_upload();
            }
            break;
        default:
            break;
    }
    echo "</main>";
    create_footer();
}
?>