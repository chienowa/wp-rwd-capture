<?php

/* Plugin Name: wp-rwd-capture
 * Plugin URI: https://screenshot-web.com
 * Description: Just insert a schortcode for responsive web design pages.
 * Author: Nash Nakagawa
 * Author URI: https://screenshot-web.com
 * Stable tag: 1.0.1
 * Version: 1.0.1
 */
 
require_once("wp-rwd-capture-setting.class.php");

function ssweb_get_screenshot($atts, $content = null) {
	$msg = shortcode_atts(array(
		"ver" => '1',
		"url" => $content ? $content: 'http://example.com',
		"template_id" => '9',
		"width" => '',
		"height" => '1024',
		"selector" => '',
		"orientation" => 'portrait',
	), $atts, 'ssweb');

	$post_id = get_the_ID();

	// Handling control codes
	$_width = $msg['width'];

	//set image key
	$_hash_key = hash("md5", serialize($msg));
	$_meta_key = substr($_hash_key, 0, 10);

	//clear control data
	unset($msg['width']);
	unset($msg['name']);

	// if returns empty array.
	$path = get_post_meta($post_id, $_meta_key);
	if(!$path) {
		$path = _post_api($msg);
	if(strlen($path)) 
		add_post_meta($post_id, $_meta_key, $path,true);
	} else {
		$path = array_shift($path);
	}
    $option = get_option("capture_setting");
	$host = parse_url($option['endpoint'], PHP_URL_HOST) ;
	// DO HTTP POST
	return '<img width="'.$width.'" src="https://'.$host.$path. '" '.' data-failover="'.plugins_url( 'images/rwd-404.gif', __FILE__ ).'"/>';
}

function _post_api($msg){
	
	$setting = maybe_unserialize(get_option('capture_setting'));
	if(empty($setting)) {
		error_log("You need to fill out configurations for wp-capture", 0);
		return "400 Bad Request";
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
add_shortcode("ssweb", "ssweb_get_screenshot");
wp_enqueue_script( 'wp-rwd-capture', plugins_url('js/wp-rwd-capture.js',__FILE__), array(), '1.0.0', true );

if( is_admin() ) {
	$capture_settings_page = new CaptureSettingsPage();
}

