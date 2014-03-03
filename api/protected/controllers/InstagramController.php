<?php
class OauthController extends Controller {

	private $instagram;

	public function __construct()
	{
		$this->instagram = new Instagram(array(
			'apiKey'      => INSTAGRAM_AKEY,
			'apiSecret'   => INSTAGRAM_SKEY,
			'apiCallback' => INSTAGRAM_CALLBACK_URL
		));
	}

	public function actionLoginInstagram() {
		echo "<a href='{$this->instagram->getLoginUrl()}'>Login with Instagram</a>";
	}

	public function actionInstagramCallback() {
//		$code = $_GET['code'];
//		$data = $this->instagram->getOAuthToken($code);
//		print_r($data);
		$data = new stdClass();
		$data->access_token = "1133838733.28fb0c1.18d82e8ec3454d49988ab2ee083be6fa";
		$this->instagram->setAccessToken($data);

		//echo 'Your username is: ' . $data->user->username;

		$media = $this->instagram->getTagMedia('9263');
		print_r($media);

		foreach ($media->data as $entry) {
			echo "<img src=\"{$entry->images->thumbnail->url}\">";
		}
	}
}