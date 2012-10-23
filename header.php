<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<?="\n".header_()."\n"?>
		
		<?php if(GA_ACCOUNT or CB_UID):?>
		
		<script type="text/javascript">
			var _sf_startpt = (new Date()).getTime();
			<?php if(GA_ACCOUNT):?>
			
			var GA_ACCOUNT  = '<?=GA_ACCOUNT?>';
			var _gaq        = _gaq || [];
			_gaq.push(['_setAccount', GA_ACCOUNT]);
			_gaq.push(['_setDomainName', 'none']);
			_gaq.push(['_setAllowLinker', true]);
			_gaq.push(['_trackPageview']);
			<?php endif;?>
			<?php if(CB_UID):?>
			
			var CB_UID      = '<?=CB_UID?>';
			var CB_DOMAIN   = '<?=CB_DOMAIN?>';
			<?php endif?>
			
		</script>
		<?php endif;?>
		
		<?  $post_type = get_post_type($post->ID);
			if(($stylesheet_id = get_post_meta($post->ID, $post_type.'_stylesheet', True)) !== False
				&& ($stylesheet_url = wp_get_attachment_url($stylesheet_id)) !== False) { ?>
				<link rel='stylesheet' href="<?=$stylesheet_url?>" type='text/css' media='all' />
		<? } ?>
		
	</head>
	<!--[if lt IE 7 ]>  <body class="ie ie6 <?=body_classes()?>"> <![endif]-->
	<!--[if IE 7 ]>     <body class="ie ie7 <?=body_classes()?>"> <![endif]-->
	<!--[if IE 8 ]>     <body class="ie ie8 <?=body_classes()?>"> <![endif]-->
	<!--[if IE 9 ]>     <body class="ie ie9 <?=body_classes()?>"> <![endif]-->
	<!--[if (gt IE 9)|!(IE)]><!--> <body class="<?=body_classes()?>"> <!--<![endif]-->
		<div class="container-fluid">
			<div class="row-fluid" id="header">
				<div class="span12">
					<h1><a href="<?=bloginfo('url')?>"><?=bloginfo('name')?></a></h1>
					<h2>Something else can go here...</h2>
					<p>This is a basic demonstration of Masonry.js and InfiniteScroll.js with a custom post type loop.</p>
					<?=wp_nav_menu(array(
						'theme_location' => 'main-menu', 
						'container' => 'false', 
						'menu_class' => 'menu '.get_header_styles(), 
						'menu_id' => 'header-menu', 
						'walker' => new Bootstrap_Walker_Nav_Menu()
						));
					?>
				</div>
			</div>