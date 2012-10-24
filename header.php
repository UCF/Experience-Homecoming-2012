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
	<?php $theme_options = get_option(THEME_OPTIONS_NAME); ?>
		<div class="container-fluid">
			<div class="row-fluid" id="header">
				<a href="<?=bloginfo('url')?>"><h1><?=bloginfo('name')?></h1></a>
				<div id="header-info">
					<h4>Join the Crowd:</h4>
					<p id="header-taglist">
						<?php 
							if ($theme_options['hashtags']) {
								$hashtags = explode(',', $theme_options['hashtags']);
								foreach ($hashtags as $hashtag) {
									$hashtag = explode('#', $hashtag);
									print '<span class="taglist-hash">#</span><span class="taglist-tag">'.$hashtag[1].'</span><span class="comma">, </span>';
								}
							}
						?>
					</p>
					<p id="header-servicelist">
						<?php
							if ($theme_options['enabled_services']) {
								$services = $theme_options['enabled_services'];
								foreach ($services as $service) {
									print '<span class="servicelist-service">'.ucfirst($service).'</span><span class="comma">, </span>';
								}
							}
						?>
					</p>
				</div>
			</div>
		</div>