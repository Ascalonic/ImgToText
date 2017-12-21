<?php
session_start();
if ( isset($_FILES["file"]["type"]) )
{
  $max_size = 8056 * 1024; // 500 KB
  $destination_directory = "upload/";
  $validextensions = array("jpeg", "jpg", "png","JPEG", "JPG", "PNG");
  $temporary = explode(".", $_FILES["file"]["name"]);
  $file_extension = end($temporary);
  // We need to check for image format and size again, because client-side code can be altered
  if ( (($_FILES["file"]["type"] == "image/png") ||
        ($_FILES["file"]["type"] == "image/jpg") ||
        ($_FILES["file"]["type"] == "image/jpeg")
       ) && in_array($file_extension, $validextensions))
  {
    if ( $_FILES["file"]["size"] < ($max_size) )
    {
      if ( $_FILES["file"]["error"] > 0 )
      {
        echo "<div class=\"alert alert-danger\" role=\"alert\">Error: <strong>" . $_FILES["file"]["error"] . "</strong></div>";
      }
      else
      {
        if ( file_exists($destination_directory . $_FILES["file"]["name"]) )
        {
          echo "<div class=\"alert alert-danger\" role=\"alert\">Error: File <strong>" . $_FILES["file"]["name"] . "</strong> already exists.</div>";
        }
        else
        {
          $sourcePath = $_FILES["file"]["tmp_name"];
		  
		  $new_file_name = hash('ripemd160', $_FILES["file"]["name"].time()).'.'.$file_extension;
		  
          $targetPath = $destination_directory.$new_file_name;
          move_uploaded_file($sourcePath, $targetPath);
		  list($width, $height, $type, $attr) = getimagesize($targetPath);
          $ret = array();
		  $ret['file_path']=$targetPath;
		  $ret['file_width']=$width;
		  $ret['file_height']=$height;
		  
		  $_SESSION['imgpath']=$targetPath;
		  $_SESSION['imgformat']=$_FILES["file"]["type"];
		  
		  echo json_encode($ret);
        }
      }
    }
    else
    {
      echo "<div class=\"alert alert-danger\" role=\"alert\">The size of image you are attempting to upload is " . round($_FILES["file"]["size"]/1024, 2) . " KB, maximum size allowed is " . round($max_size/1024, 2) . " KB</div>";
    }
  }
  else
  {
    echo "<div class=\"alert alert-danger\" role=\"alert\">Unvalid image format. Allowed formats: JPG, JPEG, PNG.</div>";
  }
}
?>