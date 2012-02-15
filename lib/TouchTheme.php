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
  
  public function clearTransients()
  {
    //delete_site_transient('theme_roots');
  }

  public static function isTouch()
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
  
  public function hockFooter()
  {
    if(self::isTouchDevice())
    {
      $currentUri = $_SERVER['REQUEST_URI'];
      $params = array();
      if(strpos($currentUri, '?'))
      {
        list($currentUri, $queryString) = explode('?', $currentUri);
        $params = $_GET;
      }
      
      if(self::isTouch())
      {
        $params['touch_theme_mode'] = 'pc';
        $switchTo = 'パソコン';
      }
      else
      {
        $params['touch_theme_mode'] = 'touch';
        $switchTo = 'スマートフォン';
      }
      $switchLink = $currentUri.'?'.http_build_query($params);
      
      echo '<div id="touchThemeSwitcher">表示方法：<a href="'.$switchLink.'">'.$switchTo.'</a></div>';
    }
  }
  
  public function fixImageWidth($content)
  {
    if(self::isTouch())
    {
      if(preg_match_all("/<img[^>]+>/is", $content, $matches, PREG_SET_ORDER))
      {
        foreach($matches as $match)
        {
          $orig_tag = $match[0];
          
          if(preg_match("/width=\"([0-9]+)\"/is", $orig_tag, $_matches) && intval(str_replace('px', '', $_matches[1])) > 320)
          {
            $new_tag = preg_replace(array("/height=\"[0-9]+\"/i", "/width=\"[0-9]+\"/i"), array('', 'width="100%"'), $orig_tag);
          }
          elseif(preg_match("/src=([^\s]+)/i", $orig_tag, $_matches))
          {
            $imgurl = str_replace('"', '', $_matches[1]);
            $homeUrl = get_bloginfo('url');
            if(strpos($imgurl, $homeUrl)!==false)
            {
              $homePath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
              $imgurl = str_replace($homeUrl, $homePath, $imgurl);
            }
            $size = @getimagesize($imgurl);
            
            if($size && is_array($size))
            {
              $width = $size[0];
              if($width > 320)
              {
                $new_tag = str_replace('<img', '<img width="100%"', $orig_tag);
              }
            }
          }
          
          if(isset($new_tag))
          {
            $content = str_replace($orig_tag, $new_tag, $content);
            unset($new_tag);
          }
        }
      }
    }
    return $content;
  }
}