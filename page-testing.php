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
		
			<div class="modal fade" id="archivemodal">
				<div class="modal-body">
					<p>This is an archived version of the Experience UCF website's content for Homecoming 2012.  Content in this archive was published from 10/25/2012 - 11/06/2012.  Please note that this is not an exact replica of the original website; dependencies on WordPress and dynamically-loaded content have been removed, as well as some stylistic elements (animated tile readjustment.)</p>
					<p>Images will load as you scroll down.  Please be patient; image loading may take extra time due to the large amount of content being processed.</p>
					<p>Due to the extensive amount of content to display, this page may not perform optimally on older machines or browsers.</p>
				</div>
				<div class="modal-footer">
					<a href="#" class="btn" data-dismiss="modal">Close</a>
				</div>
			</div>
			
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
				isAnimated: false,
			});
		});
		
		// LazyLoad
		$('.box-inner img').lazyload({
			event: 'scrollstop',
			effect : 'fadeIn',
			load : function() {
                setTimeout(function() {
					$container.masonry('reload');
				}, 100);
            }
		});
		
		// Call modal
		$('#archivemodal').modal();
	});
	</script>
		
<?php get_footer();?>