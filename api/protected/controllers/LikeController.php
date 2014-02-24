<?php
class LikeController extends Controller {

	/**
	 * Like
	 */
	public function actionPost() {
		$request = Yii::app()->getRequest();

		if (!$request->isPostRequest) {
			$this->responseError(101);
		}

		if(Yii::app()->user->isGuest) {
			return $this->responseError(601);
		}

		if (!Yii::app()->user->checkAccess("flagNode")) {
			return $this->responseError(601);
		}

		$uid = Yii::app()->user->getId();
		$nid = $request->getPost("nid");

		$likeAr = new LikeAR();
		$likeAr->attributes = array(
		    "uid" => $uid,
		    "nid" => $nid
		);

		$currentUserLikeCount = $likeAr->getUserNodeCount($nid,$uid);

		if ($likeAr->validate()) {
			// Check if already liked
			if($currentUserLikeCount == 0) {
				$likeAr->save();
    
			// Like 成功后，需要清除缓存
			$this->cleanCache("node_")
				->cleanCache("comment_");
        
				$this->responseJSON($likeAr->getNodeCount($nid), "success");
			}
			else
			{
				$this->responseError('Already Liked');
			}
		}
		else {
			$this->responseError(current(array_shift($likeAr->getErrors())));
		}
	}

	/**
	 * Unlike
	 */
	public function actionDelete() {
		$request = Yii::app()->getRequest();

		if (!$request->isPostRequest) {
		  $this->responseError("http error");
		}

		if(Yii::app()->user->isGuest) {
		  return $this->responseError(601);
		}
		$uid = Yii::app()->user->getId();
		$nid = $request->getPost("nid");

		$likeAr = new LikeAR();
		$likeAr->deleteLike($uid, $nid);

		$this->cleanCache("node_")
			->cleanCache("comment_");
		$this->responseJSON($likeAr->getNodeCount($nid), "success");
	}
}

