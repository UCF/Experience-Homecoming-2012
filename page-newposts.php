<?php get_header(); ?>
<?php
	date_default_timezone_set('America/New_York'); // Make sure we're using the currect timezone
	
	// Build a new query based on time parameters set
	
	$from 		= is_numeric($_GET['from']) ? $_GET['from'] : '';
	$fromtime 	= date('Y-m-d H:i:s', strtotime($_GET['from'] + 40000 )); // Add 4 hrs to match WP GMT timestamps
	
	$args = array( 
		'post_type' 	 => 'feedsubmission',
		'posts_per_page' => -1,
		'order'			 => 'DESC',
		'orderby'		 => 'meta_value_num',
		'meta_key'		 => 'feedsubmission_original_pub_time',
		'post_status'	 => 'publish',
	);
	
	// Retrieve posts within the given time span.
	// Note that we must check by post_modified, NOT post_date, as these must be approved
	// (modified) before being published-- the mod date will be the most accurate time
	
	/*
	function filter_where($where = '') {
		$where .= " AND post_modified > '".$fromtime."'";
		return $where;
	}
	add_filter('posts_where', 'filter_where');
	*/
	
	$loop = new WP_Query($args);
	
?>
			<?php
				while ( $loop->have_posts() ) : $loop->the_post();							
				
				if ($post->post_modified > $fromtime) {				
				
					$author   = get_post_meta($post->ID, 'feedsubmission_author', TRUE);
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
						default:
							break;
					}
				?>
					<div class="box" id="<?=$service?>-<?=$post->ID?>">
						<div class="box-inner">
							<h3><?=the_title();?></h3>
							<?php
								// Twitter submission titles and content are the same, so only display it once
								if ($service !== 'twitter') {
									the_content();
								}
							?>
							<p class="post-info <?=$service?>">
								<small>via <?=$author?> <span class="<?=$service?>">on <?=ucfirst($service)?></span><br/>
								<span class="pubtime">at <?=$pub_date?></span></small>
							</p>
						</div>
					</div>
				<?php
				}
				endwhile;
			?>

<?php //remove_filter( 'posts_where' , 'filter_where' ); ?>				
		
<?php get_footer();?>