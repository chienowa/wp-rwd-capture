<?php
/* Plugin Name: WP Capture
 * Plugin URI: 
 * Description: 
 * Author: Nash Nakagawa
 * Author URI: https://screenshot-web.com
 * Stable tag: 1.0
 * Version: 1.0
 */
 
define("API_ENDPOINT_URL","https://screenshot-web.local/api/capture/admin9eca4ad85077229f3437f622711da6a0");

function getScreenshot($atts, $content = null) {
    $msg = shortcode_atts(array(  
            "template" => 'PC_CHROME',
	    "template_id" => '9',
	    "height" => '1024',
	    "orientation" => 'portrait',
	    "url" => $content ? $content: 'http://example.com'
    ), $atts, 'ssweb');
    
    $path = _post_api($msg);
    // DO HTTP POST

    return '<img src="https://screenshot-web.local'.$path. '" />';
}

function _post_api($msg){
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

    $response = file_get_contents(API_ENDPOINT_URL, false, stream_context_create($options));
    return $response;

}
add_shortcode("ssweb", "getScreenshot");
?>
