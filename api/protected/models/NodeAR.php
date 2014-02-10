<?php

class NodeAR extends CActiveRecord{
    
  const PUBLICHSED = 1;
  const UNPUBLISHED = 2;
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
  
  // 这个只是允许上传的视频格式
  const ALLOW_UPLOADED_VIDEO_TYPES = "mp4,avi,mov,mpg,3pg,wmv";

  // 其他格式的视频需要转换到这个指定的格式
  const ALLOW_STORE_VIDE_TYPE = "mp4";
  
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
        array("uid, country_id, type", "required"),
        array("uid", "uidExist"),
        array("country_id", "countryExist"),
        array("created ,file, type, datetime, status, description, nid, hashtag, user_liked,user_flagged, like, flag, topday, topmonth", "safe"),
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
    preg_match_all("/#([\\w']+)/", $description, $matches);
    $hashtags = end($matches);
    return $hashtags;
  }
  
  public function beforeSave() {
    parent::beforeSave();
    
    $hashtags = $this->getHashTag();
    // 在添加时 需要制定一个默认的 status = publichsed
    if (!$this->{$this->getPrimaryKey()}) {
        $this->setAttribute("status", self::PUBLICHSED);
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
    
    // 加载当前用户的flag/like状态
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
    
    // 加载 flagcount/ commentflag / likecount
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
      $file = $this->file;
      $nid = $this->nid;
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
        // 文件重命名后 修改数据库
        $this->updateByPk($this->nid, array("file" => $newpath));

        $this->file = $newpath;
      }

			// Generate WMV for no flash IE8
      if ($type == "video") {
        $topath = ROOT.$newpath;
        $wmvpath = str_replace('.mp4','.wmv',$topath);
        exec("ffmpeg -i {$topath} -y -vf scale=-1:360 {$wmvpath}", $output, $status);
      }
      // Load user/country
      $userAr = new UserAR();
      $userAr->setAttributes($userAr->getOutputRecordInArray(UserAR::model()->findByPk($this->uid)));
      $this->user = $userAr;
      
      $this->country = CountryAR::model()->findByPk($this->country_id);
      
      return TRUE;
  }

	public function validateUpload($fileUpload, $type) {
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
				"image/gif", "image/png", "image/jpeg", "image/jpg"
			);
			if (!in_array($mime, $allowMime)) {
				return 502; //photo media type is not allowed
			}
			list($w, $h) = getimagesize($fileUpload->tempName);
			if($w < 450 || $h < 450) {
				return 503; //photo resolution is too small
			}
		}

		if ($type == 'video') {
			$size = $fileUpload->getSize(); //in bytes
			if($size > 7 * 1024000) {
				return 501; //video size out of limitation
			}
			$mime = $fileUpload->getType();
			$allowMime = array(
				"video/mov", "video/wmv", "video/mp4", "video/avi", "video/3gp"
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
  public function saveUploadedFile($upload) {
    $dir = ROOT."/uploads";
    
    if (!is_dir($dir)) {
      mkdir($dir, 0777, TRUE);
    }
    

    $dir .= '/'.date("Y/n/j");
    if (!is_dir($dir)) {
      mkdir($dir, 0777, TRUE);
    }
    
    $extname = strtolower(pathinfo($upload->getName(), PATHINFO_EXTENSION));

    $filename = md5( uniqid() . '_' . $upload->getName() ) . '.' . $extname ;

    $to = $dir."/". $filename;
    $ret = $upload->saveAs($to);
    
    // 检查是不是视频， 如果是, 就就做视频转换工作
    $videoexts = explode(",", self::ALLOW_UPLOADED_VIDEO_TYPES);
    if (in_array($extname, $videoexts)) {
      // 在这里做视频转换功能
      // 先检查 ffmpeg 是否已经安装
      exec("which ffmpeg", $output);
      if (!empty($output)) {
        $ffmpeg = array_shift($output);
        if ($ffmpeg) {
          $newpath = pathinfo($to, PATHINFO_FILENAME).".". self::ALLOW_STORE_VIDE_TYPE;
          $dir = pathinfo($to, PATHINFO_DIRNAME);
          $newpath = $dir.'/'. $newpath;
          if ($newpath != $to) {
            $status;
            $output;
            // 视频转换
            switch($extname) {
              case 'mpg':
                exec("ffmpeg -i {$to} -c:v libx264 -c:a libfaac -r 30 {$newpath}", $output, $status);
                break;
              case 'mov':
                exec("ffmpeg -i {$to} -vcodec copy -acodec copy {$newpath}", $output, $status);
                break;
							case 'wmv':
								exec("ffmpeg -i {$to} -strict -2 -s size {$newpath}", $output, $status);
								break;
							case '3gp':
								exec("ffmpeg -i {$to} -strict -2 -ab 64k -ar 44100 {$newpath}", $output, $status);
								break;
							case 'avi':
								exec("ffmpeg -i {$to} -pix_fmt yuv420p -crf 19 -ac 2 {$newpath}", $output, $status);
								break;
              default:
                exec("ffmpeg -i {$to}  -vcodec mpeg4 -b:v 1200k -flags +aic+mv4 {$newpath}", $output, $status);
            }
            
            // 视频转换完后 要删掉之前的视频文件
            unlink($to);
            // 删除后， 再返回新的文件地址
            $to = $newpath;
          }

        }
      }
    }
    
    $to = str_replace(ROOT, "", $to);
    return $to;
  }

	public function cropPhoto($file, $x, $y, $width) {
		list($srcWidth, $srcHeight) = getimagesize(ROOT.$file);
		$scale = $srcWidth / $width;
		$toX = -($x * $scale);
		$toY = -($y * $scale);
		$toWidth = 175 * $scale;
		$thumb = new EasyImage(ROOT.$file);
		$thumb->crop($toWidth, $toWidth, $toX, $toY);
		$thumb->save(ROOT.$file);
		return $file;
	}
  
  public function blockIt() {
    if ($this->nid) {
      $this->updateByPk($this->nid, array("status" => self::BLOCKED));
    }
  }

  public function countByType($uid, $type) {
    $query = new CDbCriteria();
    $query->select = array("count(*) AS nodecounts");
    $query->addCondition("uid=:uid");
    $query->addCondition("type=:type");

    $query->params = array(
      ":uid" => $uid,
      ":type" => $type
    );

    $res = $this->find($query);

    return $res->nodecounts;
  }

  public function countByDay($uid) {
    // 从今天00:00:00开始
    $start_time = strtotime(date("Y-m-d"));
    $end_time = time();
    
    $query = new CDbCriteria();
    $query->select = array("count(*) AS nodecounts");
    $query->addCondition("datetime>:start");
    $query->addCondition("datetime<=:end");
    $query->addCondition("uid=:uid");
    
    $query->params = array(
        ":start" => $start_time,
        ":end" => $end_time,
        ":uid" => $uid
    );
    
    $res = $this->find($query);
    
    return $res->nodecounts;
  }

  public function countByMonth($uid) {
    // 从今天00:00:00开始
    $start_time = strtotime(date("Y-m-1"));
    $end_time = time();

    $query = new CDbCriteria();
    $query->select = array("count(*) AS nodecounts");
    $query->addCondition("datetime>:start");
    $query->addCondition("datetime<=:end");
    $query->addCondition("uid=:uid");

    $query->params = array(
        ":start" => $start_time,
        ":end" => $end_time,
        ":uid" => $uid
    );

    $res = $this->find($query);

    return $res->nodecounts;
  }


  
  public function getAttributes($name=NULL) {
    $attrs = parent::getAttributes($name);
    $attrs["commentcount"] = $this->commentcount;
    $attrs["likecount"] = $this->likecount;
    $attrs["flagcount"] = $this->flagcount;
    return $attrs;
  }

	public function getPageByNid($nid) {
		$query = new CDbCriteria();
		$query->select = array("count(*) as nodecounts");
		$query->addCondition("nid>:nid");
		$query->addCondition("status=1");
		$query->order = "nid desc";
		$query->params = array(
			":nid" => $nid
		);
		$res = $this->find($query);
		$page = ceil($res->nodecounts/20);
		return $page;
	}

}
