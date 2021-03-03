<?php
/************************************************
include
*************************************************/
include(DOCUMENT_PATH."/base_class.php");
include(DOCUMENT_PATH."/image_slide.php");

/************************************************
クラス名：New_News
機能：ニュース更新クラス
継承元：File_prosess
*************************************************/
class New_News extends File_prosess
{
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
        foreach($this->News_list as $str):
            tag_p($str);
        endforeach;
    }
}

/************************************************
メインコンテンツの作成
*************************************************/
function main_contents($mode)
{
    $News = new New_News(NEWFILE_PATH);
    $News->read();
    $News->close();
    $News->print_news();
}
?>