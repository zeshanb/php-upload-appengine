<?php

   require_once 'google/appengine/api/cloud_storage/CloudStorageTools.php';
   use google\appengine\api\cloud_storage\CloudStorageTools;

   $options = [ 'gs_bucket_name' => 'enter-storage-bucket-name' ];
   $upload_url = CloudStorageTools::createUploadUrl('/', $options);

?>
<!DOCTYPE html>
<html>
<head>
<title></title>
<link rel="stylesheet" type="text/css" href="/inc/topcoat-0.8.0/css/topcoat-mobile-dark.css">
<link rel="stylesheet" type="text/css" href="/inc/css/main.css">
</head>
<body>
<div class="contentArea">
<?php

if(isset($_POST['do-upload']) AND $_POST['do-upload'] === "yes"){

   $yesupload = $_POST['do-upload'];
   preg_match("/yes/", "".$yesupload."");

   $filename = $_FILES['testupload']['name'];
   
   $gs_name = $_FILES['testupload']['tmp_name'];
   move_uploaded_file($gs_name, 'gs://enter-storage-bucket-name/'.$filename.'');

?>

<?php
   echo "<p>Hey, file is uploaded</p>";
   echo "<p>Name of the file you uploaded: ".$filename."</p>";
   }
   
?>
<form class="SomeSpaceDude" action="<?php echo $upload_url?>" enctype="multipart/form-data" method="post">
   <p>Files to upload: </p> <br>
   <input type="hidden" name="do-upload" value="yes">
   <input class="topcoat-button" type="file" name="testupload" >
   <input class="topcoat-button" type="submit" value="Upload">
</form>
</div>
</body>
</html>