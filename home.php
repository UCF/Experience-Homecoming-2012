<?php get_header(); ?>
<?php
	// We're manually handing GET params for pagination because WP pagination sucks
	$paged = is_numeric($_GET['pg']) ? $_GET['pg'] : 1;
	$args = array( 
		'post_type' 	 => 'feedsubmission',
		'paged'			 => $paged,
		'order'			 => 'DESC',
		'orderby'		 => 'meta_value_num',
		'meta_key'		 => 'feedsubmission_original_pub_time',
			
		'post_status'				 => 'pending' // Delete me after testing!
			
	);
	$loop = new WP_Query($args);
	
?>
	<div class="row-fluid page-content" id="home">
		<div class="span3" id="sidebar">
			<p>This is a basic demonstration of Masonry.js and InfiniteScroll.js with a custom post type loop.</p>
			<?=get_sidebar();?>
			<p id="page-nav"><?php print "<a href='".site_url()."/?pg=".($paged+1)."'>Next</a>"; ?></p>
		</div>
		<div class="span9" id="content-col">
			<?php
				while ( $loop->have_posts() ) : $loop->the_post();
						
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
					<div class="box">
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
				finishedMsg: 'No more posts to load.',
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