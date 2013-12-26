<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CountryAR
 *
 * @author jackey
 */
class CountryAR extends CActiveRecord{
  
  public function tableName() {
    return "country";
  }
  
  public function getPrimaryKey() {
    return "country_id";
  }
  
  public function rules() {
    return array(
        array("country_name", "required"),
        array("code", "required"),
        array("flag_icon", "required"),
    );
  }
}
