<?php
class Drtool {

    /**
     * Generate random string
     */
    public static function randomNew()
    {
        return hash("sha1",time().md5(uniqid(rand(), TRUE)));
    }

    /**
     * set cookie
     */
    public static function setMyCookie($name,$val)
    {
        $cookie = new CHttpCookie($name, $val);
        $cookie->expire =time()+60*60*24*5;
        Yii::app()->request->cookies[$name]=$cookie;
    }

    /**
     * get cookie
     */
    public static function getMyCookie($name)
    {
      $cookie =Yii::app()->request->getCookies();
      if(is_null($cookie[$name])) //先判断对象是否存在。
        return NULL;
      else
        return $cookie[$name]->value; //对象存在返回cookie数值
    }

    /**
     * destory cookie
     */
    public static function cleanMyCookie($name)
    {
      $cookie =Yii::app()->request->getCookies();
      unset($cookie[$name]);
    }

}

