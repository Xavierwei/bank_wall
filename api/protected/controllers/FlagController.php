<?php

class FlagController extends Controller {

	public function actionIndex() {
	$this->responseError("not support yet");
	}
  
	public function actionPost() {
		$request = Yii::app()->getRequest();

		if (!Yii::app()->user->checkAccess("flagNode")) {
		  return $this->responseError("permission deny");
		}

		if (!$request->isPostRequest) {
		  $this->responseError("http error");
		}

		$nid = $request->getPost("nid");
		$cid = $request->getPost("cid");
			$comment_nid = $request->getPost("comment_nid");

		//2者任意有一个
		if (!$nid && !$cid) {
		  $this->responseError("invalid params");
		}

		// 如果有数据但是不是数字
		// 说明参数错误
		if ($nid && !is_numeric($nid)) {
		  $this->responseError("invalid params");
		}
		if ($cid && !is_numeric($cid)) {
		  $this->responseError("invalid params");
		}
			if ($comment_nid && !is_numeric($comment_nid)) {
				$this->responseError("invalid params");
			}

		$uid = Yii::app()->user->getId();

		$flagAr = new FlagAR();
		$flagAr->uid = $uid;
		if ($nid) {
		  $flagAr->nid = $nid;
		}
		if ($cid) {
		  $flagAr->cid = $cid;
		}
		if ($comment_nid) {
			$flagAr->comment_nid = $comment_nid;
		}

		// 检查之前是否flag过
		if ($flagAr->nid) {
		  $flagold = FlagAR::model()->findByAttributes(array("uid" => $flagAr->uid, "nid" => $flagAr->nid));
		}
		else {
		  $flagold = FlagAR::model()->findByAttributes(array("uid" => $flagAr->uid, "cid" => $flagAr->cid));
		}

		if ($flagold) {
		  $this->responseError('flagged');
		}


		if ($flagAr->validate()) {
		  $flagAr->save();

		  $this->responseJSON($flagAr->attributes, "success");
		}
		else {
		  $this->responseError(current(array_shift($flagAr->getErrors())));
		}
	}





	public function actionGetFlaggedNodes() {
		if (!Yii::app()->user->checkAccess("listFlagedNode")) {
		  return $this->responseError(601);
		}
		$flagAr = new FlagAR();
		$query = new CDbCriteria();
		$query->addCondition($flagAr->getTableAlias().".nid <> 0");
		$query->select = "distinct ".$flagAr->getTableAlias().".nid AS distinct_nid" .$flagAr->getTableAlias().".*";
		$query->order = "flag_id DESC";
		$query->with = array("node");
		$flags = $flagAr->findAll($query);


		$retnodes = array();
		foreach ($flags as $flag) {
			if ($flag->node) {
				$retnodes[] = $flag->node->attributes;
			}
		}

		return $this->responseJSON($retnodes, "success");
	}


	public function actionGetFlaggedComments() {
		if (!Yii::app()->user->checkAccess("listFlagedComment")) {
			return $this->responseError(601);
		}
		$flagAr = new FlagAR();
		$query = new CDbCriteria();
		$query->addCondition($flagAr->getTableAlias().".cid <> 0");
		$query->group = $flagAr->getTableAlias().".cid";
		$query->order = "flag_id DESC";
		$query->with = array("node", "comment");
		$flags = $flagAr->findAll($query);

		$retcomments = array();
		foreach ($flags as $flag) {
			if($flag->comment) {
				$commentItem = $flag->comment->attributes;
				$commentItem['flagcount'] = CommentAR::model()->flagCountInComment($commentItem['cid']);
				$commentItem['node'] = $flag->comment->node;
				$retcomments[] = $commentItem;
			}

		}

		return $this->responseJSON($retcomments, "success");
	}


	public function actionGetSetting() {
		$setting = FlagAR::model()->getSetting();
		echo $setting;
	}

	public function actionSetSetting() {
		$request = Yii::app()->getRequest();

		if (!$request->isPostRequest) {
			$this->responseError(101);
		}

		$value = $request->getPost("value");
		if (!$value || !is_numeric($value)) {
			$this->responseError(101);
		}


		if (!Yii::app()->user->checkAccess("isAdmin")) {
			return $this->responseError(601);
		}

		$ret = FlagAR::model()->setSetting($value);

		return $this->responseJSON($ret, "success");

	}
}

