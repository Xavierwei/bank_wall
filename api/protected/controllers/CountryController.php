<?php

/**
 * @author Jackey <jziwenchen@gmail.com>
 */
class CountryController extends Controller {
  
  public function actionList() {
    $request = Yii::app()->getRequest();
    
    $countryAr = new CountryAR();
    $list = $countryAr->findAll();
    
    $retdata = array();
    foreach ($list as $country) {
      $retdata[] = $country->attributes;
    }
    
    $this->responseJSON($retdata, "success");
  }
  
  public function actionPost() {
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      $this->responseError("http error");
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

