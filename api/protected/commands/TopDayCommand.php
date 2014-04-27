<?php

class TopDayCommand extends CConsoleCommand {

	public function actionUpdateTopOfDay() {
		print "time: ".date("Y-m-d")." -- Begin update top of day in node like. \r\n";
		$likeAr = new LikeAR();
		$ret = $likeAr->updateAllTopOfDay();
		print "time: ".date("Y-m-d")." -- Finished update top of day in node like. \r\n";

		return 0;
	}

	public function actionUpdateTopOfMonth() {
		print "time: ".date("Y-m-d")." -- Begin update top of month in node like. \r\n";
		$likeAr = new LikeAR();
		$ret = $likeAr->updateAllTopOfMonth();
		print "time: ".date("Y-m-d")." -- Finished update top of month in node like. \r\n";

		return 0;
	}
}