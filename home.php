<?php get_header(); ?>
<?php
	date_default_timezone_set('America/New_York'); // Make sure we're using the currect timezone
	$theme_options 			= get_option(THEME_OPTIONS_NAME);
	$autorefresh_on 		= $theme_options['autorefresh_on'] ? $theme_options['autorefresh_on'] : 1;
	$autorefresh_interval 	= $theme_options['autorefresh_interval'] ? $theme_options['autorefresh_interval'] : 2;

	// We're manually handing GET params for pagination because WP pagination sucks
	$paged = is_numeric($_GET['pg']) ? $_GET['pg'] : 1;
	$args = array( 
		'post_type' 	 => 'feedsubmission',
		'paged'			 => $paged,
		'order'			 => 'DESC',
		//'orderby'		 => 'meta_value_num',
		'orderby'		 => 'post_modified',
		//'meta_key'		 => 'feedsubmission_original_pub_time',
		'post_status'	 => 'publish',
	);
	$loop = new WP_Query($args);
	
?>
	<div class="row-fluid page-content" id="home">
		<div class="span3" id="sidebar">
			<p>This is a basic demonstration of Masonry.js and InfiniteScroll.js with a custom post type loop.</p>
			<?=get_sidebar();?>
			<p id="page-nav"><?php print "<a href='".site_url()."/?pg=".($paged+1)."'>Next</a>"; ?></p>
			<p id="new-posts"><?php print "<a href='".site_url()."/newposts/?from=".date('YmdHis')."'>Get New Posts</a>"; ?></p>
		</div>
		<div class="span9" id="content-col">
			<?php
				while ( $loop->have_posts() ) : $loop->the_post();
					print display_feedsubmission($post);
				endwhile;
			?>
				
		</div>
	</div>
	
	<script>
		// Masonry Init
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
		
		// Handle refreshes
		var getNewPosts = function() {
			$.ajax({
				url: $('#new-posts a').attr('href'),
				success: function(data){
					// On success, prepend the data found
					var newPostsURL = $('#new-posts a').attr('href');
					var data = $(data),
						boxes = data.find('div.box');
					$container.prepend( boxes ).masonry( 'reload' );
					
					// Update newPostsURL with correct time interval after refresh
					var fromInt = newPostsURL.split('from='),
						fromInt = parseInt(fromInt[1]);
					
					var fromTime = fromInt + (<?=$autorefresh_interval?> * 100); //  *100 for adding to Minute value
					$('#new-posts a').attr('href', '<?=site_url()?>/newposts/?from=' + fromTime);
			   }
			});
		}
		
		<?php if ($autorefresh_on == 1) { ?>
		// If Autorefreshing is turned on, set an interval for running getNewPosts()		
		var interval = 1000 * 60 * <?=$autorefresh_interval?>;
		setInterval(getNewPosts, interval);
		<?php } ?>
		
		
		// Handle Infinite Scrolling
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