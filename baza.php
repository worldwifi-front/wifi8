<?php
	
	 ini_set('display_errors', '0');

  $live = 60*90*1;
  $live = $live - mktime() + filemtime("counter.txt");
  if ($live<0) {

        $file = "counter.txt";
         if (file_exists($file)) {
         $fp = fopen($file, "r");
         $counter = fread($fp, filesize($file));
         fclose($fp);
       } else {
          $counter = "2647000";
        }

      $rand = rand(500, 2000);
      $content =($counter+$rand);
      $fp = fopen($file, "w");
      fwrite($fp, $content);
      fclose($fp);

      $content = number_format($content / 1000000, 3) . ' M';
      echo json_encode(array('result' => $content));

       }else{

         $content = file_get_contents('counter.txt', FILE_USE_INCLUDE_PATH);
         $content = number_format($content / 1000000, 3) . ' M';
         echo json_encode(array('result' => $content));
       }

?>