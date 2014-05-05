<?php

class LikeAR extends CActiveRecord {
  
	public $likecount = 0;


	public function tableName() {
		return "like";
	}

	public function primaryKey() {
		return "like_id";
	}

	public static function model($class = __CLASS__) {
		return parent::model($class);
	}

	public function rules() {
		return array(
		    array("nid", "NidExist"),
		    array("uid", "UidExist"),
		    array("datetime, like_id, nids, count, count_d ", "safe"),
		);
	}

	public function beforeSave() {
		if (!$this->getAttribute("datetime")) {
		  $this->setAttribute("datetime", time());
		}

		return true;
	}

	public function afterSave() {
		$nid = $this->getAttribute("nid");
		$node = NodeAR::model()->findByPk((int)$nid);
		$this->saveTopOfDay($node);
		$this->saveTopOfMonth($node);

		// Send mail to notify user
		$this->emailnotify();
	}

	public function emailnotify() {
		// The node id that be liked
		$nid = $this->getAttribute("nid");
		// The user id that like it
		$uid = $this->getAttribute("uid");
		if (!$nid || !$uid) {
			return FALSE;
		}
		$node = NodeAR::model()->findByPk($nid);
		$user = UserAR::model()->findByPk($uid);

		$description = $node->getAttribute("description");
		$company_email = $user->getAttribute("company_email");
		$personal_email = $user->getAttribute("personal_email");
		$siteDomain = Yii::app()->params['siteDomain'];
		$node_link = $siteDomain."/#/nid/". $nid;

		if (!$node || !$user) {
			return FALSE;
		}

		$begin = 'Bonjour '.$user->firstname.' '.$user->lastname.',\n\n';
		$end = '\n\nL\'équipe SG WALL';
		$begin_en = '\n\n\n\nDear '.$user->firstname.' '.$user->lastname.',\n\n';
		$end_en = '\n\nSG WALL Team';
		$msg_html = $begin.$user->getAttribute("firstname").' '.$user->getAttribute("lastname")." a aimé votre contenu:".$node_link.$end
					.$begin_en.$user->getAttribute("firstname").' '.$user->getAttribute("lastname")." liked your content:".$end_en;
		$mailler = Yii::app()->Smtpmail;
		$mailler->SetFrom("upload@wall150ans.com");
		$mailler->MsgHTML($msg_html);
		$mailler->AddAddress($company_email, "");
		//$mailler->AddCC($personal_email, "");
		$mailler->Subject = "Your content was liked!";
		if ($mailler->Send()) {
			return FALSE;
		}
		return TRUE;
	}

	public function updateAllTopOfDay() {
		return $this->updateAllTopOfDuringTime("day");
	}

	public function updateAllTopOfDuringTime($time = "day") {
		$query = new CDbCriteria();
		if ($time == "day") {
			$res = Yii::app()->db->createCommand()
				->select("group_concat(l.nid) as nids, count(distinct l.nid) as countd, count(l.nid) as count, from_unixtime(l.datetime, '%Y-%m-%e') as datetime")
				->from("like as l")
				->group('from_unixtime(l.datetime, "%Y-%m-%e")');
		}
		else if ($time == "month") {
			$res = Yii::app()->db->createCommand()
				->select("group_concat(l.nid) as nids, count(distinct l.nid) as countd, count(l.nid) as count, from_unixtime(l.datetime, '%Y-%m') as datetime")
				->from("like as l")
				->group('from_unixtime(l.datetime, "%Y-%m")');
		}
		else {
			return FALSE;
		}

		$rows = $res->queryAll();
		// Find out node that most liked in one day.
		$top_likes = array();
		foreach ($rows as $row) {
			$nids = explode(",", $row["nids"]);
			if ($row["count"] == $row["countd"]) {
				$top_likes[] = array(
					"datetime" => $row["datetime"],
					"nid" => $nids[0],
				);
			}
			else {
				$c_nids = array();
				foreach ($nids as $nid) {
					if (!isset($c_nids[$nid])) {
						$c_nids[$nid] = 0;
					}
					$c_nids[$nid] += 1;
				}
				asort($c_nids, SORT_NUMERIC);
				$top_likes[] = array(
					"datetime" => $row["datetime"],
					"nid" => array_shift(array_keys($c_nids))
				);
			}
		}

		// Then save or update with datetime.
		$sql = "INSERT INTO topday (nid, `date`) VALUES ";
		$values = array();
		foreach ($top_likes as $top_like) {
			$values[] = '( '.$top_like["nid"]. ','. strtotime($top_like["datetime"]). ')';
		}
		if (count($values)) {
			$str_values = implode(",", $values);
			$sql .= $str_values;
		}
		// IMPORTANT: When there's duplicate item, we update it only
		$sql .= " ON DUPLICATE KEY UPDATE nid=VALUES(nid)";
		$command = Yii::app()->db->createCommand($sql);
		// Return number of  affected rows
		$ret = $command->execute();
		return $ret;
	}

	public function updateAllTopOfMonth() {
		return $this->updateAllTopOfDuringTime("month");
	}


	/**
	 * Set content of day
	 */
	public function saveTopOfDay($node) {
		$str_datetime = $node->datetime;
		$datetime = date('Y-m-d',$str_datetime);
		$start_time = strtotime($datetime);
		$end_time = strtotime($datetime) + 1*24*60*60;
		$query = new CDbCriteria();
		$query->select = array("`node`.nid". ", count(like_id) AS likecount");
		$query->addCondition("`node`.datetime>:start");
		$query->addCondition("`node`.datetime<=:end");
		$query->join = 'left join `node` '.' on '. $this->getTableAlias() .".nid = `node`.nid";
		$query->params = array(
			":start" => $start_time,
			":end" => $end_time
		);
		$query->group = "`node`.nid";
		$query->order = "likecount desc";
		$res = $this->find($query);
		if(!$res) {
			return Yii::app()->db->createCommand()->delete('topday', 'date=:date', array(':date'=>$start_time));
		}
		else {
			$updateRes = Yii::app()->db->createCommand()->update('topday', array(
				'nid'=>$res->nid,
			), 'date=:date', array(':date'=>$start_time));
		}

		if(!$updateRes) {
			$count = Yii::app()->db->createCommand()->select('count(*) as count')
				->from('topday')
				->where('date=:date', array('date'=>$start_time))
				->queryRow();
			if(!$count['count'])
			{
				Yii::app()->db->createCommand()->insert('topday', array(
					'nid'=>$res->nid,
					'date'=>$start_time
				));
			}
		}
	}


	/**
	 * Set content of month
	 */
	public function saveTopOfMonth($node) {
		$str_datetime = $node->datetime;
		$datetime = date('Y-m-1',$str_datetime);
		$start_time = strtotime($datetime);
		$end_time = strtotime(date("Y-m-1", $start_time) . " +1 month");
		$query = new CDbCriteria();
		$query->select = array("`node`.nid". ", count(like_id) AS likecount");
		$query->addCondition("`node`.datetime>:start");
		$query->addCondition("`node`.datetime<=:end");
		$query->join = 'left join `node` '.' on '. $this->getTableAlias() .".nid = `node`.nid";
		$query->params = array(
			":start" => $start_time,
			":end" => $end_time
		);
		$query->group = "`node`.nid";
		$query->order = "likecount desc";
		$res = $this->find($query);
		if(!$res) {
			return Yii::app()->db->createCommand()->delete('topmonth', 'date=:date', array(':date'=>$start_time));
		}
		$updateRes = Yii::app()->db->createCommand()->update('topmonth', array(
			'nid'=>$res->nid,
		), 'date=:date', array(':date'=>$start_time));

		if(!$updateRes) {
			$count = Yii::app()->db->createCommand()->select('count(*) as count')
				->from('topmonth')
				->where('date=:date', array('date'=>$start_time))
				->queryRow();
			if(!$count['count'])
			{
				Yii::app()->db->createCommand()->insert('topmonth', array(
					'nid'=>$res->nid,
					'date'=>$start_time
				));
			}
		}
	}


	/**
	 * Get node like status
	 */
	public function getNodeCount($nid) {
		$query=new CDbCriteria;
		$query->condition='nid=:nid';
		$query->params=array(':nid'=>$nid);
		$res=$this->count($query);

		return $res;
	}

	/**
	 * Get like counts
	 */
	public function getUserNodeCount($nid,$uid) {
		$query=new CDbCriteria;
		$query->addCondition('nid=:nid');
		$query->params[':nid']=$nid;
		$query->addCondition('uid=:uid');
		$query->params[':uid']=$uid;
		$res=$this->count($query);

		return $res;
	}

	/**
	 * Delete like
	 */
	public function deleteLike($uid, $nid) {
		$query = new CDbCriteria();
		$query->addCondition("nid = :nid");
		$query->addCondition("uid = :uid");
		$query->params[":uid"] = $uid;
		$query->params[":nid"] = $nid;
		$this->deleteAll($query);
		$node = NodeAR::model()->findByPk($nid);
		$this->saveTopOfDay($node);
		$this->saveTopOfMonth($node);
		return;
	}

	/**
	 * Get total like count
	*/
	public function totalLikeByUser($uid) {
		$query = new CDbCriteria();
		$query->select = array("count(*) AS likecount");
		$query->addCondition("uid = :uid");
		$query->params[":uid"] = $uid;

		$res = $this->find($query);

		return $res["likecount"];
	}
}

