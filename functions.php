<?php
require_once('functions/base.php');   			# Base theme functions
require_once('custom-taxonomies.php');  		# Where per theme taxonomies are defined
require_once('custom-post-types.php');  		# Where per theme post types are defined
require_once('functions/admin.php');  			# Admin/login functions
require_once('functions/config.php');			# Where per theme settings are registered
require_once('shortcodes.php');         		# Per theme shortcodes

//Add theme-specific functions here.

/**
 * Format Hashtag list for use in a query string
 *
 * @return string
 **/
function build_hashtag_query($tagname='tags') {
	$theme_options = get_option(THEME_OPTIONS_NAME);
	if ($theme_options['hashtags']) {
		$data = array($tagname => $theme_options['hashtags']);
		return http_build_query($data);
	}
}

/**
 * Retrieve feeds based on selected services in Theme Options.
 * 
 * @return array
 **/
function fetch_flickr() {
	$theme_options = get_option(THEME_OPTIONS_NAME);
	if ( (in_array('flickr', $theme_options['enabled_services'])) && ($theme_options['hashtags']) ) {
		$max 	= is_numeric($theme_options['flickr_max_results']) ? $theme_options['hashtags'] : 20; // fallback check for valid max #
		$feed 	= fetch_feed('http://api.flickr.com/services/feeds/photos_public.gne?format=rss2&tagmode=ANY&'.build_hashtag_query('tags'));
		
		if (!is_wp_error($feed)) { 
			// Figure out how many total items there are, but limit it to the max number set. 
			$total = $feed->get_item_quantity($max); 
			// Build an array of all the items, starting with element 0 (first element).
			$items = $feed->get_items(0, $total);
			
			return $items;
		}
		else { return 'Error in generating feed object'; }
	}
}
function fetch_instagram() {
	$theme_options = get_option(THEME_OPTIONS_NAME);
	if ( (in_array('instagram', $theme_options['enabled_services'])) && ($theme_options['hashtags']) ) {
		// http://instagram.com/tags/ucf/feed/recent.rss
	}
}
function fetch_twitter() {
	$theme_options = get_option(THEME_OPTIONS_NAME);
	if ( (in_array('twitter', $theme_options['enabled_services'])) && ($theme_options['hashtags']) ) {
		
	}
}



?>