<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN
Transitional" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
 <head>
   <?php include($_SERVER['DOCUMENT_ROOT'].'/php_test/Head.php');?>
   <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
 </head>
    <body>
    <?php
        $mode = "Debug";
        create_header($mode);
        main_contents("GALLERY",$mode);
        create_footer(); 
    ?>
    <script src="/php_test/scroll_ivent.js"></script>
    <script src="/php_test/gallery_ivent.js"></script>
 </body>
</html>