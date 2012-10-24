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
		'post_status'	=> 'Post Status',
	);
	return $columns;
}
add_action('manage_edit-feedsubmission_columns', 'edit_feedsubmission_columns');
function manage_feedsubmission_columns( $column, $post_id ) {
	global $post;
	switch ( $column ) {
		case 'image':
			if (get_post_meta( $post->ID, 'feedsubmission_image', true )) {
				print '<a href="'.get_post_meta( $post->ID, 'feedsubmission_image', true ).'"><img src="'.get_post_meta( $post->ID, 'feedsubmission_image', true ).'" style="max-height: 100px;" /></a>';
				break;
			}
			elseif (get_the_post_thumbnail($post->ID)) {
				print get_the_post_thumbnail($post->ID, array(100,100));
				break;
			}
			else { print '(No image)'; }
			break;
		case 'service':
			print ucfirst(get_post_meta( $post->ID, 'feedsubmission_service', true ));
			break;
		case 'orig_pub_time':
			switch (get_post_meta($post->ID, 'feedsubmission_service', true)) {
				case 'selfpost':
					print date('Y/m/d @ g:i a', strtotime($post->post_date.' - 4 hours'));
					break;
				default:
					print date('Y/m/d @ g:i a', strtotime(get_post_meta( $post->ID, 'feedsubmission_original_pub_time', true )));
					break;
			}
			break;
		case 'post_status':
			switch ($post->post_status) {
				case 'publish':
					print '<span style="font-weight:bold; color:green;">Published</span><br/>(Last Modified '.date('Y/m/d @ g:i a', strtotime($post->post_modified.' -4 hours')).')';
					break;
				default:
					print ucfirst($post->post_status);
					break;
			}
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
 * Function to reset the cache lifetime used by fetch_feed to cache feed data.
 * Change the return value to change the number of seconds before a cache reset.
 *
 **/
function cache_reset( $seconds ) {
  	return 240;
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
		
		add_filter( 'wp_feed_cache_transient_lifetime' , 'cache_reset' );		
		$feed 	= fetch_feed('http://api.flickr.com/services/feeds/photos_public.gne?format=rss2&tagmode=ANY&'.build_hashtag_query('tags'));
		remove_filter( 'wp_feed_cache_transient_lifetime' , 'cache_reset' );
		
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
		$max 		= is_numeric($theme_options['instagram_max_results']) ? $theme_options['hashtags'] : 20;
		$hashtags 	= explode(',', $theme_options['hashtags']);
		$merged		= array();
		
		foreach ($hashtags as $hashtag) {
			$merged[] .= 'http://instagram.com/tags/'.preg_replace('/[^A-Za-z0-9]/', "", $hashtag).'/feed/recent.rss';
		}
		
		add_filter( 'wp_feed_cache_transient_lifetime' , 'cache_reset' );
		$feed = fetch_feed($merged);
		remove_filter( 'wp_feed_cache_transient_lifetime' , 'cache_reset' );
		
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
		$hashtags 	= explode(',', $theme_options['hashtags']);
		$merged		= array();
		
		foreach ($hashtags as $hashtag) {
			// NOTE: Twitter RSS will be completely deprecated by March 2013; this is only a temporary solution!
			$merged[] .= 'http://search.twitter.com/search.rss?q='.urlencode($hashtag);
		}
		
		add_filter( 'wp_feed_cache_transient_lifetime' , 'cache_reset' );
		$feed 	= fetch_feed($merged);
		remove_filter( 'wp_feed_cache_transient_lifetime' , 'cache_reset' );
		
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
				$author = '';
				if ($key == 'twitter') {
					$author = $item->get_author();
					$author = explode('@', $author->get_email());
					$author = $author[0];
				}
				else {
					$enclosure = $item->get_enclosure(0);
					$credits = $enclosure->get_credits();
					if ($credits) {
						foreach ($credits as $credit){ 
							$author = $credit->get_name();
						}
					}
				}
				// Content
				$content = $item->get_content();
				// Get an image, if it exists within the post content
				$image = '';
				if (preg_match('/(<img[^>]+>)/i', $content, $matches)) {
					$image = explode('src="', $matches[0]);
					$image = explode('"', $image[1]);
					$image = $image[0];
				}
				// Original Pub Time
				$pub_time = date('YmdHis', strtotime($item->get_date()));
				// Instagram time stamps are totally wrong, so let's fix them
				if ($key == 'instagram') {
					$pub_time = date('YmdHis', strtotime($pub_time.' -11 hours'));
				}
				// Twitter time stamps are also wrong
				if ($key == 'twitter') {
					$pub_time = date('YmdHis', strtotime($pub_time.' -4 hours'));
				}
				// Set up an array for each item
				$item_array = array(
					'feedsubmission_service' 			=> $key,
					'feedsubmission_author' 			=> $author,
					'feedsubmission_original_pub_time' 	=> $pub_time,
					'feedsubmission_image' 				=> $image,
					'title' 							=> $item->get_title(),
					'post_content' 						=> $content,
					'feedsubmission_original_link'		=> $item->get_link(0),
				);
				// Add the item array to the master array
				$master_array[] = $item_array;
			}
		}
	}

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
			$clean_title = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)\#@\'\;\:\/%&-]/s', '', $item['title']);
			
			// If a Feed Submission already exists with the same contents, don't re-submit it
			$already_submitted 	= false;			
			$args = array(
				'post_type'		=> 'feedsubmission',
				'numberposts'	=> 1,
				'meta_key'		=> 'feedsubmission_original_link',
				'meta_value'	=> $item['feedsubmission_original_link'],
				'post_status'	=> array('publish', 'pending', 'trash'),
			);
			$similar = get_posts($args);
			$similar = $similar[0];
			
			if ($similar !== NULL) {
				if ( 
					get_post_meta($similar->ID, 'feedsubmission_author', TRUE) == $item['feedsubmission_author'] &&
					get_post_meta($similar->ID, 'feedsubmission_original_pub_time', TRUE) == $item['feedsubmission_original_pub_time']
				) {
					$already_submitted = true;
				}
			}		
			
			if ($already_submitted == false && $item['feedsubmission_service'] !== false) {
				// Setup primary post data
				$post_data = array(
					'post_type'		=> 'feedsubmission',
					'post_title'    => $clean_title,
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
				add_post_meta($post_id, 'feedsubmission_original_link', $item['feedsubmission_original_link']);
					
				$count++;
			}
			
		}
		return $count.' new Feed Submission pending posts created at '.date('Y-m-d H:i:s');
	}
}



/**
 * Display FeedSubmission content on the front-end.
 * Intended for use within The Loop.
 *
 **/
function display_feedsubmission($post) {
	$author   = get_post_meta($post->ID, 'feedsubmission_author', TRUE) ? get_post_meta($post->ID, 'feedsubmission_author', TRUE) : 'Anonymous';
	$pub_date = date('F j Y, g:i a', strtotime(get_post_meta( $post->ID, 'feedsubmission_original_pub_time', true )));
	$service  = get_post_meta($post->ID, 'feedsubmission_service', TRUE);
					
	switch ($service) {
		case 'flickr':
			// Search the first part of the post content for the "user has uploaded a photo:" line
			$content_expl = explode('posted a photo:', get_the_content());
			$user_link = explode('<p>', $content_expl[0]);
			$author = $user_link[1];
			break;
		case 'instagram':
			// Instagram has no online profile views
			break;
		case 'twitter':
			$author = '<a href="http://www.twitter.com/'.$author.'">@'.$author.'</a>';
			break;
		case 'selfpost':
			// If this is a self post, force UCF as the author and the last modified date as the pub date
			$author = 'UCF';
			$pub_date = date('F j Y, g:i a', strtotime($post->post_modified.' - 4 hours'));
		default:
			break;
	}
	
	ob_start();
	?>
	
	<div class="box" id="<?=$service?>-<?=$post->ID?>">
		<div class="box-inner">
			<h3><?=the_title();?></h3>
			<?php
				// Self posts should display a featured image, if one is available
				if ($service == 'selfpost') {
					if (get_post_meta($post->ID, 'feedsubmission_links_to', true)) {
						print '<a target="_blank" href="'.get_post_meta($post->ID, 'feedsubmission_links_to', true).'">';
					}
					if (get_the_post_thumbnail($post->ID)) {
						print get_the_post_thumbnail($post->ID);
					}
					if (get_post_meta($post->ID, 'feedsubmission_links_to', true)) {
						print '</a>';
					}
				}
				// Twitter submission titles and content are the same, so only display it once
				if ($service !== 'twitter') {
					the_content();
				}
			?>
			<p class="post-info <?=$service?>">
				<small>via <?=$author?> <span class="<?=$service?>">on <?=ucfirst($service)?></span><br/>
				<span class="pubtime">at <?=$pub_date?></span></small>
			</p>
			<?php
				// If the user is logged in and has editing capabilities, display editing options 
				if (is_user_logged_in() && current_user_can('edit_post')) { ?>
				<div class="edit-options">
					<p class="post-status"><strong>Post status:</strong>
						<?php 
						switch ($post->post_status) {
							case 'publish':
								print '<span class="label label-success">Published</span>';
								break;
							default:
								print '<span class="label">'.ucfirst($post->post_status).'</span>';
								break;
						}
						?>
					</p>
					<div class="btn-group">
						<?php if ($post->post_status !== 'publish') { ?>
						<button class="btn btn-small edit-approve" value="<?=$post->ID?>"
							data-approve-url="<?=site_url()?>/admin-update-status/?id=<?=$post->ID?>&status=p">
							<i class="icon-ok"></i> Approve
						</button>
						<?php } ?>
						<button class="btn btn-small edit-delete" value="<?=$post->ID?>" 
							data-trash-url="<?=site_url()?>/admin-update-status/?id=<?=$post->ID?>&status=t">
							<i class="icon-trash"></i> Trash
						</button>
					</div>
					<a class="btn btn-small editlink" target="_blank" href="<?=get_edit_post_link($post->ID)?>"><i class="icon-edit"></i> Edit Post</a>
				</div>
			<?php } ?>
		</div>
	</div>
	
	<?php
	return ob_get_clean();
}

?>