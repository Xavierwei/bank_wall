<?php

/**
 * @author Jackey <jziwenchen@gmail.com>
 * 这个 Controller 是为了缩略图准备
 * 比如 用户访问缩略图 /uploads/p20_400_400.png ， 
 * 在缩略图没有生成之前会把请求给 UploadsController 来生成缩略图
 */
class UploadsController extends Controller {
  
  public function init() {
    Yii::import("application.vendor.*");
  }
  
  // 生成
  public function missingAction($actionID) {
    $thumbnail_path = $actionID;
    // 先确定是视频的截图压缩图还是普通的图片
    //  如果是视频
    if ($actionID[0]== "v") {
      $output;
      exec("which ffmpeg", $output);
      if (!empty($output)) {
        $ffmpeg = array_shift($output);
        if ($ffmpeg) {
          $matches = array();
          preg_match("/_([0-9]+_[0-9]+)/", $actionID, $matches);
          if (!empty($matches)) {
            list($width, $height) = explode("_", $matches[1]);
            $filename = str_replace("_{$width}_{$height}", "", $actionID);
            $source_path = "uploads/".$filename;
            $this->makeVideoThumbnail($source_path, 'uploads/'.$actionID, $width, $height);
          }
        }
      }
    }
    else {
      $matches = array();
      preg_match("/_([0-9]+_[0-9]+)/", $actionID, $matches);
      if (!empty($matches)) {
        list($width, $height) = explode("_", $matches[1]);
        $filename = str_replace("_{$width}_{$height}", "", $actionID);
        $source_path = "uploads/".$filename;
        $this->makeImageThumbnail($source_path, 'uploads/'.$actionID, $width, $height);
      }
    }

    die();
  }
  
  private function makeImageThumbnail($path, $save_to, $w, $h) {
    $abspath = ROOT .'/'.$path;
    $abssaveto = ROOT.'/'.$save_to;
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
    $absscreenImagePath = ROOT .'/'. $screenImagePath;
    $abssaveTo = ROOT .'/'. $saveTo;
    $absvideoPath = ROOT. '/' . $basename.'.'.NodeAR::ALLOW_STORE_VIDE_TYPE;

    // 视频截图不能截2次
    // 做个检查
    if (!file_exists($absscreenImagePath)) {
      exec("ffmpeg -i $absvideoPath -ss 0.5 -t 1 -f image2 ".$absscreenImagePath. " 2>&1", $output, $status);
      // 成功了
      if ($status) {
        // nothing
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

