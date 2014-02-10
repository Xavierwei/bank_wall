<?php

/**
 * 这个 Controller 是为了缩略图准备
 * 比如 用户访问缩略图 /uploads/p20_400_400.png ， 
 * 在缩略图没有生成之前会把请求给 UploadsController 来生成缩略图
 */
class UploadsController extends Controller {
  
  private $_max_photo_size = 5120000;
  private $_max_video_size = 7168000;
  private $_photo_mime = array(
          "image/gif", "image/png", "image/jpeg", "image/jpg"
      );
  private $_video_mime = array(
      "video/mov", "video/wmv", "video/mp4", "video/avi", "video/3gp"
      );

  public function init() {
    Yii::import("application.vendor.*");
  }
  
  // 生成
  public function missingAction($actionID) {
    $thumbnail_path = $actionID;
    
    $files = explode("/", substr($_SERVER["REQUEST_URI"], 1));
    $filename = $files[count($files) - 1];
    // 先确定是视频的截图压缩图还是普通的图片
    //  如果是视频
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
//						echo $source_path;
//            if (!is_file($source_path)) {
//              return ;
//            }
            $this->makeVideoThumbnail($source_path, DOCUMENT_ROOT.'/'.$basepath .'/' .$filename, $width, $height);
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
        $this->makeImageThumbnail($source_path, $request_file_path, $width, $height);
      }
    }

    die();
  }


	public function actionUpload() {
		$fileUpload = CUploadedFile::getInstanceByName("file");
		$mime = $fileUpload->getType();
		if( in_array($mime, $this->_photo_mime ) ){
			$type = "photo";

		} else if ( in_array($mime, $this->_video_mime ) ){
			$type = "video";
		} else {
			return $this->responseError(502); //photo media type is not allowed
		}

		$nodeAr = new NodeAR();
		$validateUpload = $nodeAr->validateUpload($fileUpload, $type);
		if($validateUpload !== true) {
			return $this->responseError($validateUpload);
		}

		// save file to dir
		$file = $nodeAr->saveUploadedFile($fileUpload);

		// return result
		$retdata = array( "type"=> $type , "file" => $file );
		$this->responseJSON($retdata, "success");
	}

  
  private function makeImageThumbnail($path, $save_to, $w, $h) {
    $abspath = $path;
    $abssaveto = $save_to;
    $thumb = new EasyImage($abspath);
    
    // 这里需要做下调整
    
    $size = getimagesize($abspath);
    $s_w = $size[0];
    $s_h = $size[1];
    
    $r1 = $w / $s_w;
    $r2 = $h / $s_h;
    $widthSamller = TRUE;
    if ($r1 > $r2) {
      $r = $r1;
    }
    else {
      $widthSamller = FALSE;
      $r = $r2;
    }
    $t_w = $r * $s_w;
    $t_h = $r * $s_h;
    
    // 先等比例 resize
    $thumb->resize($t_w, $t_h);
    // 再裁剪
    // 裁剪 多余的宽
    if (!$widthSamller) {
       $start_x = ($t_w - $w)/2;
       $start_y = 0;
       $thumb->crop($w, $h, $start_x, $start_y);
    }
    // 裁剪多余的 高
    else {       
       $start_x = 0;
       $start_y = ($t_h - $h);
       $thumb->crop($w, $h, $start_x, $start_y);
    }
    
    $thumb->save($abssaveto);
    
    // 输出
    $fp = fopen($abssaveto, "rb");
    if ($size && $fp) {
        header("Content-type: {$size['mime']}");
        fpassthru($fp);
        exit;
    } else {
        // error
    }
  }
  
  /**
   * 这个函数有2步；
   * 第一步 生成视频截图
   * 第二步 生成缩略图
   * @param type $screenImagePath 视频截图的相对路径
   * @param type $saveTo 缩略图保存路径
   * @param type $w 缩略图 width
   * @param type $h 缩略图 height
   * @return 
   */
  private function makeVideoThumbnail($screenImagePath, $saveTo, $w, $h) {
    // 我们要根据视频截图的路径推算出视频的路径
    $paths = explode(".",$screenImagePath);
    $basename = array_shift($paths);
    $output = NULL;
    $status = NULL;
    $absscreenImagePath = $screenImagePath;
    $abssaveTo = $saveTo;
    $absvideoPath = $basename.'.'.NodeAR::ALLOW_STORE_VIDE_TYPE;

    // 视频截图不能截2次
    // 做个检查
    if (!file_exists($absscreenImagePath)) {
      exec("ffmpeg -i $absvideoPath -ss 0.5 -t 1 -f image2 ".$absscreenImagePath. " 2>&1", $output, $status);
      // 成功了
      if ($status) {
        // nothing
				exec("ffmpeg -i $absvideoPath -ss 0.5 -t 1 -f image2 ".$absscreenImagePath. " 2>&1", $output, $status);
      }
      else {
        //TODO:: 不成功 我们可能需要返回一个默认的视频；因为客户端需要的是一个图片链接
        // 这里暂时直接 die() 掉， 因为后续工作 都是在此图片生成成功基础上做操作
        die();
      }
    }

    if($w && $h) {
        $this->makeImageThumbnail($screenImagePath, $saveTo, $w, $h);
    }
    
//    // 生成缩略图
//    $thumb = new EasyImage($absscreenImagePath);
//
//    $size = getimagesize($absscreenImagePath);
//
//    //TODO:: 这里需要更复杂点的 缩略图方式
//
//    $thumb->resize($w, $h);
//    $thumb->save($abssaveTo);
//
//    // 输出
//    $fp = fopen($abssaveTo, "rb");
//    if ($size && $fp) {
//        header("Content-type: {$size['mime']}");
//        fpassthru($fp);
//        exit;
//    } else {
//        // error
//    }
    
  }
}

