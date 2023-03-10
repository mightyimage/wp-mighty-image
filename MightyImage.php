<?php
/*
Plugin Name: MightyImage
Description: A WordPress plugin to automatically fetch your WordPress images via <a href="https://www.mightyimage.io" target="_blank">MightyImage</a> for optimization and fast delivery. <a href="https://mightyimage.io/blog" target="_blank">Learn more</a> from documentation.
Author: Jimmy - MightyImage
Author URI: https://mightyimage.io
Version: 1.1.0
Text Domain: mightyimage_domain
*/

// Variables
$mightyimage_options = get_option('mightyimage_settings');

if (!defined('ABSPATH')) {
  exit;
}

if (!defined('MightyImage_PluginPath')) {
    define('MightyImage_PluginPath', __DIR__);
}

if (!defined('MightyImage_PluginEntrypoint')) {
    define('MightyImage_PluginEntrypoint', __FILE__);
}
  
if (!defined(('MightyImage_Debug'))) {
    define('MightyImage_Debug', false);
}

if (!defined(('MightyImage_Validation_Url'))) {
  define('MightyImage_Validation_Url', "https://europe-west1-mightyimage-52e65.cloudfunctions.net/account");
  //define('MightyImage_Validation_Url', "https://api.mightyimage.io/v1/account");
}

add_action('template_redirect', function () {

    global $mightyimage_options;
  
    if (isset($mightyimage_options['mightyimage_id'])) {
      $mightyimageId = $mightyimage_options['mightyimage_id'];
    }
  
    if (isset($mightyimage_options['mightyimage_url_endpoint'])) {
      $mightyimageUrlEndpoint = $mightyimage_options['mightyimage_url_endpoint'];
    }
  
    //Do not activate CDN, it is not enabled
    if (!isset($mightyimage_options['enabled'])) {
      return;
    }

    if (empty($mightyimageId) && empty($mightyimageUrlEndpoint)) {
      return;
    }
  
    // load class
    require_once __DIR__ . '/lib/MightyImageReWriter.php';
    require_once __DIR__ . '/lib/MightyImageHelper.php';
  
    // get url of cdn & site
    if (!empty($mightyimageId)) {
      $cdn_url = "https://media.mightyimage.io/image/" . $mightyimageId;
    }
  
    if (!empty($mightyimageUrlEndpoint)) {
      $cdn_url = $mightyimageUrlEndpoint;
    }
  
    $cdn_url = MightyImageHelper::EnsureValidUrl($cdn_url);
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
?>