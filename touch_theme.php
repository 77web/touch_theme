<?php
/*
Plugin Name: Touch theme
Description: スマートフォンでのアクセス時、スマートフォン用テーマへの自動切り替えのみを行う簡易プラグイン
Auther: 77web
Auther URI: http://github.com/77web
*/

require_once dirname(__FILE__).'/lib/TouchTheme.php';
$ttObj = new TouchTheme();

add_filter('theme_root', array($ttObj, 'filterThemeRoot'));
add_filter('theme_root_uri', array($ttObj, 'filterThemeRootUri'));
add_action('wp_footer', array($ttObj, 'hockFooter'));
add_filter('the_content', array($ttObj, 'fixImageWidth'));



