<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN
Transitional" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
    <head>
      <?php
         $path = $_SERVER['DOCUMENT_ROOT'];
         include($path.'/php_test/Head.php');
      ?>
      <?php include('class.php');?>
      <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    </head>
    <body>
      <header>
        <?php
          $mode = "Client";
          create_header($mode);
        ?>
      </header>
      <div class = "main_flame">
          <div class = "menu">
            <?php create_menu($mode);?>
          </div>
          <main>
            <h1>PROFILE</h1>
            <?php create_profile($mode);?>
          </main>
      </div>
      <footer>
          <?php create_footer();?>
      </footer>
      <script src="/php_test/scroll_ivent.js"></script>
      <script src="profile_ivent.js"></script>
    </body>
</html>