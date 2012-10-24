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
		//'orderby'		 => 'meta_value_num',
		'orderby'		 => 'post_modified',
		//'meta_key'		 => 'feedsubmission_original_pub_time',
		'post_status'	 => 'publish',
	);
	// Logged-in users should get pending and published posts
	if (is_user_logged_in() && current_user_can('edit_post')) {
		$args['post_status'] = array('publish', 'pending');
	}
	
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
<div class="container-fluid">
	<div class="row-fluid page-content" id="home">
		<div class="span12" id="content-col">
			<?php
				if ($from !== '') {
					while ( $loop->have_posts() ) : $loop->the_post();							
						if ($post->post_modified > $fromtime) {				
							print display_feedsubmission($post);
						}
					endwhile;
				}
			?>
		</div>
		
<?php //remove_filter( 'posts_where' , 'filter_where' ); ?>				
		
<?php get_footer();?>