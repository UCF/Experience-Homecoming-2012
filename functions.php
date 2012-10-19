<?php
require_once('functions/base.php');   			# Base theme functions
require_once('custom-taxonomies.php');  		# Where per theme taxonomies are defined
require_once('custom-post-types.php');  		# Where per theme post types are defined
require_once('functions/admin.php');  			# Admin/login functions
require_once('functions/config.php');			# Where per theme settings are registered
require_once('shortcodes.php');         		# Per theme shortcodes

//Add theme-specific functions here.


/**
 * Custom columns for 'FeedSubmission' post type
 **/
function edit_feedsubmission_columns() {
	$columns = array(
		'cb'        	=> '<input type="checkbox" />',
		'title'     	=> 'Title',
		'image' 		=> 'Image',
		'service'		=> 'Service',
		'orig_pub_time' => 'Original Submission Date',
	);
	return $columns;
}
add_action('manage_edit-feedsubmission_columns', 'edit_feedsubmission_columns');
function manage_feedsubmission_columns( $column, $post_id ) {
	global $post;
	switch ( $column ) {
		case 'image':
			if (get_post_meta( $post->ID, 'feedsubmission_image', true )) {
				print '<img src="'.get_post_meta( $post->ID, 'feedsubmission_image', true ).'" style="max-height: 100px;" />';
			}
			else { print '(No image)'; }
			break;
		case 'service':
			print get_post_meta( $post->ID, 'feedsubmission_service', true );
			break;
		case 'orig_pub_time':
			print get_post_meta( $post->ID, 'feedsubmission_original_pub_time', true );
			break;
		default:
			break;
	}
}
add_action('manage_feedsubmission_posts_custom_column', 'manage_feedsubmission_columns', 10, 2);
function sortable_feedsubmission_columns( $columns ) {
	$columns['orig_pub_time'] = 'orig_pub_time';
	return $columns;
}
add_action('manage_edit-feedsubmission_sortable_columns', 'sortable_feedsubmission_columns');


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
				// Content
				$content = $item->get_content();
				$image = '';
				// Get an image, if it exists within the post content
				if (preg_match('/(<img[^>]+>)/i', $content, $matches)) {
					$image = explode('src="', $matches[0]);
					$image = explode('"', $image[1]);
					$image = $image[0];
				}
				// Set up an array for each item
				$item_array = array(
					'feedsubmission_service' 			=> $key,
					'feedsubmission_author' 			=> $author,
					'feedsubmission_original_pub_time' 	=> $item->get_date(),
					'feedsubmission_image' 				=> $image,
					'title' 							=> $item->get_title(),
					'post_content' 						=> $content,
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


/**
 * Creates new FeedSubmission post drafts given an array of data.
 * Intended to be called in a separate file, run by a cron job with
 * a set time interval.
 * 
 **/
function create_feedsubmissions($feed=null) {
	if (!$feed) { return 'No feed specified.'; }
	if (empty($feed)) { return 'Feed returned no content.'; }
	
	elseif (!empty($feed)) {
		$count = 0;
		foreach ($feed as $item) {
			// If a Feed Submission already exists with the same contents, don't re-submit it
			$already_submitted 	= false;
			$similar_post 		= get_page_by_title($item['title'], 'OBJECT', 'feedsubmission');
			if ( // If a similarly-titled post exists, and its author, original publish date, and service match:
				($similar_post) &&
				(get_post_meta($similar_post->ID, 'feedsubmission_author', TRUE) == $item['feedsubmission_author']) &&
				(get_post_meta($similar_post->ID, 'feedsubmission_original_pub_time', TRUE) == $item['feedsubmission_original_pub_time']) &&
				(get_post_meta($similar_post->ID, 'feedsubmission_service', TRUE) == $item['feedsubmission_service'])
				) {
					$already_submitted = true;
			}
			
			if ($already_submitted == false) {
				// Setup primary post data
				$post_data = array(
					'post_type'		=> 'feedsubmission',
					'post_title'    => $item['title'],
					'post_content'  => $item['post_content'],
					'post_status'   => 'pending',
					'post_date'		=> date('Y-m-d H:i:s'),
				);			
				
				// Insert the post into the database and return its ID
				$post_id = wp_insert_post( $post_data, $wp_error );
				
				// Add meta data...get_post_custom always returns empty so we have to do this manually for each field :(
				add_post_meta($post_id, 'feedsubmission_service', $item['feedsubmission_service']);
				add_post_meta($post_id, 'feedsubmission_author' , $item['feedsubmission_author' ]);
				add_post_meta($post_id, 'feedsubmission_original_pub_time', $item['feedsubmission_original_pub_time']);
				add_post_meta($post_id, 'feedsubmission_image', $item['feedsubmission_image']);
					
				$count++;
			}
		}
		return $count.' new Feed Submission pending posts created at '.date('Y-m-d H:i:s');
	}
}

?>