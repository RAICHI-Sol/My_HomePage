<?php
/*******************************************************************
* function
*******************************************************************/

/************************************************
SNSアカウントのURLの表示
 *************************************************/
function create_image_URL($url,$dir,$img_dr,$name,$style="none",$id = "none")
{
    echo '<a target = "_blank" href='.$url.'>';
    create_image($dir,$img_dr,$name,$style,$id);
    echo '</a>';
}

/************************************************
　imageの作成
 *************************************************/
function create_image($dir,$img_dr,$name,$id = "none",$style="none")
{
    echo <<< END_OF_TEXT
        <img src = "/php_test/image/$dir/$img_dr" alt = "$name"
    END_OF_TEXT;

    if($style != "none")
    {
        echo 'class = "'.$style.'"';
    }
    
    if($id != "none")
    {
        echo 'id = "'.$id.'"';
    }
    echo '>';
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

    $fp = fopen(LOGO_PATH,"r");
    if(flock($fp,LOCK_EX))
    {
        while($line = fgets($fp))
        {
            list($url,$img_dr,$name) = explode(" ,",$line);
            create_image_URL($url,'logo',$img_dr,$name);
        }
        flock($fp,LOCK_UN);
    }
    else
    {
        _alert("ファイルロックに失敗しました。");
        return;
    }
    fclose($fp);
    if($mode == "Debug"){
        echo '</div><h1>(開発者用)</h1></header>';
    }
    else{
        echo '</div><h1>RAICHI_Official</h1></header>';
    }
    
}

/************************************************
　プロフィールの作成
 *************************************************/
function create_profile()
{
    echo '<ul>';
    $array = array('name' =>'RAICHI(ライチ)');
    foreach($array as $key => $str){
        echo '<li>'.$key.':'.$str.'</li>';
    }
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

/*******************************************************************
* class
*******************************************************************/

/************************************************
　ニュース更新クラス
 *************************************************/
class New_News{
    public $News_list = array();
    public $News_count;

    //コンストラクタ
    public function __construct()
    {
        $fp = fopen(NEWFILE_PATH,"r");
        if(flock($fp,LOCK_EX))
        {
            while($line = fgets($fp))
            {
                list($_date,$_text) = explode(",",$line);
                $_content = $_date.' : '.$_text;
                array_push($this->News_list,$_content);
            }
            flock($fp,LOCK_UN);
        }
        else
        {
            _alert("ファイルロックに失敗しました。");
            return;
        }
        fclose($fp);
    }

    //書き込み処理
    public function append($_text)
    {
        $fp = fopen(NEWFILE_PATH,"r+");
        if(flock($fp,LOCK_EX))
        {
            $_contents = null;
            while($line = fgets($fp))
            {
                $_contents .= $line; 
            }
            rewind($fp);
            date_default_timezone_set('Asia/Tokyo');
            $_contents = date('Y/m/d').','.$_text."\n".$_contents;
            fwrite($fp,$_contents);
            flock($fp,LOCK_UN);
        }
        else
        {
            _alert("ファイルロックに失敗しました。");
            return;
        }
        fclose($fp);
    }

    //表示
    public function print_news()
    {
        echo <<< END_OF_TEXT
            <h1>News</h1>
            <div class = "Scrollarea">
        END_OF_TEXT;

        foreach($this->News_list as $str)
        {
            echo  '<p class = "news_line">'.$str.'</p>';
        }
        echo '</div>';
    }
}


/************************************************
　画像更新クラス
 *************************************************/
class Imag_Slide{
    public $image_list;

    public $count_max = 0;

    //コンストラクタ
    public function __construct()
    {   
        $this->fileupload();
        $this->load();

        echo <<<EOM
            <script type="text/javascript">
                var count_max = {$this->count_max};
            </script>
        EOM;
    }
    
    public function load()
    {
        $fp = fopen(IMG_PATH,"r");
        if(flock($fp,LOCK_EX))
        {
            while($line = fgets($fp))
            {   
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
            flock($fp,LOCK_UN);
        }
        else
        {
            _alert("ファイルロックに失敗しました。");
            return;
        }
        fclose($fp);
    }

    public function append($img,$name,$comment)
    {
        $fp = fopen(IMG_PATH,"a");
        if(flock($fp,LOCK_EX))
        {
            date_default_timezone_set('Asia/Tokyo');
            $_contents = "\n".date('Y/m/d').','.$img.','.$name.','.$comment;
            fwrite($fp,$_contents);
            flock($fp,LOCK_UN);
        }
        else
        {
            _alert("ファイルロックに失敗しました。");
            return;
        }
        fclose($fp);
    }

    public function fileupload()
    {
        $storedir = DOCUMENT_PATH."/image/illust/";
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $tmp_file = $_FILES['datafile']['tmp_name'];
            $filename = $_FILES['datafile']['name'];
            $title_name = $_POST['name'];
            $comment = $_POST['comment'];
            
            if($_FILES['datafile']['error'] !=0){
                _alert('アップロードが失敗しました。');
            }
            else{
                if(is_uploaded_file($tmp_file)){
                    if(move_uploaded_file($tmp_file,$storedir.$filename)){
                       _alert('「'.$filename.'」をアップロードしました。');
                       $this->append($filename,$title_name,$comment);
                    }
                    else{
                        _alert('「'.$filename.'」をアップロードできませんでした。');
                    }
                }
                else{
                    _alert('ファイルが選択されていません。');
                }
            }
        }
    }

    public function List_get($num,$index)
    {
       return $this->image_list[$num][$index];
    }

    public function Show_Image()
    {
        $max = $this->count_max-1;
        
        echo '<div class = "frame"><div class = "slide">';
        for($i = $max,$j = 0;$i >= 0;$i--,$j++)
        {
            $img = $this->List_get($i,'img');
            $comment = $this->List_get($i,'comment');
            create_image('illust',$img,$comment,$j);
            if($j > 3){
                break;
            }
        }
        echo '</div>';
        create_submit('submit right','scroll_right',1,PLUS,">>");
        create_submit('submit left','scroll_left',$max,MINUS,"<<");

        echo '<div class = "botton_frame">';
        for($i = $max,$j = 0;$i >= 0;$i--,$j++){
            create_submit('botton','botton'.$j,$j,$j,"");
            if($j > 3){
                break;
            }
        }
        echo '</div></div>';
    }

    public function Show_Gallery()
    {
        $max = $this->count_max - 1;

        echo '<div class = "gallary">';
        for($i = $max;$i >= 0;$i--)
        {
            $img     = $this->List_get($i,'img');
            $comment = $this->List_get($i,'comment');
            $name    = $this->List_get($i,'name');

            echo '<div class = "image_text">';
                create_image('illust',$img,$comment);
                echo "<p>{$name}</p>";
            echo '</div>';
        }
        echo '</div>';
    }
}


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

function _alert($str){
    echo '<script>alert("'.$str.'");</script>';
}

/************************************************
　メインコンテンツの作成
 *************************************************/
function main_contents($content_name,$mode) 
{
    create_header($mode);
    create_menu($mode);

    echo <<< END_OF_TEXT
    <main class = "contents">
        <h1>$content_name</h1>
    END_OF_TEXT;

    $Image = new Imag_Slide();

    switch($content_name)
    {
        case "HOME":
            $News = new New_News();
            $Image->Show_Image();
            $News->print_news();
            break;
        case "PROFILE":
            create_profile();
            break;
        case "GALLERY":
            $Image->Show_Gallery();
            if($mode == "Debug"){
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