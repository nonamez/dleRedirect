<?php
/*
Plugin Name: Redirect for "DLE to WP" plugin
Description: This plugin provides the ability to do a safe 301 redirect to new posts, users, tags transferred from "DataLife Engine" to "Wordpress". 
Version: 0.1 (Beta)
Author: Kiril Calkin 
Email: nonamez123[doggyStyle]gmail{dot}com
Author URI: http://nonamez.name
License: Beerware
*/

function customRedirectAfterDLETransfer()
{
	if (is_404()) {
		$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$new_url = FALSE;

		preg_match_all('/.*?\/(?:\d+-)?([^\/]*)\.html/', $url, $matches);

		if (isset($matches[1][0])) {
			$post_names_file = dirname(__FILE__) . '/posts_names.txt';
			$post_names = FALSE;

			if (file_exists($post_names_file))
				$post_names = @json_decode(file_get_contents($post_names_file), TRUE);

			if (is_array($post_names) && array_key_exists($matches[1][0], $post_names))
				$new_url = get_permalink($post_names[$matches[1][0]]);
		} elseif (strpos($url, 'user') !== FALSE) {
			preg_match('/\/user\/(.*)\//', $url, $matches);

			if (isset($matches[1]) && is_int(username_exists($matches[1])))
				$new_url = get_author_posts_url(username_exists($matches[1]));
		} elseif (strpos($url, 'tags') !== FALSE) {
			preg_match('/\/tags\/(.*)\//', $url, $matches);

			if (isset($matches[1]) && is_numeric(term_exists($matches[1])))
				$new_url = get_tag_link(term_exists($matches[1]));
		} elseif (in_array(strtolower(pathinfo($url, PATHINFO_EXTENSION)), array('jpg', 'jpeg', 'gif', 'png', 'bmp'))) {
			$query_args = array('s' => pathinfo($url, PATHINFO_FILENAME) );
			$get_posts = new WP_Query($query_args);

			if (isset($get_posts->posts[0]->ID))
				$new_url = get_permalink($get_posts->posts[0]->ID);
		}
		
		if (is_string($new_url)) {
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: " . $new_url);
			
			exit();
		}
	}
}

add_action('template_redirect', 'customRedirectAfterDLETransfer');
?>