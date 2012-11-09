<?php get_header(); ?>
<?php
	// This template is being used for the sole reason of generating a page suitable for archiving.
	
	date_default_timezone_set('America/New_York'); // Make sure we're using the currect timezone
	$theme_options 			= get_option(THEME_OPTIONS_NAME);

	$args = array( 
		'post_type' 	 => 'feedsubmission',
		'posts_per_page' => -1,
		'order'			 => 'DESC',
		'orderby'		 => 'post_modified',
		'post_status'	 => 'publish',
	);
	$loop = new WP_Query($args);
	
?>
<div class="container-fluid">
	<div class="row-fluid page-content" id="testing">
		<div class="span12" id="content-col">
			<?php
				while ( $loop->have_posts() ) : $loop->the_post();
					print display_feedsubmission($post);
				endwhile;
			?>	
		</div>
		
	</div>
	
	<script type="text/javascript">
		// Masonry Init
		var $container = $('#content-col');
		
		$(function(){
			$container.imagesLoaded(function(){
			$container.masonry({
				itemSelector: '.box',
				/*columnWidth: function( containerWidth ) {
					return containerWidth / 4;
				},*/
				columnWidth: 0,
				isAnimated: true,
			});
		});
		
		// LazyLoad
		$('.box-inner img').lazyload({
			event: 'scrollstop',
			effect : 'fadeIn',
			load : function()
            {
                $container.masonry('reload');
            }
		});
	});
	</script>
		
<?php get_footer();?>