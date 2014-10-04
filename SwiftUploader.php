<?php
class SwiftUploader {

    protected $Options = array(
        "UploadPath" => false, //Yükleme path'ini belirtin
        "MaximumSize" => "512K", //Megabytes - MB | For bytes B | For KiloBytes KB | [0-9]+[AZ]*[B] regexi bu
		"Overwrite" => false, // Üzerine yazma işlemi yapılsınmı yapılmasın mı?
        "SupportedFormats" => array("png","jpg","sql"),  // Desteklenen formatlar array olarak belirtilmeli
		"Resolution" => array("MaxWidth"=>false,"MaxHeight"=>false), // Resolution resimler için geçerli bir parametredir.
		"FileName" => "File", //Dosyanın adını yazar
		"isMultiple" => false // Çoklu dosya yükleme işlemi aktif mi?
    );
	
	protected $ValidationErrors = array(); // Validation ile ilgili bir problem olduğunda
	
    var $FileTypes = array(
			// Image Files
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
			// Text Files
			'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',
			// Archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // Audio/Video files
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // Adobe Files
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // MS Office Documents
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // Open Office Documents
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );
	var $ImageExtensions = array(
			'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml'
	);
	/*
	* Sınıf initialize edildiğinde default ayarlar arasında eksik veya girilmemiş olan
	* ayar parametrelerini default değerleriyle birleştirir.
	*/
    public function __construct($Options = array()) {
		$DefaultOptions = $this->Options;
		foreach($DefaultOptions as $Key=>$Option){
			if(isset($Options[$Key])){
				$this->Options[$Key] = $Options[$Key];
			}
			
		}
		
    }
	/*
	* Desteklenen dosya formatlarını listeler.Bu verileri $Options array'indeki
	* SupportedFormats key değerinden almaktadır.Bu değer array olmak zorundadır.
	*/
	public function GetSupportedFormats(){
		$SupportedFormats = $this->Options["SupportedFormats"];
		$Data = array();
		if(is_array($SupportedFormats)){
			foreach($SupportedFormats as $Format){
				$Data[] = $Format;
			}
		}else if(is_string($SupportedFormats)){
			$Data[] = $SupportedFormats;
		}else{
			return false;
		}
		return $Data;
	}
	/* 
	* Mime değerinin tamamını verer.Buna dayanarak dosyanın açıldığında
	* hangi formatta olduğu bilgisine ulaşmayı sağlar.
	*/
	public function GetImageMimeExtension($FilePath){
		$Image = getimagesize($FilePath);
		return $Image['mime']; 
	}
	/*
	* Dosyanın mime'sine karşılık gelen dosya kısa uzantısını veren
	* fonksiyondur.
	*/
	public function GetShortMime($Mime = NULL){
		$Data = false;
		foreach($this->FileTypes as $Extension=>$Format){
			if($Mime == $Format){  
				$Data = $Extension;
			}
		}
		return $Data;
	}
	/*
	*
	* Girilen uzantının upload edilmesinin desteklenip, desteklenmeyeceği
	* kararına yardımcı olan boolean methodudur.
	*
	*/
	public function isSupportedFormat($ShortMime){
		$SupportedFormats = $this->GetSupportedFormats();
		$isSupported = false;
		foreach($SupportedFormats as $Extension=>$Format){
			if($ShortMime==$Format){
				$isSupported = true;
				break;
			}
		}
		return $isSupported;
	}
	
    /*
     * Aynı dosya isminde bir dosya daha varmı diye kontrol eder.Aynı dosya isminden
	 * bir tane daha varsa true yoksa false  döndür.
     */
    public function CheckForDuplicate($FileName, $Extension) {
        $FullPath = $this->Options["UploadPath"] . $FileName . "." . strtolower($Extension);
        $hasDuplicate = false;
        if (file_exists($FullPath)) {
            $hasDuplicate = true;
        }
        return $hasDuplicate;
    }
    /*
     * Dosyanın aynı isimlisinden varsa _ ekleyerek kontrol eder.
     */

    public function RenameFile($FileName, $Extension) {
		if($this->Options["Overwrite"]!=true){
			if ($this->CheckForDuplicate($FileName, $Extension)) {
				$HasCopies = strpos($FileName,'_');
				if($HasCopies !== false){
					$ChangeCopies = preg_split('/_/s',$FileName);
					$CopyNumber = intval($ChangeCopies[1]); // _1 _2 _3
					$CopyNumber++;
					$FileName = $ChangeCopies[0].'_'.$CopyNumber;
				}else{
					$FileName = $FileName.'_1';
				}
				return $this->RenameFile($FileName, $Extension);
			}
		}
		return $FileName;			
    }
	public function GenerateImage($ResizedImage,$File){
			if($this->GetShortMime($File)=="jpg" || $this->GetShortMime($File)=="jpeg"){
				imagejpeg($ResizeImage,$File);
			}else if($this->GetShortMime($File)=="png"){
				imagepng($ResizeImage,$File);
			}else if($this->GetShortMime($File)=="gif"){
				imagegif($ResizeImage,$File);
			}
	}
	public function ImageCreation($File){
		if($this->GetShortMime($File)=="jpg" || $this->GetShortMime($File)=="jpeg"){
			return imagecreatefromjpeg($ResizeImage,$File);
		}else if($this->GetShortMime($File)=="png"){
			return imagecreatefrompng($ResizeImage,$File);
		}else if($this->GetShortMime($File)=="gif"){
			return imagecreatefromgif($ResizeImage,$File);
		}
	}
	/*
	*	Gönderilen resim bilgisinin maxWidth ve maxHeight değerlerinden büyük ise
	*	%60 oranında ve ölçekli bir şekilde küçültür.
	*/
    public function ResizeImage($File) {
        $Percent = 0.6;
        list($CurrentWidth, $CurrentHeight) = getimagesize($File);
        if ($this->Options["Resolution"]["MaxWidth"] == false || $this->Options["Resolution"]["MaxHeight"] != false) {
            
        } else {
            if ($CurrentWidth > $this->Options["Resolution"]["MaxWidth"] || $CurrentHeight > $this->Options["Resolution"]["MaxHeight"]) {
                $FixedWidth = $CurrentWidth * $Percent;
                $FixedHeight = $CurrentHeight * $Percent;
                $ResizedImage = imagecreatetruecolor($FixedWidth, $FixedHeight);
                $OriginalImage = $this->ImageCreation($File);
                imagecopyresized($ResizedImage, $OriginalImage, 0, 0, 0, 0, $FixedWidth, $FixedHeight, $CurrentWidth, $CurrentHeight);
				$this->GenerateImage($ResizedImage, $File);
                imagedestroy($ResizedImage);
            }
        }
    }
	function RequestedSupportedSize(){
		$MaxSize = $this->Options["MaximumSize"];
		$SizeArray = preg_split('#(?<=\d)(?=[a-z])#i', $MaxSize);
		$Value = NULL;
		$Format = NULL;
		if(isset($SizeArray[0])){
			if(is_numeric($SizeArray[0])){
				$Value = $SizeArray[0];
			}
		}
		if(isset($SizeArray[1])){
			if(is_string($SizeArray[1])){
				$Format = $SizeArray[1];
			}
		}
		if($Value == NULL || $Format == NULL){
			exit("Yanlış bir boyut tipi belirtdiniz...");
		}
		$Data = array(
			"Value" => $Value,
			"Format" => $Format
		);
		return $Data;
	}
	public function SizeCalculation($Data=array()){
		$Format = $Data["Format"];
		$Value = $Data["Value"];
		switch($Format){
			case "G":
				$Data = $Value*1024*1024*1024;
			break;
			case "M":
			// byte'a çevir
				$Data = $Value*1024*1024;
			break;
			case "K":
			// byte'a çevir
				$Data = $Value*1024;
			break;
			default:
				$Data = $Value;
			break;
		}
		return $Data;
	}
	public function isSupportedFileSize($FileSize){
		$isSupported = true;
		$RequestedSize = $this->RequestedSupportedSize();
		$ByteSize = $this->SizeCalculation($RequestedSize);
		if($FileSize > $ByteSize){
			$isSupported = false;
		}
		return $isSupported;
	}
	/* 
	*	Girilen Türkçe karakterleri latin karakterlere çevirir.
	*/
    public function FullNameSef($Title) {
        $FoundChars = array('Ç', 'Ş', 'Ğ', 'Ü', 'İ', 'Ö', 'ç', 'ş', 'ğ', 'ü', 'ö', 'ı', '+', '#');
        $ReplaceChars = array('c', 's', 'g', 'u', 'i', 'o', 'c', 's', 'g', 'u', 'o', 'i', 'plus', 'sharp');
        $NewTitle = strtolower(str_replace($FoundChars, $ReplaceChars, $Title));
        $NewTitle = preg_replace("@[^A-Za-z0-9\-_\.\+]@i", ' ', $NewTitle);
        $NewTitle = trim(preg_replace('/\s+/', ' ', $NewTitle));
        $NewTitle = str_replace(' ', '-', $NewTitle);
        return $NewTitle;
    }
	public function isFileEmpty($ErrorCode=NULL){
		$isEmpty = false;
		if($ErrorCode==4){
			$isEmpty = true;
		}
		return $isEmpty;
	}
	public function isFileImage($Extension=NULL){
		$isImage = false;
		foreach($this->ImageExtensions as $Key=>$Format){
			if($Key == $Extension){
				$isImage = true;
				break;
			}
		}
		return $isImage;
	}
	/*
	*	DOsya yükler
	*/
	public function UploadSingleFile($File = array()){
	$this->RequestedSupportedSize();
			$Path = $this->Options["UploadPath"];
			$NewTitle = $this->Options["FileName"];
			$Data = false;
			$Extension = $this->GetShortMime($File["type"]);
			if($this->isFileEmpty($File["error"])){
				$this->SetError("Lütfen bir dosya seçiniz");
				return $Data;
			}
			if($Extension==false){
				$this->SetError("Bu format desteklenmiyor");
				return $Data;
			}
			if($this->isSupportedFormat($Extension)==false){
				$this->SetError("Bu dosya formatı desteklenmiyor.");
			}
			if($this->isSupportedFileSize($File["size"])==false){
				$this->SetError("Dosya boyutu çok büyük");
			}
			if(count($this->ValidationErrors)==0){
				$NewName = $this->RenameFile($NewTitle, $Extension);
				@move_uploaded_file($File["tmp_name"], $Path . $NewName . "." . $Extension);
				$Data = array(
					"Title" => $NewName,
					"Extension" => $Extension,
					"Size" => $File["size"],
					"Path" => $this->Options["UploadPath"],
					"FullFile"=> $this->Options["UploadPath"].$NewName.'.'.$Extension
				);
				$FullPath = $this->Options["UploadPath"] . $NewName . "." . $Extension;
			}
			return $Data;
	}
	public function UploadMultipleFile($Files = array(),$NewTitle){
        $Path = $this->Options["UploadPath"];
		$Data = array();
		if($this->isFileEmpty($File["error"])){
				$this->SetError("Lütfen bir dosya seçiniz");
				return $Data;
			}
			if($Extension==false){
				$this->SetError("Bu format desteklenmiyor");
				return $Data;
			}
			if($this->isSupportedFormat($Extension)==false){
				$this->SetError("Bu dosya formatı desteklenmiyor.");
			}
			if($this->isSupportedFileSize($File["size"])==false){
				$this->SetError("Dosya boyutu çok büyük");
			}
			if(count($this->ValidationErrors)==0){
				foreach ($Files as $File) {
					$NewName = $this->RenameFile($NewTitle, $Extension);
					@move_uploaded_file($File["tmp_name"], $Path . $NewName . "." . $Extension);
					$Data[] = array(
						"Title" => $NewName,
						"Extension" => $Extension,
						"Size" => $File["size"],
						"Path" => $this->Options["UploadPath"],
						"FullFile"=> $this->Options["UploadPath"].$NewName.'.'.$Extension
					);
					$FullPath = $this->Options["UploadPath"] . $NewName . "." . $Extension;
				}
			}
	}
    public function UploadFile($Files = array()) {
        $Data = array();
        $NewTitle = $this->FullNameSef($this->Options["FileName"]);  
        if ($this->Options["isMultiple"]!=false) {
            $Data = $this->UploadMultipleFile($Files,$NewTitle);
        }else{
			$Data = $this->UploadSingleFile($Files,$NewTitle);
        }
        return $Data;
    }
	public function SetError($Error){
		$this->ValidationErrors[] = $Error;
	}
	public function ValidationErrors(){
		$Errors = $this->ValidationErrors;
		return $Errors;
	}
}
?>