<?php get_header(); the_post();?>
	<div class="row page-content" id="<?=$post->post_name?>">
		<div class="span9">
			<? if(!is_front_page())	{ ?>
					<h1><?php the_title();?></h1>
			<? } ?>
			<?php the_content();?>
		</div>
		<div class="span3" id="sidebar">
			<?=get_sidebar();?>
		</div>
	</div>
<?php get_footer();?>