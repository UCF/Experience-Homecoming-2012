<?php get_header(); ?>
	<div class="row-fluid page-content" id="home">
		<div class="span3" id="sidebar">
			<p>This is a basic demonstration of Masonry.js and InfiniteScroll.js with a generic post loop.</p>
			<?=get_sidebar();?>
			<p id="page-nav"><?php /*next_posts_link('&laquo; Older Entries', $loop->max_num_pages)*/ print "<a href='".site_url()."'>Next</a>"; ?></p>
		</div>
		<div class="span9" id="content-col">
				<? if(!is_front_page())	{ ?>
						<h1><?php the_title();?></h1>
				<? } ?>
				
				<?php
					$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
					$args = array( 
						'post_type' 	 => 'feedsubmission', 
						'posts_per_page' => 4,
						'paged'			 => $paged,
					);
					$loop = new WP_Query($args);
					while ( $loop->have_posts() ) : $loop->the_post();
					?>
						<div class="span3 box">
							<div class="box-inner">
								<?=the_title();?>
								<?=the_content();?>
							</div>
						</div>
					<?php
					endwhile;
				?>
		</div>
	</div>
<?php if (is_front_page()) { ?>
	<script>
		$(function(){
			var $container = $('#content-col');
		
			$container.imagesLoaded(function(){
			  $container.masonry({
				itemSelector: '.box',
				columnWidth: 100,
				isAnimated: true
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
			  }
			);
	  });
	</script>
<?php } ?>		
<?php get_footer();?>