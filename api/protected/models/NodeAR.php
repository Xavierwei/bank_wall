<?php

class NodeAR extends CActiveRecord{
    
	const PUBLICHSED = 1;
	const UNPUBLISHED = 0;
	const BLOCKED = 3;

	const PHOTO = 'photo';
	const VIDEO = 'video';


	public $likecount = 0;

	public $commentcount = 0;

	public $flagcount = 0;

	public $user_liked = FALSE;

	public $user_flagged = FALSE;

	public $topday = FALSE;

	public $topmonth = FALSE;

	public $like = array();

	public $flag = array();


	const ALLOW_UPLOADED_PHOTO_TYPES = "jpg,png,gif,jpeg";

	const ALLOW_UPLOADED_VIDEO_TYPES = "mp4,avi,mov,mpg,mpeg,3gp,wmv";

	const ALLOW_STORE_VIDE_TYPE = "mp4";

    const ALLOW_MAX_FFMPEG_COUNT = 5;

	public $nodecounts;
  
	public static function model($class = __CLASS__) {
		return parent::model($class);
	}

	public function tableName() {
		return "node";
	}

	public function getPrimaryKey() {
		return "nid";
	}

	public function rules() {
		return array(
		    array("uid, file, country_id, type", "required"),
		    array("uid", "uidExist"),
		    array("country_id", "countryExist"),
		    array("created , type, datetime, status, description, nid, hashtag, user_liked,user_flagged, like, flag, topday, topmonth, from", "safe"),
		);
	}
  
	public function uidExist($attribute, $params = array()) {
		$uid = $this->{$attribute};

		if ($uid) {
		  $user = UserAR::model()->findByPk($uid);
		  if (!$user) {
		    $this->addError($attribute, "user is not exist our system");
		  }
		}
	}
  
	public function countryExist($attribute, $params = array()) {
		$country_id = $this->{$attribute};

		if ($country_id) {
		  $country = CountryAR::model()->findByPk($country_id);
		  if (!$country) {
		    $this->addError($attribute, "country is not exist our system");
		  }
		}
	}
  
	public function relations() {
		return array(
		    "country" => array(self::BELONGS_TO, "CountryAR", "country_id"),
		    "user" => array(self::BELONGS_TO, "UserAR", "uid"),
		);
	}
  
	public function getHashTag() {
		$description = $this->description;
		$matches = array();
		preg_match_all("/#([^\s]*)/", $description, $matches);
		$hashtags = end($matches);

		return $hashtags;
	}
  
	public function beforeSave() {
		parent::beforeSave();

		$hashtags = $this->getHashTag();
		if (!$this->{$this->getPrimaryKey()}) {
		    $this->setAttribute("datetime", time());
		    $this->setAttribute("created", time());
		}
		$this->setAttribute("hashtag", serialize($hashtags));
		foreach($hashtags as $tag) {
			TagAR::model()->saveTag($tag);
		}

		return TRUE;
	}
  
	public function afterFind() {
		parent::afterFind();
		$this->hashtag = unserialize($this->hashtag);

		if ($uid = Yii::app()->user->getId() ) {
		  $user = UserAR::model()->findByPk($uid); // 此处是否有必要？多一次查询了
		  if ($user) {
		    $like = LikeAR::model()->findByAttributes(array("nid" => $this->nid, "uid" => $user->uid));
		    if (($like)) {
		      $this->user_liked = TRUE;
		    }
		    else {
		      $this->user_liked = FALSE;
		    }

			$flag = FlagAR::model()->findByAttributes(array("nid" => $this->nid, "uid" => $user->uid));
			if (($flag)) {
				$this->user_flagged = TRUE;
			}
			else {
				$this->user_flagged = FALSE;
			}
		  }
		}

		$nid = $this->nid;
		$commentAr = new CommentAR();
		$this->commentcount = $commentAr->totalCommentsByNode($this->nid);
		$likeAr = new LikeAR();
		$this->likecount = $likeAr->getNodeCount($this->nid);
		$flagAr = new FlagAR();
		$this->flagcount = $flagAr->flagCountInNode($this->nid);

		return TRUE;
	}
  
    public function afterSave() {
		$type = $this->type;
		if ($type == "photo") {
				$name = "p". $this->nid;
		}
		else {
				$name = "v". $this->nid;
		}

		$ext = pathinfo($this->file, PATHINFO_EXTENSION);
		$newname = $name.'.'.$ext;

		$paths = explode("/", $this->file);
		$paths[count($paths) - 1] = $newname;
		$newpath = implode("/", $paths);

		if (file_exists(ROOT.$this->file)) {
			rename(ROOT.$this->file, ROOT. $newpath);
			//rename the file name
			$this->updateByPk($this->nid, array("file" => $newpath));
			$this->file = $newpath;

			if($type == 'photo') {
				$this->makeImageThumbnail(ROOT.$newpath, ROOT.str_replace('.jpg', '_250_250.jpg', $newpath), 250, 250, false);
				$this->makeImageThumbnail(ROOT.$newpath, ROOT.str_replace('.jpg', '_640_640.jpg', $newpath), 650, 650, false);
			}
			else {
				$newImgPath = str_replace('.mp4', '.jpg', $newpath);
				$this->makeVideoThumbnail(ROOT.$newImgPath, ROOT.str_replace('.jpg', '_250_250.jpg', $newImgPath), 250, 250, false);
				$this->makeVideoThumbnail(ROOT.$newImgPath, ROOT.str_replace('.jpg', '_640_640.jpg', $newImgPath), 650, 650, false);
			}
		}

		// Generate WMV for no flash IE8
		if ($type == "video") {
			$topath = ROOT.$newpath;
			$wmvpath = str_replace('.mp4','.wmv',$topath);
			exec("ffmpeg -i {$topath} -y {$wmvpath}", $output, $status);
		}
		// Load user/country
		$userAr = new UserAR();
		$userAr->setAttributes($userAr->getOutputRecordInArray(UserAR::model()->findByPk($this->uid)));
		$this->user = $userAr;

		$this->country = CountryAR::model()->findByPk($this->country_id);

		return TRUE;
    }

	public function deleteRelatedData($nid) {
		// Delete related comments
		Yii::app()->db->createCommand()
			->delete('comment', 'nid=:nid', array(':nid'=>(int)$nid));
		// Delete related likes
		Yii::app()->db->createCommand()
			->delete('like', 'nid=:nid', array(':nid'=>(int)$nid));
		// Delete related flags
		Yii::app()->db->createCommand()
			->delete('flag', 'nid=:nid', array(':nid'=>(int)$nid));
		// Delete related topday
		Yii::app()->db->createCommand()
			->delete('topday', 'nid=:nid', array(':nid'=>(int)$nid));
		// Delete related topmonth
		Yii::app()->db->createCommand()
			->delete('topmonth', 'nid=:nid', array(':nid'=>(int)$nid));
	}


	public function validateUpload($fileUpload, $type, $device = null) {
		if(!$fileUpload) {
			return 500; //video or photo is mandatory
		}
		if ($type == 'photo') {
			$size = $fileUpload->getSize(); //in bytes
			if($size > 5 * 1024000) {
				return 501; //photo size out of limition
			}
			$mime = $fileUpload->getType();
			$allowMime = array(
				"image/gif", "image/png", "image/jpeg", "image/jpg", "image/pjpeg", "image/x-png"
			);
			if (!in_array($mime, $allowMime)) {
				return 502; //photo media type is not allowed
			}
			list($w, $h) = getimagesize($fileUpload->tempName);
			if($w < 450 || $h < 450) {
				return 503; //photo resolution is too small
			}
		}

		if ($type == 'avatar') {
			$size = $fileUpload->getSize(); //in bytes
			if($size > 5 * 1024000) {
				return 501; //photo size out of limition
			}
			$mime = $fileUpload->getType();
			$allowMime = array(
				"image/gif", "image/png", "image/jpeg", "image/jpg", "image/pjpeg", "image/x-png"
			);
			if (!in_array($mime, $allowMime)) {
				return 502; //photo media type is not allowed
			}
			list($w, $h) = getimagesize($fileUpload->tempName);
			if($w < 100 || $h < 100) {
				return 503; //photo resolution is too small
			}
		}

		if ($type == 'video') {
			$size = $fileUpload->getSize(); //in bytes
			if($device == 'android') {
				$allowsize = 16 * 1024000;
			}
			else {
				$allowsize = 7 * 1024000;
			}

			if($size > $allowsize) {
				return 501; //video size out of limitation
			}
			$mime = $fileUpload->getType();
			$allowMime = array(
				"application/x-empty" ,
				"video/mp2p" ,
				"video/mov",
				"video/quicktime",
				"video/x-msvideo",
				"video/x-ms-wmv",
				"video/wmv",
				"video/mp4",
				"video/avi",
				"video/3gp",
				"video/3gpp",
				"video/mpeg",
				"video/mpg",
				"application/octet-stream",
				"video/x-ms-asf",
				"video/x-ms-dvr",
				"video/x-ms-wm",
				'video/x-ms-wmv',
				'video/x-msvideo',
				'video/x-ms-asx',
				'video/x-ms-wvx',
				'video/x-ms-wmx',
				'application/x-troff-msvideo',
			);
			if (!in_array($mime, $allowMime)) {
				return 502; //video media type is not allowed
			}
		}

		return true;
	}

  /**
   * 
   * @param CUploadedFile $upload
   */
	public function saveUploadedFile($upload, $device = null) {
		$dir = ROOT."/uploads";
		if (!is_dir($dir)) {
		  mkdir($dir, 0777, TRUE);
		}

		$dir .= '/'.date("Y/n/j");
		if (!is_dir($dir)) {
		  mkdir($dir, 0777, TRUE);
		}
		$photoexts = explode(",", self::ALLOW_UPLOADED_PHOTO_TYPES);
		$videoexts = explode(",", self::ALLOW_UPLOADED_VIDEO_TYPES);
		$extname = strtolower(pathinfo($upload->getName(), PATHINFO_EXTENSION));
		$extnameArray = explode("?", $extname);
		$extname = $extnameArray[0];
		if(empty($extname)){
			exec("/usr/bin/file -b --mime {$upload->tempName}", $output, $status);
			$mime = explode(';',$output[0]);
			$mime = $mime[0];
			$extname = explode('/',$mime);
			$extname = $extname[1];
		}
		if (in_array($extname, $photoexts)) {
			$filename = md5( uniqid() . '_' . $upload->getName() ) . '.jpg';
			$to = $dir."/". $filename;
			switch($extname) {
				case 'gif':
					$srcImg = @imagecreatefromgif($upload->tempName);
					break;
				case 'png':
					$srcImg = @imagecreatefrompng($upload->tempName);
					break;
				default:
					$srcImg = @imagecreatefromjpeg($upload->tempName);
			}
			$exif = @exif_read_data($upload->tempName);
			if (!empty($exif['Orientation'])) {
				switch ($exif['Orientation']) {
					case 3:
						$srcImg = imagerotate($srcImg, 180, 0);
						break;

					case 6:
						$srcImg = imagerotate($srcImg, -90, 0);
						break;

					case 8:
						$srcImg = imagerotate($srcImg, 90, 0);
						break;
				}
			}
			if($srcImg) {
				imagejpeg($srcImg, $to, 90);
				imagedestroy($srcImg);
			}
			else {
				return false;
			}
		}

		// check if the video type
		if (in_array($extname, $videoexts)) {
			$filename = md5( uniqid() . '_' . $upload->getName() ) . '.' .$extname ;
			$to = $dir."/". $filename;
			$ret = $upload->newSaveAs($to);
      
			if (!$ret) {
				return FALSE;
			}

			exec("which ffmpeg", $output);
			if(!$output) {
				exec("which ffmpeg 2>/dev/null 2>&1",$output);
			}
			if (!empty($output)) {
				$ffmpeg = array_shift($output);
				if ($ffmpeg) {
					// ffmpeg 确定安装后 检查 ffmpeg 进程个数
					// 如果超过了 ALLOW_MAX_FFMPEG_COUNT 阀值 就直接返回FALSE
					// 然后保存文件 并且返回 临时文件路径
					if (self::ffmpeg_process_count() >= self::ALLOW_MAX_FFMPEG_COUNT) {
						return array(FALSE, $to);
					}
					$newpath = pathinfo($to, PATHINFO_FILENAME)."_new.". self::ALLOW_STORE_VIDE_TYPE;
					$dir = pathinfo($to, PATHINFO_DIRNAME);
					$newpath = $dir.'/'. $newpath;

					if ($newpath != $to) {
							//if (1) {
						$status;
						$output;
						$rotate = '';
						$orientation = $this->get_video_orientation($to);
						switch ($orientation) {
							case 90:
								$rotate = '-vf "transpose=1" -metadata:s:v:0 rotate=0';
								break;
							case 270:
								$rotate = '-vf "transpose=2" -metadata:s:v:0 rotate=0';
								break;
							case 180:
								$rotate = '-vf "transpose=1,transpose=1" -metadata:s:v:0 rotate=0';
								break;
						}

						$size = '';
						if($device == 'android') {
							$size = '-filter:v scale=720:-1';
						}


                        // 视频转换
						switch($extname) {
							case 'mp4':
								exec("ffmpeg  -i {$to} -vcodec libx264 {$size} -movflags +faststart -acodec aac -strict experimental -ac 2 {$rotate} {$newpath}", $output, $status);
								break;
							case 'mpg':
								exec("ffmpeg -i {$to} -vcodec libx264 -movflags +faststart -acodec aac -strict experimental -ac 2 {$newpath}", $output, $status);
								break;
							case 'mpeg':
								exec("ffmpeg -i {$to} -vcodec libx264 -movflags +faststart -acodec aac -strict experimental -ac 2 {$newpath}", $output, $status);
								break;
							case 'mov':
								exec("ffmpeg -i {$to} -vcodec libx264 -movflags +faststart -acodec aac -strict experimental -ac 2 {$rotate} {$newpath}", $output, $status);
								if(!is_file($newpath) || !$this->is_valid_video($newpath)) {
									exec("ffmpeg -i {$to} -acodec copy -vcodec copy {$rotate} {$newpath}", $output, $status);
								}
								break;
							case 'wmv':
								exec("ffmpeg -i {$to} -movflags +faststart -strict -2 -ar 44100 {$newpath}", $output, $status);
								break;
							case '3gp':
								exec("ffmpeg -i {$to} -movflags +faststart -strict -2 -ar 44100 {$newpath}", $output, $status);

								break;
							case 'avi':
								exec("ffmpeg -i {$to} -vcodec libx264 -movflags +faststart -acodec aac -strict experimental -ac 2 {$newpath}", $output, $status);
								break;
							default:
								exec("ffmpeg -i {$to} -vcodec libx264 -movflags +faststart -acodec aac -strict experimental -ac 2 {$newpath}", $output, $status);
						}


                        if (!$this->is_valid_video($newpath)) {
                            return FALSE;
                        }

						if (!is_file($newpath)) {
							return FALSE;
						}

						if(is_file($to)) {
							unlink($to);
						}
						$to = $newpath;
					}

				}
			}
			else {
				return false;
			}
		}


		if(isset($to) && is_file($to)) {
			$to = str_replace(ROOT, "", $to);
			return $to;
		}
		else {
			return false;
		}
  	}

	function get_video_orientation($video_path) {
		$cmd = "/usr/bin/ffprobe " . $video_path . " -show_streams 2>/dev/null 2>&1";
		$result = shell_exec($cmd);
		$orientation = 0;
		if(strpos($result, 'TAG:rotate') !== FALSE) {
			$result = explode("\n", $result);
			foreach($result as $line) {
				if(strpos($line, 'TAG:rotate') !== FALSE) {
					$stream_info = explode("=", $line);
					$orientation = $stream_info[1];
				}
			}
		}

		return $orientation;
	}

    function is_valid_video($path) {
        $cmd = "/usr/bin/ffprobe " . $path . "  2>/dev/null 2>&1";
        $result = shell_exec($cmd);
        if (strpos($result, "Invalid") === FALSE && strpos($result, " fault") === FALSE) {
            return TRUE;
        }
        return FALSE;
    }



	public function makeImageThumbnail($path, $save_to, $w, $h, $isOutput) {
		$abspath = $path;
		$abssaveto = $save_to;
		$thumb = new EasyImage($abspath);


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

		$thumb->resize($t_w, $t_h);
		if (!$widthSamller) {
			$start_x = ($t_w - $w)/2;
			$start_y = 0;
			$thumb->crop($w, $h, $start_x, $start_y);
		}
		else {
			$start_x = 0;
			$start_y = ($t_h - $h);
			$thumb->crop($w, $h, $start_x, $start_y);
		}

		$thumb->save($abssaveto);

		if($isOutput) {
			$fp = fopen($abssaveto, "rb");
			if ($size && $fp) {
				header("Content-type: {$size['mime']}");
				fpassthru($fp);
				exit;
			} else {
				// error
			}
		}
	}

	/**
	 * Generate Video Thumbnail
	 */
	public function makeVideoThumbnail($screenImagePath, $saveTo, $w, $h, $isOutput) {
		$paths = explode(".",$screenImagePath);
		$basename = array_shift($paths);
		$output = NULL;
		$status = NULL;
		$absscreenImagePath = $screenImagePath;
		$abssaveTo = $saveTo;
		$absvideoPath = str_replace('.jpg','.mp4',$screenImagePath);

		if (!file_exists($absscreenImagePath)) {

			exec("ffmpeg -ss 00:00:03 -i $absvideoPath -vframes 1 -an -f image2 ".$absscreenImagePath, $output, $status);

			if (!file_exists($absscreenImagePath)) {
                exec("ffmpeg -i $absvideoPath -vframes 1 -an -f image2 ".$absscreenImagePath, $output, $status);
			}
		};
		if($w && $h) {
			$this->makeImageThumbnail($absscreenImagePath, $saveTo, $w, $h, $isOutput);
		}

	}

	public function cropPhoto($file, $x, $y, $width, $scale_size) {
		$thumb = new EasyImage(ROOT.$file);
		$size = $thumb->resize(1000,1000,EasyImage::RESIZE_AUTO);
		$srcWidth = $size->width;
		$scale = $srcWidth / $width;
		$toX = -($x * $scale);
		$toY = -($y * $scale);
		$toWidth = $scale_size * $scale;
		$thumb->crop($toWidth,$toWidth,$toX,$toY);
		$thumb->save(ROOT.$file);
		return $file;
	}
  
	public function blockIt() {
		if ($this->nid) {
		  $this->updateByPk($this->nid, array("status" => self::UNPUBLISHED));
		}
	}

	public function countByType($uid, $type) {
		$query = new CDbCriteria();
		$query->select = array("count(*) AS nodecounts");
		$query->addCondition("uid=:uid");
		$query->addCondition("type=:type");
		$query->addCondition("status=:status");
		$query->params = array(
			":uid" => $uid,
			":type" => $type,
			":status" => 1
		);
		$res = $this->find($query);

		return $res->nodecounts;
	}

	public function countByDay($uid) {
		$query = new CDbCriteria();
		$query->select = "*". ",topday_id AS topday";
		$query->join = 'right join `topday` '.' on '. '`topday`' .".nid = ". $this->getTableAlias().".nid";
		$query->addCondition("uid=:uid");
		$query->addCondition("status=:status");
		$query->params = array(
			":uid" => $uid,
			":status" => 1
		);
		$res = $this->count($query);

		return $res;
	}

	public function countByMonth($uid) {
		$query = new CDbCriteria();
		$query->select = "*". ",topmonth_id AS topmonth";
		$query->join = 'right join `topmonth` '.' on '. '`topmonth`' .".nid = ". $this->getTableAlias().".nid";
		$query->addCondition("uid=:uid");
		$query->addCondition("status=:status");
		$query->params = array(
			":uid" => $uid,
			":status" => 1
		);
		$res = $this->count($query);

		return $res;
	}


  
	public function getAttributes($name=NULL) {
		$attrs = parent::getAttributes($name);
		$attrs["commentcount"] = $this->commentcount;
		$attrs["likecount"] = $this->likecount;
		$attrs["flagcount"] = $this->flagcount;
		return $attrs;
	}

	public function getPageByNid($nid, $pagenum) {
		$queryDate = new CDbCriteria();
		$queryDate->select = array("datetime");
		$queryDate->addCondition("nid=:nid");
		$queryDate->addCondition("status=1");
		$queryDate->params = array(
			":nid" => $nid
		);
		$datetime = $this->find($queryDate);

		$query = new CDbCriteria();
		$query->select = array("count(*) as nodecounts");
		$query->addCondition("datetime>:datetime");
		$query->addCondition("status=1");
		$query->order = "datetime desc";
		$query->params = array(
			":datetime" => $datetime->datetime
		);
		$res = $this->find($query);
		$page = ceil($res->nodecounts/$pagenum);

		return $page;
	}


	/*
	 * Save temp file
	 */
	public static function saveFile($file, $uid) {
		$ret = Yii::app()->db->createCommand()
			->insert('file', array('file'=>$file, 'uid'=>$uid));
		$file_id = Yii::app()->db->getLastInsertID();
		return $file_id;
	}


	/*
	 * Get temp file
	 */
	public static function getFile($file_id, $uid) {
		$ret = Yii::app()->db->createCommand()
			->select('file')
			->from('file')
			->where('file_id=:file_id and uid=:uid', array(':file_id'=>$file_id, ':uid'=>$uid))
			->queryRow();
		if(isset($ret)) {
			return $ret['file'];
		}
		else {
			return false;
		}

	}

	/*
	 * Delete temp file
	 */
	public static function deleteFile($file_id) {
		Yii::app()->db->createCommand()
			->delete('file', 'file_id=:file_id', array('file_id'=>$file_id));
	}

  
	public static function detechFileMime($path) {
		if (is_file($path)) {

		  $ret = exec("file -b --mime-type ". $path, $output, $staus);
		  if ($staus === 0 && $ret) {
		    return $ret;
		  }
		  else {
		    return FALSE;
		  }
		}
		return FALSE;
	}
  

	// 检查 ffmpeg 进程个数
	public static function ffmpeg_process_count() {
		$command = "ps -ef | grep -v grep | grep ffmpeg | wc -l";

		$descriptorspec = array(
		    0 => array("pipe", "r"),
		    1 => array("pipe", "w"),
		    2 => array("file", ROOT."/uploads/log.log", "w"),
		);

		$process = proc_open($command, $descriptorspec, $pipes);
		$can_be_convert = FALSE;
		if (is_resource($process)) {
		  fclose($pipes[0]);

		  $content = stream_get_contents($pipes[1]);
		  fclose($pipes[1]);

		  $ret_value = proc_close($process);

		  return intval(trim($content));
		}

		else {
		  return FALSE;
		}
	}
  
	# Linux / Centos only
	public static function cpu_core_count() {
		$command = "cat /proc/cpuinfo | grep -v grep | grep processor | wc -l";

		$descriptorspec = array(
		    0 => array("pipe", "r"),
		    1 => array("pipe", "w"),
		    2 => array("file", "/dev/null", "w"),
		);

		$process = proc_open($command, $descriptorspec, $pipes);
		if (is_resource($process)) {
		  fclose($pipes[0]);

		  $content = stream_get_contents($pipes[1]);
		  fclose($pipes[1]);

		  $ret_value = proc_close($process);

		  return intval(trim($content));
		}

		else {
		  // 打开进程失败
		  return FALSE;
		}
	}
}
