<?php get_header(); ?>
<?php
	date_default_timezone_set('America/New_York'); // Make sure we're using the currect timezone
	
	// Build a new query based on time parameters set
	
	$from 		= is_numeric($_GET['from']) ? $_GET['from'] : '';
	// Webcom requires +4 hours to match WP GMT timestamps
	$fromtime 	= date('Y-m-d H:i:s', strtotime($_GET['from'] + 40000 ));
	//$fromtime 	= date('Y-m-d H:i:s', strtotime($_GET['from']));
	
	$args = array( 
		'post_type' 	 => 'feedsubmission',
		'posts_per_page' => -1,
		'order'			 => 'DESC',
		'orderby'		 => 'post_modified',
		'post_status'	 => 'publish',
	);
	// Logged-in users should get pending and published posts
	if (is_user_logged_in() && current_user_can('edit_post')) {
		$args['post_status'] = array('publish', 'pending');
	}
	
	$loop = new WP_Query($args);
	
?>
<div class="container-fluid">
	<div class="row-fluid page-content" id="home">
		<div class="span12" id="content-col">
			<?php
				var_dump($fromtime);
				if ($from !== '') {
					while ( $loop->have_posts() ) : $loop->the_post();	
						// need to subtract 4 hours from post_modified for other servers
						// Webcom should compare an unmodified post_modified value						
						//if (date('Y-m-d H:i:s', strtotime($post->post_modified.' - 4 hours')) > $fromtime) {	
						if ($post->post_modified > $fromtime) {		
							var_dump($post->post_modified);	
							print display_feedsubmission($post);
						}
					endwhile;
				}
			?>
		</div>		
		
<?php get_footer();?>