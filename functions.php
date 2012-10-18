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
 * Retrieve feeds based on hashtags specified in Theme Options.
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
		else { return 'Error in generating Flickr feed object'; }
	}
}
function fetch_instagram() {
	$theme_options = get_option(THEME_OPTIONS_NAME);
	if ( (in_array('instagram', $theme_options['enabled_services'])) && ($theme_options['hashtags']) ) {
		$max 	= is_numeric($theme_options['instagram_max_results']) ? $theme_options['hashtags'] : 20;
		$feed 	= fetch_feed('http://instagram.com/tags/'.preg_replace('/[^A-Za-z0-9]/', "", $theme_options['hashtags']).'/feed/recent.rss');
		
		// TODO: Fix feed url to accept more than one tag at a time...
		
		if (!is_wp_error($feed)) { 
			// Figure out how many total items there are, but limit it to the max number set. 
			$total = $feed->get_item_quantity($max); 
			// Build an array of all the items, starting with element 0 (first element).
			$items = $feed->get_items(0, $total);
			
			return $items;
		}
		else { return 'Error in generating Instagram feed object'; }
		
	}
}
function fetch_twitter() {
	$theme_options = get_option(THEME_OPTIONS_NAME);
	if ( (in_array('twitter', $theme_options['enabled_services'])) && ($theme_options['hashtags']) ) {
		$max 	= is_numeric($theme_options['twitter_max_results']) ? $theme_options['hashtags'] : 20;
		// NOTE: RSS will be completely deprecated by March 2013; this is only a temporary solution!
		$feed 	= fetch_feed('http://search.twitter.com/search.rss?'.build_hashtag_query('q'));
		
		if (!is_wp_error($feed)) { 
			// Figure out how many total items there are, but limit it to the max number set. 
			$total = $feed->get_item_quantity($max); 
			// Build an array of all the items, starting with element 0 (first element).
			$items = $feed->get_items(0, $total);
			
			return $items;
		}
		else { return 'Error in generating Twitter feed object'; }
	}
}


/**
 * Master feed builder
 * 
 * Uses fetch functions (fetch_flickr(), fetch_instagram(), fetch_twitter())
 * to get all available feeds and return a single array with only the relevant 
 * information needed to create new posts.
 *
 * @return array
 **/
function get_master_feed() {
	$theme_options = get_option(THEME_OPTIONS_NAME);
	
	// Fetch initial feeds; check if the services are activated first
	if ( (in_array('flickr', $theme_options['enabled_services'])) ) {
		$feed_flickr = fetch_flickr();
	}
	if ( (in_array('instagram', $theme_options['enabled_services'])) ) {
		$feed_instagram = fetch_instagram();
	}
	if ( (in_array('twitter', $theme_options['enabled_services'])) ) {
		$feed_twitter = fetch_twitter();
	}
	
	$services = array(
		'flickr' 		=> $feed_flickr, 
		'instagram' 	=> $feed_instagram, 
		'twitter' 		=> $feed_twitter,
	);
	$master_array = array();
	
	// If the feeds aren't empty, get the content we need
	foreach ($services as $key => $feed) {
		if (!empty($feed)) {
			foreach ($feed as $item) {
				// Get the author
				$enclosure = $item->get_enclosure(0);
				$credits = $enclosure->get_credits();
				if ($credits) {
					foreach ($credits as $credit){ 
						$author = $credit->get_name();
					}
				}
				// Set up an array for each item
				$item_array = array(
					'feedsubmission_service' => $key,
					'feedsubmission_author' => $author,
					'feedsubmission_original_pub_time' => $item->get_date(),
					'title' => $item->get_title(),
					'post_content' => $item->get_content(),
				);
				// Add the item array to the master array
				$master_array[] = $item_array;
			}
		}
	}
	
	function sort_by_date($a, $b) {
		return strtotime($a['feedsubmission_original_pub_time']) - strtotime($b['feedsubmission_original_pub_time']);
	}
	usort($master_array, 'sort_by_date');

	return $master_array;
}


var_dump(get_master_feed());

?>