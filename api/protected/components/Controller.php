<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/column1';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();
    
    public function beforeAction($action) {
      try {
        $role = Yii::app()->user->role;
      }
      catch (CException $e) {
        Yii::app()->user->setState("role", 0);
        Yii::app()->user->setState("country_id", 0);
      }
      
      $cache_key = $this->cacheKey();
      $data = Yii::app()->cache->get($cache_key);
      if ($data) {
        $this->responseJSON($data, "success");
      }
      
      return parent::beforeAction($action);
    }
    
    public function responseError($message) {
         $this->_renderjson($this->wrapperDataInRest(NULL, $message, TRUE));
    }
    

  
    public function randomString($length = 10) {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $randomString = '';
      for ($i = 0; $i < $length; $i++) {
          $randomString .= $characters[rand(0, strlen($characters) - 1)];
      }
      return $randomString;
    }
    
    public function cacheKey() {
      $prefix = Yii::app()->controller->id.'_';
      $key = '';
      foreach ($_GET as $name => $value) {
        $key .= "key_{$name}_value_{$value}";
      }
      if ($key == "") {
        $key = Yii::app()->controller->id."_". Yii::app()->controller->action->id;
      }
      
      // 每个用户一个Cache
      // 匿名用户的 id 是一样的，所以共享同一个缓存
      $key .= "_ui_". Yii::app()->user->getId();

      $keys = Yii::app()->cache->get("keys");
      if ($keys == "") {
        $keys = array();
      }

      $keys[$key] = $key;
      Yii::app()->cache->set("keys" ,($keys));
      
      return $prefix.$key;
    }
    
    // 清理缓存
    // key 可以是前缀
    public function cleanCache($prefix = "node") {
      $keys = Yii::app()->cache->get("keys");
      foreach ($keys as $key) {
        // 因为是搜索前缀，所以只需要判断是不是第一个位置就OK
        if (strpos($key, $prefix) == 0) {
          Yii::app()->cache->delete($key);
        }
      }
      
      return $this;
    }

    // 在这里加缓存功能
    public function responseJSON($data, $message, $ext = array()) {
      $request = Yii::app()->getRequest();
      $controllerName = Yii::app()->controller->id;
      $actioName = Yii::app()->controller->action->id;
      if (!$request->isPostRequest) {

        //判断是否需要cache

        // api: node/list
        if ($controllerName == "node" && $actioName == "list") {
          $key = $this->cacheKey();
          // 3分钟失效
          Yii::app()->cache->set($key, $data, 60 * 3);
        }
        // api: comment/list
        if ($controllerName == "comment" && $actioName == "list") {
          $key = $this->cacheKey();
          Yii::app()->cache->set($key, $data, 60 * 3);
        }

        // 其他需要缓存的情况可以写在后面
      }
      $this->_renderjson($this->wrapperDataInRest($data, $message, FALSE, $ext));
    }

    public function wrapperDataInRest($data, $message = '', $error = FALSE, $ext = array()) {
      $json = array(
          "success" => !$error,
          "message" => $message,
          "data" => $data
      );

      if (!empty($ext)) {
        $json += $ext;
      }

      return $json;
    }

    private function _renderjson($data) {
      header("Content-Type: application/json; charset=UTF-8");
      print CJavaScript::jsonEncode($data);
      die();
    }
    
    // 辅助方法
    public function isPost() {
      return Yii::app()->getRequest()->isPostRequest;
    }
    
    public function isPut() {
      return Yii::app()->getRequest()->isPutRequest;
    }
    
    public function __construct($id, $module = null) {
      parent::__construct($id, $module);
      
      $id = Yii::app()->user->getId();
      // 未登陆情况下 设置一个默认的 useridentity
      if (!$id) {
        $userIdentity = new UserIdentity("", "");
        Yii::app()->user->login($userIdentity);
      }
    }
    
  public function inti() {
    parent:;init();
    
    Yii::app()->attachEventHandler("onError", array($this, "actionError"));
    Yii::app()->attachEventHandler("onException", array($this, "actionError"));
  }

  public function actionError() {
    $error = Yii::app()->errorHandler->error;
    if (!$error) {
      $event = func_get_arg(0);
      if ($event instanceof CExceptionEvent) {
        return $this->responseError("PHP Exception");
      }
    }
    $this->responseError(print_r($error, TRUE));
  }
}