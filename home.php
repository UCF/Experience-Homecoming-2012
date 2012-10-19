<?php get_header(); ?>
<?php
	// We're manually handing GET params for pagination because WP pagination sucks
	$paged = is_numeric($_GET['pg']) ? $_GET['pg'] : 1;
	$args = array( 
		'post_type' 	 => 'feedsubmission',
		'paged'			 => $paged,
			
		'post_status'				 => 'pending' // Delete me after testing!
			
	);
	$loop = new WP_Query($args);
	
?>
	<div class="row-fluid page-content" id="home">
		<div class="span3" id="sidebar">
			<p>This is a basic demonstration of Masonry.js and InfiniteScroll.js with a custom post type loop.</p>
			<?=get_sidebar();?>
			<p id="page-nav"><?php /*print next_posts_link('&laquo; Older Entries', $loop->max_num_pages)*/ print "<a href='".site_url()."/?pg=".($paged+1)."'>Next</a>"; ?></p>
		</div>
		<div class="span9" id="content-col">
			<?php
				while ( $loop->have_posts() ) : $loop->the_post();
						
					$author   = get_post_meta($post->ID, 'feedsubmission_author', TRUE);
					$pub_date = get_post_meta($post->ID, 'feedsubmission_original_pub_time', TRUE);
					$service  = get_post_meta($post->ID, 'feedsubmission_service', TRUE);
				?>
					<div class="box">
						<div class="box-inner">
							<h3><?=the_title();?></h3>
							<?=the_content();?>
							<small><?=$author?> <span class="pubtime">at <?=$pub_date?></span></small>
							<div class="<?=$service?>-icon"><?=$service?></div>
						</div>
					</div>
				<?php
				endwhile;
			?>
				
		</div>
	</div>
	
	<script>
		$(function(){
			var $container = $('#content-col');
		
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
		
		$container.infinitescroll({
			navSelector  : '#page-nav',    // selector for the paged navigation 
			nextSelector : '#page-nav a',  // selector for the NEXT link (to page 2)
			itemSelector : '.box',     // selector for all items you'll retrieve
			loading: {
				finishedMsg: 'No more pages to load.',
				img: 'http://i.imgur.com/6RMhx.gif'
			}
		},
		// trigger Masonry as a callback
		function( newElements ) {
			// hide new items while they are loading
			var $newElems = $( newElements ).css({ opacity: 0 });
			// ensure that images load before adding to masonry layout
			$newElems.imagesLoaded(function(){
				// show elems now they're ready
				$newElems.animate({ opacity: 1 });
				$container.masonry( 'appended', $newElems, true ); 
			});
			
			// reset the pagination link
			var pglink 		= $('#page-nav a').attr('href'),
				linksplit 	= pglink.split('?pg='),
				pgurl 		= linksplit[0]; // the beginning of the url
				pgval  		= parseInt(linksplit[1]) + 1; // the actual incremented value
			
			$('#page-nav a').attr('href', pgurl + '?pg=' + pgval);
		});
	});
	</script>
		
<?php get_footer();?>