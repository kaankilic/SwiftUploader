<?php 
	require_once('SwiftUploader.php');
	if($_FILES){
	$config = array(
        "UploadPath" => 'My_Upload_Path/',
        "MaximumSize" => "1M",
		"Overwrite" => false,
        "SupportedFormats" => array("txt"), 
		"Resolution" => array("MaxWidth"=>false,"MaxHeight"=>false),
		"FileName" => "File",	
		"isMultiple" => true // Çoklu dosya yükleme işlemi aktif mi?
	);
	$test = new SwiftUploader($config);
		print_r($test->UploadFile($_FILES["File"])); // Trying to upload file if there no validation errors
		print_r($test->ValidationErrors()); // printing validation errors
	}
?> 
<form action="" method="post" enctype="multipart/form-data">
	<input type="file" multiple name="File" />
	<input type="submit"  name="Upload" />
</form>