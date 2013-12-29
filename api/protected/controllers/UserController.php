<?php

/**
 * @author Jackey <jziwenchen@gmail.com>
 */
class UserController extends Controller {

  public function actionIndex() {
    return $this->responseError("not support yet");
  }
  
  /**
   * 获取用户列表
   */
  public function actionList() {
    $request = Yii::app()->getRequest();
    $role = $request->getParam("role");
    $country_id = $request->getParam("countryid");
    $orderby = $request->getParam("orderby");

    // 验证参数
    // 国家必须是数字
    if ($country_id && !is_numeric($country_id)) {
      $country_id = FALSE;
    }
    
    // 在这里有一个逻辑:
    // 当用户是 country manager 的时候，我们只取这个user 所属的 country_id的用户， 所以 country_id 不管传入参数如何，都要重置
    if (!Yii::app()->user->checkAccess("list_all_user") && Yii::app()->user->checkAccess("list_user_in_country")) {
      $login_uid = Yii::app()->user->getId();
      $user = UserAR::model()->findByPk($login_uid);
      //$country_id = $user->country_id;
    }
    
    if (!Yii::app()->user->checkAccess("list_user_in_country") && !Yii::app()->user->checkAccess("list_all_user")) {
      //return $this->responseError("permission deny");
    }

    // Role 必须是数字
    if (!$role) {
      $this->responseError("invalid params role");
    }
    if ($role && !is_numeric($role)) {
      $this->responseError("invalid params role");
    }

    // orderby 必须是数据库字段
    $columns = UserAR::model()->getTableSchema()->columns;
    if (!isset($columns[$orderby])) {
      $this->responseError("invalid params orderby");
    }

    $query = new CDbCriteria();
    $query->addCondition("role=:role");

    if ($country_id) {
      $query->addCondition(UserAR::model()->tableAlias . ".country_id = :country_id");
    }

    $query->order = $orderby . " DESC";

    $query->params = array(":role" => $role, ":country_id" => $country_id);

    $users = UserAR::model()->with("country")->findAll($query);

    // 查询后，组装返回的数据
    $array_users = array();
    foreach ($users as $user) {
      $array = $user->attributes;
      $country = $user->country;
      $array["country"] = $country->attributes;

      $array_users[] = $array;
    }

    $allow_fields = array("uid", "firstname", "lastname", "avatar", "country" => array("country_name", "flag_icon"),
        "company_email", "personal_email", "role", "status");

    $ret_data = array();
    foreach ($array_users as $user) {
      $ret_user = array();
      foreach ($allow_fields as $key => $field) {
        if (is_numeric($key)) {
          $ret_user[$field] = $user[$field];
        } else {
          if (is_array($field)) {
            $ret_user[$key] = array();
            foreach ($field as $sub_field) {
              $ret_user[$key][$sub_field] = $user[$key][$sub_field];
            }
          }
        }
      }
      $ret_data[] = $ret_user;
    }

    $this->responseJSON($ret_data, "success");
  }
  
  /**
   * 用uid 获取用户资料
   */
  public function actionGetuserbyuid() {
    $request = Yii::app()->getRequest();
    $uid = $request->getParam("uid");
    
    if ($uid) {
      $user = UserAR::model()->with("country")->findByPk($uid);
      if ($user) {
        $ret_user = UserAR::getOutputRecordInArray($user);
        $this->responseJSON($ret_user, "success");
      }
      else {
        $this->responseError("not found user");
      }
    }
    else {
      $this->responseError("invalid params");
    }
  }

  /**
   * Post/ 添加用户
   */
  public function actionPost() {
    $arUser = new UserAR();

    $request = Yii::app()->getRequest();

    // Only allow post
    if ($this->isPost()) {
      $id = $arUser->postNewUser();
      if ($id) {
        $mUser = UserAR::model()->findByPk($id);

        // 返回数据
        $this->responseJSON(array(
            "uid" => $id,
            "lastname" => $mUser->lastname,
            "firstname" => $mUser->firstname,
            "avadar" => $mUser->avadar,
                ), Yii::t("strings", "success"));
      } else {
        $this->responseError($arUser->errorsString());
      }
    } else {
      $this->responseError(Yii::t("strings", "http error"));
    }
  }
  
  /**
   * 删除用户
   */
  public function actionDelete() {
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      $this->responseError("http error");
    }
    
    $uid = $request->getPost("uid");
    if (!$uid) {
      $this->responseError("invalid params");
    }
    $user = UserAR::model()->with("country")->findByPk($uid);
    
    if ($user) {
      UserAR::model()->deleteByPk($user->uid);
      $this->responseJSON(array(), "success");
    }
    else {
      $this->responseJSON(array(), "success");
    }
  }
  
  /**
   * 更改用户资料 
   */
  public function actionUserput() {
    $request = Yii::app()->getRequest();
    if (!$request->isPostRequest) {
      $this->responseError("http error");
    }
    
    $uid = $request->getPost("uid");
    
    if ($uid) {
      $user = UserAR::model()->findByPk($uid);
      if (!$user) {
        $this->responseError("invalid params");
      }
      
      $data = $_POST;
      foreach ($data as $key => $value) {
        if ($user->{$key}) {
          $user->{$key} = $value;
        }
      }
      UserAR::model()->updateByPk($uid, $user->attributes);
      
      $this->responseJSON($user, "success");
    }
    else {
      $this->responseError("invalid params");
    }
  }
  
  public function actionLogin() {
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      $this->responseError("http error");
    }
    
    $company_email = $request->getPost("company_email");
    $password = $request->getPost("password");
    
    $userIdentify = new UserIdentity($company_email, $password);
    
    // 验证没有通过
    if (!$userIdentify->authenticate()) {
      // 不必把对应的错误消息返回给客户端， 客户端只需知道登陆失败即可
      // 可以避免Hacker 根据错误消息来推敲我们系统的运行机制和用户密码/帐号
      $this->responseError("login failed");
    }
    else {
      Yii::app()->user->login($userIdentify);
      
      $userAr = new UserAR();
      $this->responseJSON($userAr->getOutputRecordInArray(UserAR::model()->findByPk(Yii::app()->user->getId())), "success");
    }
  }
  
  public function actionLoginForm() {
    $request = Yii::app()->getRequest();
    $loginform = new LoginForm();
    
    if (!$request->isPostRequest) {
      $params = array("model" => $loginform);
      $this->render("login", $params);
    }
    else {
      $loginform->attributes = $_POST["LoginForm"];
      if ($loginform->validate()) {
        Yii::app()->user->login($loginform->getUserIdentify());
        $this->redirect(Yii::app()->user->returnUrl);
      }
      else {
        $params = array("model" => $loginform);
        $this->render("login", $params);
      }
    }
  }
  
  public function actionTest() {
    $this->responseError("test");
  }

}
