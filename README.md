PHP Swift Uploader v1.0&Beta;
=========

I prepaired Swift Uploader for the CakePHP upload operations.There aren't any simple upload class for using CakePHP.Also, It's a simple PHP upload class;
Easy to use, easy to implement and easy to learn how to upload file.

Don't waste your time with controlling, validating and modifying files that is uploaded from your web site.You can try it for free.


Version
----

1.0

Technologies
-----------

Dillinger uses a number of open source projects to work properly:

* [PHP GD] - Image manupilation library for php


Installation
--------------

```sh
cd /cakephp_path/app/Lib/
git clone https://github.com/bl4cksta/SwiftUploader.git .
```

When you installed library to hosted path.You should import on it.

For CakePHP you can use the following segment of code before initialize controller;
```sh
App::Uses('SwiftUploader','Lib');

class ExampleController extends Controller{
  function index(){
    if($_FILES){
    	$config = array(
            "UploadPath" => 'Upload_Path/',
            "MaximumSize" => "10M", // 10M, 10K, 10B, 10G
    		"Overwrite" => false,
            "SupportedFormats" => array("png"), // jpg,png,gif,svg ...
    		"Resolution" => array("MaxWidth"=>false,"MaxHeight"=>false),
    		"FileName" => "Picture",	
    	);
    	$test = new SwiftUploader($config);
    		print_r($test->UploadFile($_FILES["Post_Name"]));
    		print_r($test->ValidationErrors());
    }
  }

```

For Flat PHP you can use the following segment of code;
```sh
<?php 
  require_once('FileUpload.php');
    if($_FILES){
    	$config = array(
            "UploadPath" => 'Upload_Path/',
            "MaximumSize" => "10M", // 10M, 10K, 10B, 10G
    		    "Overwrite" => false,
            "SupportedFormats" => array("png"), // jpg,png,gif,svg ...
    		"Resolution" => array("MaxWidth"=>false,"MaxHeight"=>false),
    		"FileName" => "Picture",	
    	);
    	$test = new SwiftUploader($config);
    		print_r($test->UploadFile($_FILES["Post_Name"]));
    		print_r($test->ValidationErrors());
    }
?>
```

Output:
```sh
  Array ( [Title] => File [Extension] => txt [Size] => 0 [Path] => Upload_Path/ [FullFile] => Upload_Path/File.txt )
```

License
----

GNU/GPL 3.0



** Thank you for your interests **
[Kaan Kılıç]:http://kaankilic.com/
