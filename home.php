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
				if (is_user_logged_in() && current_user_can('edit_post')) {
					global $current_user;
					get_currentuserinfo(); 
			?>
			<div class="modal fade" id="adminmodal">
				<div class="modal-header">
					<a class="close" data-dismiss="modal">×</a>
					<h3>Hello, <?=$current_user->display_name?>!</h3>
				</div>
				<div class="modal-body">
					<p>When approving posts from the home page, please remember to <strong>scroll down</strong> until you reach the last <span class="label label-success">published</span> post, and work your way up. This way, published posts will display in the correct order.</p>
				</div>
				<div class="modal-footer">
					<a href="#" class="btn" data-dismiss="modal">Close</a>
				</div>
			</div>
			<?php
				}
			?>
			<?php
				while ( $loop->have_posts() ) : $loop->the_post();
					print display_feedsubmission($post);
				endwhile;
			?>
		
			<p id="page-nav"><?php print "<a href='".site_url()."/?pg=".($paged+1)."'>Next</a>"; ?></p>
			<p id="new-posts"><?php print "<a href='".site_url()."/newposts/?from=".date('YmdHis')."'>Get New Posts</a>"; ?></p>	
		</div>
		
	</div>
	
	<script type="text/javascript">
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
					// TODO: fix this to handle months, years
					var fromInt = newPostsURL.split('from='),
						fromInt = parseInt(fromInt[1]);
					
					fromInt = fromInt + (<?=$autorefresh_interval?> * 100); //  *100 for adding to Minute value
					
					fromInt = fromInt.toString(); // so we can use substr
					
					var fromDay = fromInt.substr(0,8),
						fromHr	= fromInt.substring(8,10),
						fromMin	= fromInt.substr(10,14);
						
					if (fromMin > 5999) {
						fromMin = '0000';
						fromHr = parseInt(fromHr) + 1;
						fromHr = fromHr.toString(); // prevent '10' from being appended in fromTime as '1'
					}
					if (fromHr > 23) {
						fromHr = '00';
						fromDay = parseInt(fromDay) + 1;
						fromDay = fromDay.toString();
					}
					
					fromInt = "" + fromDay + fromHr + fromMin; // force contatenation
					var fromTime = parseInt(fromInt);
					
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
		
		
		// Handle Admin updating
		<?php if (is_user_logged_in() && current_user_can('edit_post')) { ?>
		$('.edit-approve').live('click', function(e) {
			e.preventDefault();
			var button = $(this);
			$.ajax({
				url: button.attr('data-approve-url'),
				cache: false
			}).done(function() {
				button.addClass('btn-success').parents('.btn-group').next('a.editlink').addClass('disabled').click(function(e) { e.preventDefault(); });;
				button.next('.edit-delete').andSelf().attr('disabled', 'disabled');
				button.parents('.box').animate({ opacity: 0.45, }, 1000);	
			});
		});
		$('.edit-delete').live('click', function(e) {
			e.preventDefault();
			var button = $(this);
			$.ajax({
				url: button.attr('data-trash-url'),
				cache: false
			}).done(function() {
				button.addClass('btn-danger').parents('.btn-group').next('a.editlink').addClass('disabled').click(function(e) { e.preventDefault(); });;
				button.prev('.edit-approve').andSelf().attr('disabled', 'disabled');
				button.parents('.box').animate({ opacity: 0.45, }, 1000);	
			});
		});
		$('#adminmodal').modal();
		<?php } ?>
	});
	</script>
		
<?php get_footer();?>