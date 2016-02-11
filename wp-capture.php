<?php

/* Plugin Name: WP Capture
 * Plugin URI: 
 * Description: 
 * Author: Nash Nakagawa
 * Author URI: https://screenshot-web.com
 * Stable tag: 1.0
 * Version: 1.0
 */
 
require_once("wp-capture-setting.class.php");

function getScreenshot($atts, $content = null) {
    $msg = shortcode_atts(array(  
	    "url" => $content ? $content: 'http://example.com',
	    "template_id" => '9',
	    "height" => '1024',
	    "selector" => '',
	    "orientation" => 'portrait'
    ), $atts, 'ssweb');
    $post_id = get_the_ID();
    $_hash_key = hash("md5", serialize($msg));
    $_meta_key = substr($_hash_key, 0, 10);
    // if returns empty array.
    $path = get_post_meta($post_id, $_meta_key);
    if(!$path) {
    	$path = _post_api($msg);
	if(strlen($path)) 
	    add_post_meta($post_id, $_meta_key, $path,true);
    } else {
	$path = array_shift($path);
    }
    // DO HTTP POST
    return '<img src="https://screenshot-web.local'.$path. '" />';
}

function _post_api($msg){
    
    $setting = maybe_unserialize(get_option('capture_setting'));
    if(empty($setting)) {
	error_log("You need to fill out configurations for wp-capture", 0);
	return "/images/404.jpg";
    }
    $content = json_encode($msg);
    $content_length = strlen($content);
    $options = array(
	'http' => array(
        		'method' => 'POST',
        		'header' => "Content-type: application/json\r\n"
                  	. "Content-Length: $content_length",
        		'content' => $content),
	"ssl"  => array(
        		"verify_peer"=>false,
        		"verify_peer_name"=>false)
    );

    $response = file_get_contents($setting['endpoint'].$setting['apikey'], false, stream_context_create($options));
    return $response;

}
add_shortcode("ssweb", "getScreenshot");

if( is_admin() ) {
    $capture_settings_page = new CaptureSettingsPage();
}

?>
