<?php
/************************************************
*
親クラスの宣言
*
*************************************************/


/****************************************************
クラス名：File_prosess
機能：ファイル処理クラス(抽象クラス)
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
クラス名：Form
機能：フォーム作成クラス(抽象クラス)
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
クラス名：Image
機能：画像生成クラス
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
            <img src = "/image/{$this->dir_file}/$img_name" alt = "$comment"
        END_OF_TEXT;
        echo $this->add_attribute("class",$this->style);
        echo $this->add_attribute("id",$id);

        echo ' >';
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
クラス名：Input
機能：入力要素クラス
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
クラス名：Header
機能：ヘッダーの作成用クラス
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
        return ($mode == "Debug")?'For developers':'RAITA_Portfolio';
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
*
function
*
*************************************************/

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
        <a href = "/$page" target="contents">$text</a><br>
    END_OF_TEXT;
}


/************************************************
関数名：create_menu
機能：メニュー項目の全表示
*************************************************/
function create_menu($mode)
{
    if($mode =="Debug"):
        $array = array(
            'PROFILE' => 'profile/profile_server.php',
            'GALLERY' => 'gallery/gallery_server.php',
        );
    else:
        $array = array(
            'TOP' => 'contents/contents.php',
            'PROFILE' => 'profile/profile.php',
            'GALLERY' => 'gallery/gallery.php',
        );
    endif;

    foreach($array as $name => $page):
        change_page($page,$name);
    endforeach;
}

/************************************************
関数名：create_header
機能：ヘッダーの作成
*************************************************/
function create_header($mode)
{
    $Head = new Header(LOGO_PATH);
    $Head->read();
    $Head->close();
    echo '<h1>'.$Head->print_title($mode).'</h1>';
}

/************************************************
関数名：create_footer
機能：フッターの作成
 *************************************************/
function create_footer()
{
    tag_p('Copyright © 2021 RAICHI All Rights Reserved.');
}

?>