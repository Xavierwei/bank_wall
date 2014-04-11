<?php

class CountryController extends Controller {
  
	public function actionList() {
		$request = Yii::app()->getRequest();

		$query = new CDbCriteria();
		$query->order = 'country_name';
		$countryAr = new CountryAR();
		$list = $countryAr->findAll($query);

		$retdata = array();
		foreach ($list as $country) {
		  $retdata[] = $country->attributes;
		}

		$this->responseJSON($retdata, "success");
	}

	public function actionFilterList() {
		$request = Yii::app()->getRequest();
		$lang = $request->getParam('lang');
		$query = new CDbCriteria();
		$query->addCondition("node_count > 0");
		if($lang == 'fr') {
			$query->order = 'country_name_fr';
		}
		else {
			$query->order = 'country_name';
		}
		$countryAr = new CountryAR();
		$list = $countryAr->findAll($query);

		$retdata = array();
		foreach ($list as $country) {
			$data = $country->attributes;
			unset($data['node_count']);
			unset($data['flag_icon']);
			$retdata[] = $data;
		}

		$this->responseJSON($retdata, "success");
	}
  
	public function actionPost() {
		$request = Yii::app()->getRequest();

		if (!$request->isPostRequest) {
		  $this->responseError("http error");
		}

		if (!Yii::app()->user->checkAccess("addCountry")) {
		  return $this->responseError("permission deny");
		}

		$countryAr = new CountryAR();

		$country_id = $countryAr->postNewCountry();

		if ($country_id) {
		  $country = CountryAR::model()->findByPk($country_id);
		  $this->responseJSON($country->attributes, "success");
		}
		else {
		  $errors = $countryAr->getErrors();
		  $this->responseError("invalid error: ". print_r($errors, TRUE));
		}
	}
  
  public function actionPut() {
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      $this->responseError("http error");
    }
    
    if (!Yii::app()->user->checkAccess("updateCountry")) {
      return $this->responseError("permission deny");
    }
    
    $countryAr = new CountryAR();
    $country_id = $request->getPost("country_id");
    
    if (!$country_id) {
      $this->responseError("invalid params");
    }
    
    $country = CountryAR::model()->findByPk($country_id);
    
    if (!$country) {
      $this->responseError("invalid params");
    }
    foreach ($_POST as $key => $value) {
      $country->{$key} = $value;
    }
    CountryAR::model()->updateByPk($country_id, $country->attributes);
    
    $this->responseJSON($country->attributes, "success");
  }
  
  public function actionDelete() {
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      $this->responseError("http error");
    }
    
    if (!Yii::app()->user->checkAccess("deleteCountry")) {
      return $this->responseError("permission deny");
    }
    
    $country_id = $request->getPost("country_id");
    
    if (!$country_id) {
      $this->responseError("invalid params");
    }
    
    $country = CountryAR::model()->findByPk($country_id);
    if (!$country) {
      $this->responseError("not found country");
    }
    
    CountryAR::model()->deleteByPk($country_id);
    
    $this->responseJSON($country->attributes, "success");
  }


}

