<?php
/************************************************
*
画面更新クラスの宣言
*
*************************************************/

/************************************************
クラス名：Imag_Slide
機能：イラスト画像の更新・追加・削除
継承元：File_prosess
*************************************************/
class Imag_Slide extends File_prosess{

    public $image_list;
    public $count_max = 0;
    public $img;
    public $max;

    //コンストラクタのオーバーライド
    public function __construct($file_path)
    {
        parent::__construct($file_path);
        $this->filerequest();
        $this->read();
        $this->close();

        echo '<script>var count_max = '.$this->count_max.';</script>';
        $this->max = $this->count_max - 1;
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
        echo '<div class = "slide">';
        for($i = $this->max,$j = 0;$i >= 0 && $j < 5;$i--,$j++):
            echo '<div class = "illust">';
                $this->list_reference($i,$img,$comment,$name,$date);
                $this->img->create($img,$comment,$j);
            echo '</div>';
        endfor;
        echo '</div>';
        create_submit('submit right','scroll_right',PLUS,">>");
        create_submit('submit left','scroll_left',MINUS,"<<");

        echo '<div class = "botton_frame">';
        for($i = $this->max,$j = 0;$i >= 0 && $j < 5;$i--,$j++):
            create_submit('button','button'.$j,$j,"");
        endfor;
        echo '</div>';
    }

    //イメージの表示(gallary)
    public function Show_Gallery($mode)
    {
        $box = new Delete_Form(GALLERY_SERVER,'delete');

        for($i = $this->max;$i >= 0;$i--):
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


/**************************************************************
クラス名：create_submit
機能：スライドショー用ボタンの作成
*************************************************************/
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
?>