<?php

class TouchTheme
{
  protected static $initialized;
  protected static $_isTouch;
  protected static $showTouch;
  protected static $userAgent;
  protected static $mode;
  
  public static function initialize()
  {
    if(!self::$initialized)
    {
      self::$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '-';
      self::$_isTouch = strpos(self::$userAgent, 'Android')!==false || strpos(self::$userAgent, 'iPhone');
      
      @session_start();
      self::$mode = (isset($_SESSION['wp_touch_theme_mode_is_pc']) && $_SESSION['wp_touch_theme_mode_is_pc'] && (!isset($_GET['touch_theme_mode']) || $_GET['touch_theme_mode']!='touch')) || (isset($_GET['touch_theme_mode']) && $_GET['touch_theme_mode'] == 'pc') ? 'pc' : 'touch';
      $_SESSION['wp_touch_theme_mode_is_pc'] = ('pc' === self::$mode);
      unset($_GET['touch_theme_mode']);
      
      self::$showTouch = self::$_isTouch ? (self::$mode == 'touch') : false;
      self::$initialized = true;
    }
    
  }

  protected static function isTouch()
  {
    if(null === self::$showTouch)
    {
      self::initialize();
    }
    return self::$showTouch;
  }
  
  protected static function isTouchDevice()
  {
    if(null === self::$_isTouch)
    {
      self::initialize();
    }
    return self::$_isTouch;
  }
  
  public function filterThemeRoot($root)
  {
    if(self::isTouch())
    {
      return dirname(dirname(__FILE__)).'/themes/';
    }
    return $root;
  }

  public function filterThemeRootUri($root)
  {
    if(self::isTouch())
    {
      return plugins_url().'/touch_theme/themes';
    }
    return $root;
  }
  
  public function filterGetTemplate($template)
  {
    return $template;
  }
  
  public function hockFooter()
  {
    if(self::isTouchDevice())
    {
      $currentUri = $_SERVER['REQUEST_URI'];
      $sep = strpos($currentUri, '?')!==false ? '&' : '?';
      if(self::isTouch())
      {
        $switchLink = $currentUri.$sep.'touch_theme_mode=pc';
        $switchTo = 'PC';
      }
      else
      {
        $switchLink = $currentUri.$sep.'touch_theme_mode=touch';;
        $switchTo = 'Touch';
      }
      echo '<div id="touchThemeSwitcher"><a href="'.$switchLink.'">'.$switchTo.'</a>';
    }
  }
}