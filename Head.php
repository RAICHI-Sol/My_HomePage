<?php
/**********************************************
define
***********************************************/
//php_path
define('GALLERY_SERVER',"/php_test/contents/gallery_server.php");
define('CONTENTS_ID',"/php_test/contents/contents.php#");

//csv_file_path
define('DOCUMENT_PATH',$_SERVER['DOCUMENT_ROOT'].'/php_test');
define('NEWFILE_PATH',DOCUMENT_PATH.'/file/News.csv');
define('IMG_PATH',DOCUMENT_PATH.'/file/Image.csv');
define('LOGO_PATH',DOCUMENT_PATH.'/file/logo.csv');
define('PROF_PATH',DOCUMENT_PATH.'/file/prof.csv');

//file_path
define('STORE_ILLUST',DOCUMENT_PATH."/image/illust/");

//ENCODE
define('ENCODE_NOMAL',"application/x-www-form-urlencoded");
define('ENCODE_FILE',"multipart/form-data");

//Code
define('PLUS',1);
define('MINUS',-1);

echo <<< END_OF_TEXT
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="/php_test/style.css">
    <link href="https://fonts.googleapis.com/earlyaccess/nicomoji.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=M+PLUS+1p" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Bangers rel="stylesheet">
    <link rel="icon" type="image/jpg" href="./image/raichi.jpg">
    <title>RAICHI_Official</title>
END_OF_TEXT;
require_once(DOCUMENT_PATH."/function.php");
?>