<?php

class UploadsController extends Controller {

  private $_photo_mime = array(
		"image/gif", "image/png", "image/jpeg", "image/jpg", "image/pjpeg", "image/x-png"
	);
  private $_video_mime = array(
		"video/mov","video/quicktime", "video/x-msvideo", "video/x-ms-wmv", "video/wmv", "video/mp4", "video/avi", "video/3gp", "video/3gpp", "video/mpeg", "video/mpg", "application/octet-stream", "video/x-ms-asf"
	);

  public function init() {
    Yii::import("application.vendor.*");
  }


  public function missingAction($actionID) {
    $files = explode("/", substr($_SERVER["REQUEST_URI"], 1));
    $filename = $files[count($files) - 1];
    if ($filename[0]== "v") {
      $output;
      exec("which ffmpeg", $output);
      if (!empty($output)) {
        $ffmpeg = array_shift($output);
        if ($ffmpeg) {
          $matches = array();
          preg_match("/_([0-9]+_[0-9]+)/", $filename, $matches);
          if (!empty($matches)) {
            list($width, $height) = explode("_", $matches[1]);
            $source_filename = str_replace("_{$width}_{$height}", "", $filename);
            $basepath = implode("/",array_splice($files, 0, count($files) - 1));
            $source_path = DOCUMENT_ROOT.'/'.$basepath.'/'.$source_filename;//增加了DOCUMENT_ROOT，否则在子目录下路径不对了
            NodeAR::model()->makeVideoThumbnail($source_path, DOCUMENT_ROOT.'/'.$basepath .'/' .$filename, $width, $height, true);
          }
        }
      }
    }
    else {
      $matches = array();
      $request_file_path = $_SERVER["REQUEST_URI"];
      preg_match("/_([0-9]+_[0-9]+)/", $request_file_path, $matches);
      if (!empty($matches)) {
        list($width, $height) = explode("_", $matches[1]);
        $filename = str_replace("_{$width}_{$height}", "", $request_file_path);
        $source_path = DOCUMENT_ROOT.'/'.substr($filename, 1);
        if (!is_file($source_path)) {
          return;
        }
		$request_file_path = DOCUMENT_ROOT.$request_file_path; //增加了DOCUMENT_ROOT，否则在子目录下路径不对了
		NodeAR::model()->makeImageThumbnail($source_path, $request_file_path, $width, $height, true);
      }
    }

    die();
  }




	public function actionUpload() {
		$uid = Yii::app()->user->getId();
		$user = UserAR::model()->findByPk($uid);
		if(!$user) {
			return $this->responseError(602);
		}
    
    $request = Yii::app()->getRequest();
    
    $tmp_file = $request->getPost("tmp_file");
    // 如果有temp file 并且文件存在就说明是重新上传文件
    if ($tmp_file && is_file(ROOT.$tmp_file)) {
      $filePath = ROOT. $tmp_file;
      $mime = NodeAR::detechFileMime($filePath);
      $size = filesize($filePath);
      $name = pathinfo($filePath, PATHINFO_BASENAME);

      $new_file_entity = array(
          "type" => $mime,
          "size" => $size,
          "tmp_name" => $filePath,
          "error" => UPLOAD_ERR_OK,
          "name" => $name
      );
      // 重新生成一个假的$_FILES 数据
      $_FILES[pathinfo($filePath, PATHINFO_FILENAME)] = $new_file_entity;
      
      $file_name = pathinfo($filePath, PATHINFO_FILENAME);
    }
    else {
      $file_name = "file";
    }
    
		$fileUpload = CUploadedFile::getInstanceByName($file_name);
		$type = $request->getPost("type");
		$device = $request->getPost("device");
		if(!isset($type)) {
			$mime = $fileUpload->getType();
			if( in_array($mime, $this->_photo_mime ) ){
				$type = "photo";

			} else if ( in_array($mime, $this->_video_mime ) ){
				$type = "video";
			} else {
				return $this->responseError(502); //photo media type is not allowed
			}
		}
		$nodeAr = new NodeAR();
		$validateUpload = $nodeAr->validateUpload($fileUpload, $type, $device);
		if($validateUpload !== true) {
			return $this->responseError($validateUpload);
		}

		// save file to dir
		$file = $nodeAr->saveUploadedFile($fileUpload, $device);
    // ffmpeg 正忙
    if ($file && is_array($file) && $file[0] === FALSE) {
      $to = $file[1];
      if (is_file(ROOT. $to)) {
        $this->responseJSON(array("error" => "ffmpeg busy", "tmp_file" => str_replace(ROOT, "", $to)), "ffmpeg busy");
      }
      else {
        return $this->responseError(509); 
      }
    }
		else if($file) {
			// make preview thumbnail
			if($type == 'video') {
				$paths = explode(".",$file);
				$basename = array_shift($paths);
				$nodeAr->makeVideoThumbnail(ROOT.$basename.'.jpg', ROOT.$basename.'.jpg', 175, 175, false);
			}

			// return result
			$retdata = array( "type"=> $type , "file" => $file );
			$this->responseJSON($retdata, "success");
		}
		else {
			return $this->responseError(509); //photo media type is not allowed
		}
	}
}

