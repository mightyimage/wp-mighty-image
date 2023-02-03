<?php
/*
Plugin Name: MightyImage
Description: A WordPress plugin to automatically fetch your WordPress images via <a href="https://www.mightyimage.io" target="_blank">MightyImage</a> for optimization and fast delivery. <a href="https://mightyimage.io/blog" target="_blank">Learn more</a> from documentation.
Author: MightImage
Author URI: https://mightyimage.io
Version: 1.0.0
*/

// Variables
$mightyimage_options = get_option('mightyimage_settings');

if (!defined('ABSPATH')) {
  exit;
}

if (!defined('MI_PLUGIN_PATH')) {
    define('MI_PLUGIN_PATH', __DIR__);
}

if (!defined('MI_PLUGIN_ENTRYPOINT')) {
    define('MI_PLUGIN_ENTRYPOINT', __FILE__);
}
  
if (!defined(('MI_DEBUG'))) {
    define('MI_DEBUG', false);
}


add_action('template_redirect', function () {

    global $mightyimage_options;
  
    if (isset($mightyimage_options['mightyimage_id'])) {
      $mightyimageId = $mightyimage_options['mightyimage_id'];
    }
  
    if (isset($mightyimage_options['mightyimage_url_endpoint'])) {
      $mightyimageUrlEndpoint = $mightyimage_options['mightyimage_url_endpoint'];
    }
  
    if (empty($mightyimageId) && empty($mightyimageUrlEndpoint)) {
      return;
    }
  
    // load class
    require_once __DIR__ . '/lib/MightyImageReWriter.php';
    require_once __DIR__ . '/lib/MightyImageHelper.php';
  
    // get url of cdn & site
    if (!empty($mightyimageId)) {
      $cdn_url = "https://mightyimage.io/" . $mightyimageId;
    }
  
    if (!empty($mightyimage_options["cname"])) {
      $cdn_url = $mightyimage_options["cname"];
    }
  
    if (!empty($mightyimageUrlEndpoint)) {
      $cdn_url = $mightyimageUrlEndpoint;
    }
  
    $cdn_url = ensure_valid_url($cdn_url);
    if (empty($cdn_url)) {
      return;
    }
  
    $site_url = get_home_url();
  
    // instantiate class
    $mightyImage = new MightyImageReWriter($cdn_url, $site_url, $mightyimage_options);
    ob_start(array(&$mightyImage,
      'replace_all_links'
    ));
});

include ('lib/setting.php');

// Settings
function mightyimage_plugin_admin_links($links, $file) {
    static $mi_plugin;
    if (!$mi_plugin) {
      $mi_plugin = plugin_basename(__FILE__);
    }
    if ($file == $my_plugin) {
      $settings_link = '<a href="options-general.php?page=mightyimage-setting">Settings</a>';
      array_unshift($links, $settings_link);
    }
    return $links;
}

function ensure_valid_url($url) {

    $parsed_url = parse_url($url);
  
    $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '//';
    $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
    $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
    $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
    $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
    $pass = ($user || $pass) ? "$pass@" : '';
    $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
  
    $result = "$scheme$user$pass$host$port$path$query$fragment";
  
    if ($result) return substr($result, -1) == "/" ? $result : $result . '/';
  
    return NULL;
  }
  
  add_filter('plugin_action_links', 'mightyimage_plugin_admin_links', 10, 2);
  ?>